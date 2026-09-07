import { Renderer, Program, Mesh, Triangle, Texture } from 'https://esm.sh/ogl@1.0.11';

const vertex = `#version 300 es
in vec2 position;
in vec2 uv;
out vec2 vUv;
void main() {
  vUv = uv;
  gl_Position = vec4(position, 0.0, 1.0);
}`;

const fragment = `#version 300 es
precision highp float;
uniform sampler2D uText;
uniform vec2 uResolution;
uniform vec2 uPointer;
uniform float uActive;
uniform float uTime;
uniform float uDragWarp;
in vec2 vUv;
out vec4 color;

float hash(vec2 p) {
  p = fract(p * vec2(123.34, 456.21));
  p += dot(p, p + 45.32);
  return fract(p.x * p.y);
}

float noise(vec2 p) {
  vec2 i = floor(p);
  vec2 f = fract(p);
  vec2 u = f * f * (3.0 - 2.0 * f);
  return mix(mix(hash(i), hash(i + vec2(1.0, 0.0)), u.x), mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0)), u.x), u.y);
}

void main() {
  vec2 uv = vUv;
  float aspect = uResolution.x / max(uResolution.y, 1.0);
  vec2 delta = uv - uPointer;
  delta.x *= aspect;
  float distanceToPointer = length(delta);
  float lens = smoothstep(0.46, 0.0, distanceToPointer) * uActive;
  vec2 direction = distanceToPointer > 0.001 ? delta / distanceToPointer : vec2(0.0);
  vec2 dragWarp = vec2(delta.y, -delta.x) * uDragWarp * 0.12;
  vec2 displaced = uv + dragWarp;
  vec2 split = direction * (0.004 + lens * 0.01);
  vec4 base = texture(uText, displaced);
  float red = texture(uText, displaced + split).r;
  float blue = texture(uText, displaced - split).b;
  color = vec4(red + lens * base.a * 0.08, base.g, blue, base.a);
}`;

const state = {
  text: '',
  renderer: null,
  program: null,
  texture: null,
  canvas: null,
  sourceCanvas: document.createElement('canvas'),
  pointer: { x: 0.5, y: 0.5, active: 0 },
  dragWarp: 0,
  dragWarpTarget: 0,
  frame: 0,
  resizeObserver: null
};

const root = document.querySelector('.warp-text');
const fallback = document.querySelector('.hero__title--fallback');
if (root && fallback) {
  const sourceContext = state.sourceCanvas.getContext('2d');
  const redrawText = () => {
    const bounds = root.getBoundingClientRect();
    const ratio = Math.min(window.devicePixelRatio || 1, 2);
    const width = Math.max(1, Math.floor(bounds.width * ratio));
    const height = Math.max(1, Math.floor(bounds.height * ratio));
    state.sourceCanvas.width = width;
    state.sourceCanvas.height = height;
    sourceContext.clearRect(0, 0, width, height);
    sourceContext.fillStyle = '#f7fbff';
    sourceContext.textAlign = 'left';
    sourceContext.textBaseline = 'middle';
    let fontSize = Math.min(height * 0.42, width / Math.max(state.text.length * 0.48, 1));
    sourceContext.font = `900 ${Math.max(28, fontSize)}px Orbitron, sans-serif`;
    sourceContext.shadowColor = 'rgba(127, 231, 255, 0.42)';
    sourceContext.shadowBlur = 18;
    const maxWidth = width * 0.94;
    const text = state.text || fallback.textContent.trim();
    const lines = text.length > 15 && width < 700 ? text.match(/.{1,15}(?:\s|$)/g) || [text] : [text];
    const widestLine = Math.max(...lines.map(line => sourceContext.measureText(line.trim()).width), 1);
    if (widestLine > maxWidth) {
      fontSize *= maxWidth / widestLine;
      sourceContext.font = `900 ${Math.max(24, fontSize)}px Orbitron, sans-serif`;
    }
    lines.forEach((line, index) => {
      const trimmed = line.trim();
      const measured = sourceContext.measureText(trimmed).width;
      sourceContext.fillText(trimmed, (width - measured) / 2, height * (0.5 + (index - (lines.length - 1) / 2) * 0.42));
    });
    if (state.texture) {
      state.texture.image = state.sourceCanvas;
      state.texture.needsUpdate = true;
    }
  };

  const resize = () => {
    const bounds = root.getBoundingClientRect();
    if (!bounds.width || !bounds.height || !state.renderer) return;
    state.renderer.setSize(bounds.width, bounds.height);
    state.program.uniforms.uResolution.value[0] = state.renderer.gl.drawingBufferWidth;
    state.program.uniforms.uResolution.value[1] = state.renderer.gl.drawingBufferHeight;
    redrawText();
  };

  const render = (time) => {
    state.program.uniforms.uTime.value = time * 0.001;
    state.program.uniforms.uPointer.value[0] += (state.pointer.x - state.program.uniforms.uPointer.value[0]) * 0.12;
    state.program.uniforms.uPointer.value[1] += (state.pointer.y - state.program.uniforms.uPointer.value[1]) * 0.12;
    state.program.uniforms.uActive.value += (state.pointer.active - state.program.uniforms.uActive.value) * 0.08;
    state.dragWarp += (state.dragWarpTarget - state.dragWarp) * 0.14;
    state.program.uniforms.uDragWarp.value = state.dragWarp;
    state.renderer.render({ scene: state.mesh });
    state.frame = requestAnimationFrame(render);
  };

  try {
    state.text = fallback.textContent.trim();
    state.renderer = new Renderer({ webgl: 2, alpha: true, antialias: true, dpr: Math.min(window.devicePixelRatio || 1, 2) });
    state.canvas = state.renderer.gl.canvas;
    state.canvas.setAttribute('aria-hidden', 'true');
    state.texture = new Texture(state.renderer.gl, { generateMipmaps: false, minFilter: state.renderer.gl.LINEAR, magFilter: state.renderer.gl.LINEAR });
    state.program = new Program(state.renderer.gl, {
      vertex,
      fragment,
      transparent: true,
      uniforms: {
        uText: { value: state.texture },
        uResolution: { value: new Float32Array([1, 1]) },
        uPointer: { value: new Float32Array([0.5, 0.5]) },
        uActive: { value: 0 },
        uTime: { value: 0 },
        uDragWarp: { value: 0 }
      }
    });
    state.mesh = new Mesh(state.renderer.gl, { geometry: new Triangle(state.renderer.gl), program: state.program });
    root.appendChild(state.canvas);
    state.resizeObserver = new ResizeObserver(resize);
    state.resizeObserver.observe(root);
    root.addEventListener('pointermove', event => {
      const bounds = root.getBoundingClientRect();
      state.pointer.x = (event.clientX - bounds.left) / bounds.width;
      state.pointer.y = 1 - (event.clientY - bounds.top) / bounds.height;
      state.pointer.active = 1;
    }, { passive: true });
    root.addEventListener('pointerleave', () => { state.pointer.active = 0; }, { passive: true });
    root.addEventListener('warptext:update', event => {
      state.text = String(event.detail || '');
      redrawText();
    });
    root.addEventListener('warptext:drag', event => {
      state.dragWarpTarget = Math.max(-1, Math.min(1, Number(event.detail?.amount) || 0));
    });
    resize();
    root.classList.add('is-ready');
    state.frame = requestAnimationFrame(render);
  } catch (error) {
    console.warn('WarpText fallback:', error);
  }
}
