/* =========================================================
   STARK-SYS // main.js
   Xử lý tương tác giao diện
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ---------- JARVIS boot sequence (chạy mỗi lần vào web) ---------- */
  const bootSequence = document.getElementById('bootSequence');
  if (bootSequence) {
    document.body.classList.add('is-booting');

    const bootLines = [
      'KHỞI ĐỘNG LÕI ARC REACTOR…',
      'NẠP GIAO THỨC J.A.R.V.I.S…',
      'ĐỒNG BỘ VỆ TINH ĐỊNH VỊ…',
      'HIỆU CHỈNH LỚP HIỂN THỊ HUD…',
      'XÁC THỰC NGƯỜI DÙNG… OK',
      'HỆ THỐNG SẴN SÀNG.'
    ];

    const bootLog = document.getElementById('bootLog');
    const bootBarFill = document.getElementById('bootBarFill');
    const bootPct = document.getElementById('bootPct');
    let lineIndex = 0;
    let progress = 0;

    const addNextLine = () => {
      if (!bootLog || lineIndex >= bootLines.length) return;
      const line = document.createElement('p');
      line.textContent = '> ' + bootLines[lineIndex];
      bootLog.appendChild(line);
      lineIndex++;
      if (lineIndex < bootLines.length) {
        setTimeout(addNextLine, 340);
      }
    };
    addNextLine();

    const progressTimer = setInterval(() => {
      progress = Math.min(100, progress + Math.random() * 14 + 8);
      if (bootBarFill) bootBarFill.style.width = progress + '%';
      if (bootPct) bootPct.textContent = Math.floor(progress) + '%';
      if (progress >= 100) clearInterval(progressTimer);
    }, 230);

    setTimeout(() => {
      bootSequence.classList.add('is-hidden');
      document.body.classList.remove('is-booting');
      setTimeout(() => bootSequence.remove(), 700);
    }, 2700);
  }

  /* ---------- Live clock readout in boot strip ---------- */
  const clockEl = document.getElementById('clockReadout');
  if (clockEl) {
    const tick = () => {
      const now = new Date();
      const pad = (n) => String(n).padStart(2, '0');
      clockEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    };
    tick();
    setInterval(tick, 1000);
  }

  /* ---------- Mobile nav toggle ---------- */
  const navToggle = document.getElementById('navToggle');
  const mainNav = document.getElementById('mainNav');
  if (navToggle && mainNav) {
    navToggle.addEventListener('click', () => {
      mainNav.classList.toggle('is-open');
    });
  }
  /* ---------- Category / genre filter tabs + search + suggestions (index.php) ---------- */
  const movieGrid = document.getElementById('movieGrid');
  const movieSearchInput = document.getElementById('movieSearchInput');
  const movieSuggestions = document.getElementById('movieSuggestions');
  const featuredHero = document.querySelector('.hero');
  const normalizeFilterText = (value) => {
    return String(value || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd')
      .replace(/Đ/g, 'D')
      .replace(/[^a-z0-9\s]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  };

  const getMovieCatalog = () => {
    if (Array.isArray(window.movieCatalog) && window.movieCatalog.length) {
      return window.movieCatalog;
    }
    return [...document.querySelectorAll('.movie-card')].map((card) => ({
      id: Number(card.dataset.id || 0),
      title: card.dataset.title || '',
      genre: card.dataset.genre || '',
      tagline: card.dataset.tagline || '',
      poster: card.querySelector('img')?.getAttribute('src') || '',
      keywords: `${card.dataset.title || ''} ${card.dataset.genre || ''} ${card.dataset.tagline || ''} ${card.dataset.keywords || ''}`
    }));
  };

  const hideSearchSuggestions = () => {
    if (movieSuggestions) {
      movieSuggestions.innerHTML = '';
      movieSuggestions.classList.remove('is-visible');
    }
  };

  const ensurePreviewModal = () => {
    let modal = document.getElementById('moviePreviewModal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'moviePreviewModal';
    modal.className = 'movie-preview-modal';
    modal.innerHTML = `
      <div class="movie-preview-modal__backdrop" data-close-preview="true"></div>
      <div class="movie-preview-modal__panel" role="dialog" aria-modal="true" aria-label="Xem trước phim">
        <button class="movie-preview-modal__close" type="button" aria-label="Đóng">✕</button>
        <div class="movie-preview-modal__media">
          <img class="movie-preview-modal__poster" src="" alt="">
        </div>
        <div class="movie-preview-modal__content">
          <span class="movie-preview-modal__eyebrow">PREVIEW // MAIN STREAM</span>
          <h3 class="movie-preview-modal__title"></h3>
          <div class="movie-preview-modal__meta"></div>
          <p class="movie-preview-modal__tagline"></p>
          <div class="movie-preview-modal__actions">
            <a class="btn-hud btn-hud--primary movie-preview-modal__cta" href="#">▶ XEM NGAY</a>
            <button class="btn-hud btn-hud--ghost movie-preview-modal__dismiss" type="button">ĐÓNG</button>
          </div>
        </div>
      </div>
    `;

    document.body.appendChild(modal);
    modal.querySelector('[data-close-preview]')?.addEventListener('click', () => modal.classList.remove('is-open'));
    modal.querySelector('.movie-preview-modal__close')?.addEventListener('click', () => modal.classList.remove('is-open'));
    modal.querySelector('.movie-preview-modal__dismiss')?.addEventListener('click', () => modal.classList.remove('is-open'));
    return modal;
  };

  const openPreviewModal = (movie) => {
    const modal = ensurePreviewModal();
    if (!movie) return;

    const poster = movie.poster || movie.image || '';
    const title = movie.title || 'Phim';
    const genre = movie.genre || 'Phim';
    const tagline = movie.tagline || 'Tận hưởng chi tiết, âm thanh và phong cách hình ảnh tối ưu.';
    const year = movie.year || '';
    const rating = movie.rating || '9.0';
    const id = movie.id || 1;

    modal.querySelector('.movie-preview-modal__poster').src = poster;
    modal.querySelector('.movie-preview-modal__poster').alt = title;
    modal.querySelector('.movie-preview-modal__title').textContent = title;
    modal.querySelector('.movie-preview-modal__tagline').textContent = tagline;
    modal.querySelector('.movie-preview-modal__meta').innerHTML = `
      <span>${genre}</span>
      ${year ? `<span>·</span><span>${year}</span>` : ''}
      <span>·</span>
      <span>★ ${rating}</span>
    `;
    modal.querySelector('.movie-preview-modal__cta').href = `watch.php?id=${id}`;
    modal.classList.add('is-open');
  };

  const renderSuggestions = () => {
    if (!movieSearchInput || !movieSuggestions) return;

    const query = normalizeFilterText(movieSearchInput.value);
    const movieList = getMovieCatalog();

    if (!query) {
      hideSearchSuggestions();
      if (featuredHero) featuredHero.classList.remove('is-hidden-search');
      return;
    }

    const matches = movieList.filter((movie) => {
      const text = normalizeFilterText(`${movie.title || ''} ${movie.genre || ''} ${movie.tagline || ''} ${movie.keywords || ''}`);
      return text.includes(query);
    }).slice(0, 6);

    if (!matches.length) {
      movieSuggestions.innerHTML = '<div class="movie-suggestion"><div class="movie-suggestion__meta"><div class="movie-suggestion__title">Không tìm thấy phim</div></div></div>';
      movieSuggestions.classList.add('is-visible');
      return;
    }

    movieSuggestions.innerHTML = matches.map((movie) => {
      const title = movie.title || 'Phim';
      const genre = movie.genre || '';
      const id = movie.id || '';
      const poster = movie.poster || '';
      return `
        <a href="watch.php?id=${id}" class="movie-suggestion" data-id="${id}" data-title="${title}" data-genre="${genre}" data-tagline="${movie.tagline || ''}" data-poster="${poster}">
          <img class="movie-suggestion__thumb" src="${poster}" alt="${title}">
          <div class="movie-suggestion__meta">
            <div class="movie-suggestion__title">${title}</div>
            <div class="movie-suggestion__info">${genre}</div>
          </div>
        </a>
      `;
    }).join('');

    movieSuggestions.classList.add('is-visible');
  };

  const applyMovieFilters = () => {
    if (!filterTabs || !movieGrid) return;

    const cards = movieGrid.querySelectorAll('.movie-card');
    const activeTab = filterTabs.querySelector('.filter-tab.is-active');
    const selectedGenre = activeTab ? activeTab.dataset.genre : 'Tất cả';
    const searchTerm = normalizeFilterText(movieSearchInput ? movieSearchInput.value : '');

    let visibleCount = 0;

    cards.forEach((card) => {
      const genre = normalizeFilterText(card.dataset.genre || '');
      const tagline = normalizeFilterText(card.dataset.tagline || '');
      const keywords = normalizeFilterText(card.dataset.keywords || '');
      const title = normalizeFilterText(card.dataset.title || '');

      const genreMatch = selectedGenre === 'Tất cả' || genre.includes(normalizeFilterText(selectedGenre)) || keywords.includes(normalizeFilterText(selectedGenre));
      const searchMatch = !searchTerm || keywords.includes(searchTerm) || genre.includes(searchTerm) || tagline.includes(searchTerm) || title.includes(searchTerm);
      const matches = genreMatch && searchMatch;

      card.style.display = matches ? '' : 'none';
      if (matches) visibleCount += 1;
    });

    movieGrid.querySelectorAll('.movie-shelf').forEach((shelf) => {
      const hasVisibleCard = [...shelf.querySelectorAll('.movie-card')]
        .some((card) => card.style.display !== 'none');
      shelf.style.display = hasVisibleCard ? '' : 'none';
    });

    const countEl = document.querySelector('.section-heading__count');
    if (countEl) {
      countEl.textContent = `${visibleCount} ENTRIES FOUND`;
    }
  };

  if (movieSearchInput && movieSuggestions) {
    movieSearchInput.addEventListener('input', () => {
      if (featuredHero) featuredHero.classList.remove('is-hidden-search');
      renderSuggestions();
    });

    movieSearchInput.addEventListener('focus', renderSuggestions);

    movieSearchInput.closest('form')?.addEventListener('submit', (event) => {
      event.preventDefault();
      if (movieGrid) {
        applyMovieFilters();
      }
      hideSearchSuggestions();
    });

    document.addEventListener('click', (event) => {
      const target = event.target;
      const previewTrigger = target.closest('.movie-card');
      const suggestionTrigger = target.closest('.movie-suggestion');
      const watchCardTrigger = target.closest('.suggested-card');

      if (previewTrigger || suggestionTrigger || watchCardTrigger) {
        const trigger = previewTrigger || suggestionTrigger || watchCardTrigger;
        const dataset = trigger.dataset || {};
        const movie = {
          id: Number(dataset.id || 0),
          title: dataset.title || trigger.querySelector('h3, h4')?.textContent || 'Phim',
          genre: dataset.genre || '',
          tagline: dataset.tagline || '',
          poster: dataset.poster || trigger.querySelector('img')?.getAttribute('src') || '',
          year: dataset.year || '',
          rating: dataset.rating || '9.0'
        };

        if (trigger.matches('.movie-card, .movie-suggestion, .suggested-card')) {
          event.preventDefault();
          openPreviewModal(movie);
        }
      }

      if (!movieSearchInput.contains(target) && !movieSuggestions.contains(target)) {
        hideSearchSuggestions();
      }
    });

    hideSearchSuggestions();
  }

  if (filterTabs && movieGrid) {
    const tabs = filterTabs.querySelectorAll('.filter-tab');

    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        tabs.forEach((t) => t.classList.remove('is-active'));
        tab.classList.add('is-active');
        applyMovieFilters();
      });
    });

    applyMovieFilters();
  }

  movieGrid?.querySelectorAll('.movie-shelf__next').forEach((button) => {
    button.addEventListener('click', () => {
      button.previousElementSibling?.scrollBy({ left: 280, behavior: 'smooth' });
    });
  });

  /* ---------- Load more (mock — reveals a status message) ---------- */
  const loadMoreBtn = document.getElementById('loadMoreBtn');
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', () => {
      loadMoreBtn.textContent = 'ĐANG ĐỒNG BỘ DỮ LIỆU…';
      loadMoreBtn.disabled = true;
      setTimeout(() => {
        loadMoreBtn.textContent = 'ĐÃ TẢI TOÀN BỘ DỮ LIỆU HIỆN CÓ';
      }, 900);
    });
  }

  /* ---------- Hero featured movie switcher ---------- */
  const heroBg = document.querySelector('.hero__bg');
  const aeroCanvas = document.querySelector('.hero__aero-shards');
  const heroTitle = document.querySelector('.hero__title');
  const heroTagline = document.querySelector('.hero__tagline');
  const heroMeta = document.querySelector('.hero__meta');
  const heroEnergyFill = document.querySelector('.hero__energy-fill');
  const heroPoster = document.querySelector('.hero__poster-img');
  const heroPosterWrap = document.querySelector('.hero__poster-wrap');
  const heroActionsPrimary = document.querySelector('.hero__actions .btn-hud--primary');
  const heroActionsInfo = document.querySelector('.hero__actions .btn-hud--ghost');
  const heroMiniCards = document.querySelectorAll('.hero__mini-card');
  const heroDragSurface = featuredHero;
  let isDraggingHeroRail = false;
  let blockHeroRailClick = false;
  let heroRailPointerId = null;
  let heroDragStartX = 0;
  let heroDragOffset = 0;

  const replayAnimation = (element, animationName) => {
    if (!element) return;
    element.classList.remove('animate__animated', animationName);
    void element.offsetWidth;
    element.classList.add('animate__animated', animationName);
  };

  const initAeroShards = (canvas, root) => {
    if (!canvas || !root || !canvas.getContext) return;
    const context = canvas.getContext('2d');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const pointer = { x: 0.68, y: 0.46, active: false };
    const shards = [];
    let width = 0;
    let height = 0;
    let frameId = 0;
    let startTime = performance.now();

    const resize = () => {
      const bounds = root.getBoundingClientRect();
      const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
      width = Math.max(1, bounds.width);
      height = Math.max(1, bounds.height);
      canvas.width = Math.round(width * pixelRatio);
      canvas.height = Math.round(height * pixelRatio);
      canvas.style.width = `${width}px`;
      canvas.style.height = `${height}px`;
      context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
      shards.length = 0;
      const count = width < 700 ? 90 : 190;
      for (let index = 0; index < count; index += 1) {
        shards.push({
          phase: Math.random(),
          lane: Math.random() * 2 - 1,
          depth: Math.random(),
          size: 5 + Math.random() * 15,
          stretch: 0.65 + Math.random() * 1.6,
          rotation: Math.random() * Math.PI,
          speed: 0.035 + Math.random() * 0.08,
          hue: Math.random()
        });
      }
    };

    const draw = (timestamp) => {
      const elapsed = (timestamp - startTime) / 1000;
      context.clearRect(0, 0, width, height);
      const gradient = context.createLinearGradient(0, 0, width, height);
      gradient.addColorStop(0, 'rgba(3, 10, 17, 0.98)');
      gradient.addColorStop(0.55, 'rgba(8, 17, 25, 0.88)');
      gradient.addColorStop(1, 'rgba(22, 8, 16, 0.96)');
      context.fillStyle = gradient;
      context.fillRect(0, 0, width, height);

      const pointerX = pointer.x * width;
      const pointerY = pointer.y * height;
      shards.forEach((shard) => {
        const progress = (shard.phase + (reduceMotion.matches ? 0 : elapsed * shard.speed)) % 1;
        const wave = Math.sin(progress * Math.PI * 2 + shard.lane * 2.4);
        let x = progress * (width + 220) - 110;
        let y = height * (0.18 + (shard.lane + 1) * 0.29) + wave * height * 0.14;
        const distanceX = x - pointerX;
        const distanceY = y - pointerY;
        const distance = Math.hypot(distanceX, distanceY);
        if (pointer.active && distance < 230) {
          const force = (1 - distance / 230) ** 2;
          x += (distanceX / Math.max(distance, 1)) * force * 75;
          y += (distanceY / Math.max(distance, 1)) * force * 75;
        }

        const scale = 0.55 + shard.depth * 0.9;
        const size = shard.size * scale;
        const alpha = 0.16 + shard.depth * 0.62;
        context.save();
        context.translate(x, y);
        context.rotate(shard.rotation + wave * 0.45);
        context.scale(1, shard.stretch);
        const shardGradient = context.createLinearGradient(-size, -size, size, size);
        if (shard.hue > 0.7) {
          shardGradient.addColorStop(0, `rgba(255, 107, 53, ${alpha})`);
          shardGradient.addColorStop(1, `rgba(245, 208, 111, ${alpha * 0.3})`);
        } else {
          shardGradient.addColorStop(0, `rgba(127, 231, 255, ${alpha})`);
          shardGradient.addColorStop(1, `rgba(63, 179, 212, ${alpha * 0.2})`);
        }
        context.fillStyle = shardGradient;
        context.shadowBlur = 12 * shard.depth;
        context.shadowColor = shard.hue > 0.7 ? 'rgba(255, 107, 53, 0.42)' : 'rgba(127, 231, 255, 0.48)';
        context.beginPath();
        context.moveTo(0, -size);
        context.lineTo(size * 0.62, 0);
        context.lineTo(0, size);
        context.lineTo(-size * 0.62, 0);
        context.closePath();
        context.fill();
        context.restore();
      });

      frameId = window.requestAnimationFrame(draw);
    };

    root.addEventListener('pointermove', (event) => {
      const bounds = root.getBoundingClientRect();
      pointer.x = (event.clientX - bounds.left) / bounds.width;
      pointer.y = (event.clientY - bounds.top) / bounds.height;
      pointer.active = true;
    }, { passive: true });
    root.addEventListener('pointerleave', () => {
      pointer.active = false;
    }, { passive: true });
    window.addEventListener('resize', resize, { passive: true });
    resize();
    frameId = window.requestAnimationFrame(draw);
    return () => {
      window.cancelAnimationFrame(frameId);
      window.removeEventListener('resize', resize);
    };
  };

  initAeroShards(aeroCanvas, featuredHero);

  const setFeaturedMovie = (movie) => {
    if (!movie || !heroTitle || !heroTagline || !heroMeta || !heroPoster || !heroBg) return;

    const title = movie.title || 'Phim';
    const genre = movie.genre || 'Phim';
    const tagline = movie.tagline || 'Tận hưởng trải nghiệm phim với chất lượng hình ảnh và âm thanh tối ưu.';
    const poster = movie.poster || movie.image || '';
    const year = movie.year || '';
    const duration = movie.duration || '';
    const rating = movie.rating || '9.0';
    const id = movie.id || 1;

    const fadeOut = () => {
      heroTitle.style.opacity = '0';
      heroTitle.style.transform = 'translateY(10px)';
      heroTagline.style.opacity = '0';
      heroTagline.style.transform = 'translateY(10px)';
      heroMeta.style.opacity = '0';
      heroMeta.style.transform = 'translateY(10px)';
      if (heroPosterWrap) {
        heroPosterWrap.classList.add('is-switching');
      }
    };

    const fadeIn = () => {
      heroTitle.textContent = title;
      heroTitle.setAttribute('data-text', title);
      heroTagline.textContent = tagline;
      heroPoster.src = poster;
      heroPoster.alt = title;
      heroBg.style.backgroundImage = 'none';

      const metaHtml = [
        `<span class="meta-chip meta-chip--rating">★ ${rating}</span>`,
        year ? `<span class="meta-chip">${year}</span>` : '',
        duration ? `<span class="meta-chip">${duration}</span>` : '',
        `<span class="meta-chip">${genre}</span>`
      ].filter(Boolean).join('');
      heroMeta.innerHTML = metaHtml;

      if (heroEnergyFill) {
        const percent = Math.max(0, Math.min(100, Number(rating) * 10 || 90));
        heroEnergyFill.style.width = `${percent}%`;
      }

      if (heroActionsPrimary) {
        heroActionsPrimary.href = `watch.php?id=${id}`;
      }

      if (heroActionsInfo) {
        heroActionsInfo.href = `watch.php?id=${id}#movieInfo`;
      }

      replayAnimation(heroBg, 'animate__fadeIn');
      replayAnimation(heroTitle, 'animate__fadeInDown');
      replayAnimation(heroPoster, 'animate__zoomIn');
      replayAnimation(heroMeta, 'animate__fadeInUp');

      requestAnimationFrame(() => {
        heroTitle.style.opacity = '1';
        heroTitle.style.transform = 'translateY(0)';
        heroTagline.style.opacity = '1';
        heroTagline.style.transform = 'translateY(0)';
        heroMeta.style.opacity = '1';
        heroMeta.style.transform = 'translateY(0)';
        if (heroPosterWrap) {
          heroPosterWrap.classList.remove('is-switching');
        }
      });
    };

    fadeOut();
    window.setTimeout(fadeIn, 140);
  };

  const activateHeroCard = (card) => {
    if (!card) return;
    heroMiniCards.forEach((miniCard) => miniCard.classList.remove('is-selected'));
    card.classList.add('is-selected');
    replayAnimation(card, 'animate__tada');

    setFeaturedMovie({
      id: Number(card.dataset.id || 0),
      title: card.dataset.title || '',
      tagline: card.dataset.tagline || '',
      genre: card.dataset.genre || '',
      poster: card.dataset.poster || '',
      year: card.dataset.year || '',
      duration: card.dataset.duration || '',
      rating: card.dataset.rating || '9.0'
    });
  };

  heroMiniCards.forEach((card) => {
    card.addEventListener('click', (event) => {
      event.preventDefault();
      if (blockHeroRailClick) return;
      activateHeroCard(card);
    });
  });

  if (heroDragSurface && heroMiniCards.length > 1) {
    const resetHeroRailDrag = () => {
      heroDragSurface.classList.remove('is-dragging', 'is-pressed');
      heroDragSurface.style.setProperty('--hero-drag-offset', '0px');
      isDraggingHeroRail = false;
      heroDragOffset = 0;
    };

    heroDragSurface.addEventListener('pointerdown', (event) => {
      if (event.pointerType === 'mouse' && event.button !== 0) return;
      if (event.target.closest('a, button, input, textarea, select')) return;
      event.preventDefault();
      heroRailPointerId = event.pointerId;
      heroDragStartX = event.clientX;
      heroDragOffset = 0;
      isDraggingHeroRail = false;
      heroDragSurface.classList.add('is-pressed');
      heroDragSurface.setPointerCapture?.(event.pointerId);
    });

    heroDragSurface.addEventListener('pointermove', (event) => {
      if (event.pointerId !== heroRailPointerId) return;
      heroDragOffset = event.clientX - heroDragStartX;
      if (Math.abs(heroDragOffset) < 8 && !isDraggingHeroRail) return;

      isDraggingHeroRail = true;
      heroDragSurface.classList.add('is-dragging');
      const elasticOffset = Math.max(-72, Math.min(72, heroDragOffset * 0.42));
      heroDragSurface.style.setProperty('--hero-drag-offset', `${elasticOffset}px`);

      const swipeThreshold = Math.max(56, Math.min(110, heroDragSurface.clientWidth * 0.08));
      if (Math.abs(heroDragOffset) >= swipeThreshold) {
        const currentIndex = [...heroMiniCards].findIndex((card) => card.classList.contains('is-selected'));
        const fallbackIndex = currentIndex >= 0 ? currentIndex : 0;
        const direction = heroDragOffset < 0 ? 1 : -1;
        const nextIndex = (fallbackIndex + direction + heroMiniCards.length) % heroMiniCards.length;

        blockHeroRailClick = true;
        activateHeroCard(heroMiniCards[nextIndex]);
        window.setTimeout(() => {
          blockHeroRailClick = false;
        }, 280);

        heroDragStartX = event.clientX;
        heroDragOffset = 0;
        isDraggingHeroRail = false;
        heroDragSurface.classList.remove('is-dragging');
        heroDragSurface.style.setProperty('--hero-drag-offset', '0px');
      }
    });

    const finishHeroRailDrag = (event) => {
      if (event.pointerId !== heroRailPointerId) return;

      if (isDraggingHeroRail) {
        const currentIndex = [...heroMiniCards].findIndex((card) => card.classList.contains('is-selected'));
        const fallbackIndex = currentIndex >= 0 ? currentIndex : 0;
        const direction = heroDragOffset < 0 ? 1 : -1;
        const nextIndex = (fallbackIndex + direction + heroMiniCards.length) % heroMiniCards.length;
        blockHeroRailClick = true;
        activateHeroCard(heroMiniCards[nextIndex]);
        window.setTimeout(() => {
          blockHeroRailClick = false;
        }, 280);
      }

      heroDragSurface.releasePointerCapture?.(event.pointerId);
      heroRailPointerId = null;
      resetHeroRailDrag();
    };

    heroDragSurface.addEventListener('pointerup', finishHeroRailDrag);
    heroDragSurface.addEventListener('pointercancel', finishHeroRailDrag);
  }

  /* ---------- Watch page: real playback controls ---------- */
  const playBtn = document.getElementById('playBtn');
  const playBtn2 = document.getElementById('playBtn2');
  const rewindBtn = document.getElementById('rewindBtn');
  const forwardBtn = document.getElementById('forwardBtn');
  const playerScreen = document.getElementById('playerScreen');
  const moviePlayer = document.getElementById('moviePlayer');
  const progressTrack = document.querySelector('.progress-track');
  const timeReadout = document.querySelector('.time-readout');

  const seekVideo = (seconds) => {
    if (!moviePlayer || moviePlayer.tagName !== 'VIDEO') return;
    const duration = Number.isFinite(moviePlayer.duration) ? moviePlayer.duration : 0;
    const target = Math.min(Math.max(0, moviePlayer.currentTime + seconds), duration || 0);
    moviePlayer.currentTime = target;
    syncPlayerUi();
  };

  const playPauseState = () => {
    if (!moviePlayer) return false;
    if (moviePlayer.tagName === 'VIDEO') {
      return !moviePlayer.paused && !moviePlayer.ended;
    }
    return moviePlayer.dataset.playing === 'true';
  };

  const syncPlayerUi = () => {
    const isPlaying = playPauseState();
    if (playerScreen) playerScreen.classList.toggle('is-playing', isPlaying);
    if (playBtn) playBtn.style.opacity = isPlaying ? '0' : '1';
    if (playBtn2) playBtn2.textContent = isPlaying ? '❙❙' : '▶';

    if (moviePlayer && moviePlayer.tagName === 'VIDEO') {
      const duration = Number.isFinite(moviePlayer.duration) ? moviePlayer.duration : 0;
      const current = Number.isFinite(moviePlayer.currentTime) ? moviePlayer.currentTime : 0;
      const pct = duration > 0 ? (current / duration) * 100 : 0;
      const fill = progressTrack?.querySelector('.progress-track__fill');
      const handle = progressTrack?.querySelector('.progress-track__handle');
      if (fill) fill.style.width = pct + '%';
      if (handle) handle.style.left = pct + '%';
      if (timeReadout) {
        const format = (value) => {
          const total = Math.max(0, Math.floor(value));
          const minutes = Math.floor(total / 60);
          const seconds = total % 60;
          return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        };
        timeReadout.textContent = `${format(current)} / ${format(duration) || '00:00'}`;
      }
    }
  };

  const sendYouTubeCommand = (func, args = []) => {
    if (!moviePlayer || moviePlayer.tagName !== 'IFRAME' || !moviePlayer.src) return;
    moviePlayer.contentWindow?.postMessage(JSON.stringify({ event: 'command', func, args }), '*');
  };

  const togglePlaying = async () => {
    if (!playerScreen || !moviePlayer) return;

    if (moviePlayer.tagName === 'VIDEO') {
      if (moviePlayer.paused) {
        try {
          await moviePlayer.play();
        } catch (e) {
          console.warn('Không thể phát video:', e);
        }
      } else {
        moviePlayer.pause();
      }
    } else if (moviePlayer.tagName === 'IFRAME') {
      const isPlaying = moviePlayer.dataset.playing === 'true';
      if (isPlaying) {
        sendYouTubeCommand('pauseVideo');
        moviePlayer.dataset.playing = 'false';
      } else {
        sendYouTubeCommand('playVideo');
        moviePlayer.dataset.playing = 'true';
      }
    }

    syncPlayerUi();
  };

  if (moviePlayer && moviePlayer.tagName === 'VIDEO') {
    moviePlayer.addEventListener('play', syncPlayerUi);
    moviePlayer.addEventListener('pause', syncPlayerUi);
    moviePlayer.addEventListener('timeupdate', syncPlayerUi);
    moviePlayer.addEventListener('loadedmetadata', syncPlayerUi);
  }

  if (playBtn) playBtn.addEventListener('click', togglePlaying);
  if (playBtn2) playBtn2.addEventListener('click', togglePlaying);
  if (rewindBtn) rewindBtn.addEventListener('click', () => seekVideo(-10));
  if (forwardBtn) forwardBtn.addEventListener('click', () => seekVideo(10));

  if (progressTrack && moviePlayer && moviePlayer.tagName === 'VIDEO') {
    progressTrack.addEventListener('click', (e) => {
      const rect = progressTrack.getBoundingClientRect();
      const pct = Math.min(100, Math.max(0, ((e.clientX - rect.left) / rect.width) * 100));
      const duration = Number.isFinite(moviePlayer.duration) ? moviePlayer.duration : 0;
      if (duration > 0) {
        moviePlayer.currentTime = (pct / 100) * duration;
      }
      syncPlayerUi();
    });
  }

  if (moviePlayer && moviePlayer.tagName === 'IFRAME') {
    moviePlayer.dataset.playing = 'false';
  }

  /* ---------- AI Chatbox ---------- */
  const aiLauncher = document.getElementById('aiChatLauncher');
  const aiChatbox = document.getElementById('aiChatbox');
  const aiChatClose = document.getElementById('aiChatClose');
  const aiChatMessages = document.getElementById('aiChatMessages');
  const aiChatForm = document.getElementById('aiChatForm');
  const aiChatInput = document.getElementById('aiChatInput');
  const aiConversation = [];

  const scrollAiChat = () => {
    if (aiChatMessages) aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
  };

  const addAiMessage = (text, role = 'assistant', movies = []) => {
    if (!aiChatMessages) return;
    const message = document.createElement('div');
    message.className = `ai-chat-message ai-chat-message--${role}`;

    const avatar = document.createElement('span');
    avatar.className = 'ai-chat-message__avatar';
    avatar.textContent = role === 'assistant' ? '✦' : '●';

    const content = document.createElement('div');
    const paragraph = document.createElement('p');
    paragraph.textContent = text;
    content.appendChild(paragraph);

    if (movies.length && role === 'assistant') {
      const movieList = document.createElement('div');
      movieList.className = 'ai-chat-movie-list';
      movies.forEach((movie) => {
        const card = document.createElement('div');
        card.className = 'ai-chat-movie';

        const image = document.createElement('img');
        image.src = movie.poster || '';
        image.alt = movie.title || 'Phim';

        const info = document.createElement('div');
        info.className = 'ai-chat-movie__info';
        const title = document.createElement('strong');
        title.textContent = movie.title || 'Phim';
        const meta = document.createElement('small');
        meta.textContent = `${movie.genre || 'Chưa có thể loại'} · ${movie.year || 'N/A'} · ★ ${movie.rating || 'N/A'}`;
        info.append(title, meta);

        const link = document.createElement('a');
        link.className = 'ai-chat-movie__link';
        link.href = movie.watch_url || `watch.php?id=${Number(movie.id || 0)}`;
        link.textContent = 'Xem';
        card.append(image, info, link);
        movieList.appendChild(card);
      });
      content.appendChild(movieList);
    }

    message.append(avatar, content);
    aiChatMessages.appendChild(message);
    scrollAiChat();
  };

  const setAiLoading = (loading) => {
    const current = document.getElementById('aiChatLoading');
    if (current) current.remove();
    if (!loading || !aiChatMessages) return;

    const item = document.createElement('div');
    item.id = 'aiChatLoading';
    item.className = 'ai-chat-message ai-chat-message--assistant';
    item.innerHTML = '<span class="ai-chat-message__avatar">✦</span><p class="ai-chat-message__typing">AI ĐANG SUY NGHĨ...</p>';
    aiChatMessages.appendChild(item);
    scrollAiChat();
  };

  const submitAiMessage = async (value) => {
    const message = String(value || '').trim();
    if (!message || !aiChatInput) return;

    addAiMessage(message, 'user');
    aiConversation.push({ role: 'user', content: message });
    aiChatInput.value = '';
    setAiLoading(true);

    try {
      const response = await fetch('api/ai_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message, messages: aiConversation.slice(-8) })
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.error || 'AI hiện chưa sẵn sàng.');

      aiConversation.push({ role: 'assistant', content: payload.answer || '' });
      setAiLoading(false);
      addAiMessage(payload.answer || 'Mình chưa nhận được câu trả lời phù hợp.', 'assistant', payload.movies || []);
    } catch (error) {
      setAiLoading(false);
      addAiMessage(error.message || 'Không thể kết nối AI lúc này.', 'assistant');
    }
  };

  if (aiLauncher && aiChatbox) {
    aiLauncher.addEventListener('click', () => {
      const isOpen = aiChatbox.classList.toggle('is-open');
      aiChatbox.setAttribute('aria-hidden', String(!isOpen));
      if (isOpen) aiChatInput?.focus();
    });
  }
  aiChatClose?.addEventListener('click', () => {
    aiChatbox?.classList.remove('is-open');
    aiChatbox?.setAttribute('aria-hidden', 'true');
  });
  aiChatForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    submitAiMessage(aiChatInput?.value || '');
  });
  aiChatInput?.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      aiChatForm?.requestSubmit();
    }
  });
  document.querySelectorAll('[data-ai-prompt]').forEach((button) => {
    button.addEventListener('click', () => submitAiMessage(button.dataset.aiPrompt || ''));
  });

  syncPlayerUi();

  /* ---------- Watch page: server selection ---------- */
  const serverChips = document.querySelectorAll('.server-chip');
  serverChips.forEach((chip) => {
    chip.addEventListener('click', () => {
      serverChips.forEach((c) => c.classList.remove('is-active'));
      chip.classList.add('is-active');
    });
  });

  /* ---------- Sticky header shrink-on-scroll ---------- */
  const header = document.querySelector('.site-header');
  if (header) {
    let lastY = window.scrollY;
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      header.style.boxShadow = y > 12 ? '0 8px 24px rgba(0,0,0,0.35)' : 'none';
      lastY = y;
    }, { passive: true });
  }

});