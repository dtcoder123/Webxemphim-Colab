(() => {
  const canvas = document.querySelector('.hero__glow-cursor');
  const container = document.querySelector('.hero');
  if (!canvas || !container) return;

  const context = canvas.getContext('2d');
  const points = Array.from({ length: 40 }, () => ({ x: 0, y: 0 }));
  const pointer = { x: 0, y: 0 };
  let width = 1;
  let height = 1;
  let initialized = false;
  let inside = false;
  let opacity = 0;
  let lastInput = performance.now();
  let frame = 0;

  const resize = () => {
    const ratio = Math.min(window.devicePixelRatio || 1, 1.5);
    width = Math.max(1, container.clientWidth);
    height = Math.max(1, container.clientHeight);
    canvas.width = Math.round(width * ratio);
    canvas.height = Math.round(height * ratio);
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
  };

  const updatePointer = event => {
    const bounds = container.getBoundingClientRect();
    pointer.x = Math.max(0, Math.min(width, event.clientX - bounds.left));
    pointer.y = Math.max(0, Math.min(height, event.clientY - bounds.top));
    if (!initialized) {
      points.forEach(point => {
        point.x = pointer.x;
        point.y = pointer.y;
      });
      initialized = true;
    }
    inside = true;
    lastInput = performance.now();
  };

  const fadeOut = () => {
    inside = false;
    lastInput = performance.now();
  };

  const render = now => {
    const delta = Math.min((now - (render.last || now)) / 16.667, 3);
    render.last = now;
    context.clearRect(0, 0, width, height);
    if (initialized) {
      points[0].x += (pointer.x - points[0].x) * (1 - Math.pow(0.84, delta));
      points[0].y += (pointer.y - points[0].y) * (1 - Math.pow(0.84, delta));
      for (let index = 1; index < points.length; index += 1) {
        points[index].x += (points[index - 1].x - points[index].x) * (1 - Math.pow(0.66, delta));
        points[index].y += (points[index - 1].y - points[index].y) * (1 - Math.pow(0.66, delta));
      }
    }

    const idle = !inside || now - lastInput > 700;
    const fadeTarget = initialized && !idle ? 1 : 0;
    opacity += (fadeTarget - opacity) * Math.min(1, delta * 0.12);
    if (opacity > 0.002 && initialized) {
      context.save();
      context.globalCompositeOperation = 'screen';
      context.lineCap = 'round';
      context.lineJoin = 'round';
      context.globalAlpha = opacity;

      for (let index = points.length - 1; index > 0; index -= 1) {
        const progress = index / (points.length - 1);
        const widthAtPoint = 1.2 + (1 - progress) * 7.5;
        const alpha = (1 - progress) ** 1.15;
        const gradient = context.createLinearGradient(points[index].x, points[index].y, points[index - 1].x, points[index - 1].y);
        gradient.addColorStop(0, `rgba(167, 139, 250, ${alpha * 0.12})`);
        gradient.addColorStop(1, `rgba(103, 232, 249, ${alpha})`);
        context.strokeStyle = gradient;
        context.lineWidth = widthAtPoint;
        context.shadowBlur = 18 * (1 - progress);
        context.shadowColor = progress > 0.55 ? '#a78bfa' : '#67e8f9';
        context.beginPath();
        context.moveTo(points[index].x, points[index].y);
        context.lineTo(points[index - 1].x, points[index - 1].y);
        context.stroke();
      }

      const head = points[0];
      const headGlow = context.createRadialGradient(head.x, head.y, 0, head.x, head.y, 34);
      headGlow.addColorStop(0, 'rgba(255, 255, 255, 0.9)');
      headGlow.addColorStop(0.18, 'rgba(103, 232, 249, 0.85)');
      headGlow.addColorStop(1, 'rgba(103, 232, 249, 0)');
      context.fillStyle = headGlow;
      context.beginPath();
      context.arc(head.x, head.y, 34, 0, Math.PI * 2);
      context.fill();
      context.restore();
    }
    frame = requestAnimationFrame(render);
  };

  resize();
  const resizeObserver = new ResizeObserver(resize);
  resizeObserver.observe(container);
  container.addEventListener('pointermove', updatePointer, { passive: true });
  container.addEventListener('pointerenter', updatePointer, { passive: true });
  container.addEventListener('pointerleave', fadeOut, { passive: true });
  frame = requestAnimationFrame(render);
})();
