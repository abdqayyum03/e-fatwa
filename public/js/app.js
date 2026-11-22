/* e-Fatwa interactions: small, dependency-free, progressive enhancement. */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  /* ---------- Theme (light / dark), remembered per browser ---------- */
  const themeBtn = $('#themeToggle');
  const applyThemeIcon = () => {
    if (!themeBtn) return;
    const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    themeBtn.innerHTML = dark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
    themeBtn.setAttribute('aria-label', dark ? 'Tukar ke mod cerah' : 'Tukar ke mod gelap');
  };
  applyThemeIcon();
  themeBtn?.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    try { localStorage.setItem('ef-theme', next); } catch (e) { /* storage blocked: theme lasts this page only */ }
    applyThemeIcon();
  });

  /* ---------- Navbar shadow + back-to-top + reading progress ---------- */
  const nav = $('.ef-nav');
  const toTop = $('.to-top');
  const progress = $('.reading-progress');
  const doc = $('[data-reading]');
  const onScroll = () => {
    const y = window.scrollY;
    nav?.classList.toggle('is-scrolled', y > 8);
    toTop?.classList.toggle('show', y > 600);
    if (progress && doc) {
      const start = doc.offsetTop - window.innerHeight * 0.3;
      const total = doc.offsetHeight;
      const pct = Math.min(100, Math.max(0, ((y - start) / total) * 100));
      progress.style.width = pct + '%';
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  toTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }));

  /* ---------- Scroll reveal + animated counters + bars ---------- */
  const animateCount = (el) => {
    const target = parseInt(el.dataset.count, 10) || 0;
    if (reduceMotion || target === 0) { el.textContent = target.toLocaleString('ms-MY'); return; }
    const duration = 1200;
    const start = performance.now();
    const tick = (now) => {
      const t = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - t, 3);
      el.textContent = Math.round(target * eased).toLocaleString('ms-MY');
      if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };

  const onVisible = (el) => {
    el.classList.add('is-visible');
    $$('[data-count]', el).concat(el.matches('[data-count]') ? [el] : []).forEach(animateCount);
    $$('.bar-fill[data-width]', el).forEach((bar) => { bar.style.width = bar.dataset.width + '%'; });
  };

  const watched = $$('.reveal, [data-count], .bar-fill[data-width]').map((el) => el.closest('.reveal') || el);
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) { onVisible(entry.target); io.unobserve(entry.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    [...new Set(watched)].forEach((el) => io.observe(el));
  } else {
    watched.forEach(onVisible);
  }

  /* ---------- Live search suggestions ---------- */
  const escapeHtml = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const highlight = (text, q) => {
    const safe = escapeHtml(text);
    const words = q.trim().split(/\s+/).filter((w) => w.length > 1).map((w) => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    return words.length ? safe.replace(new RegExp('(' + words.join('|') + ')', 'gi'), '<mark>$1</mark>') : safe;
  };

  $$('[data-suggest]').forEach((input) => {
    const box = document.createElement('div');
    box.className = 'suggest';
    box.setAttribute('role', 'listbox');
    input.parentElement.appendChild(box);
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('aria-autocomplete', 'list');

    let timer = null;
    let controller = null;
    let active = -1;

    const close = () => { box.classList.remove('show'); active = -1; };
    const links = () => $$('a', box);

    const render = (items, q) => {
      const all = `<a class="all" href="${input.form.action}?search=${encodeURIComponent(q)}"><i class="bi bi-search me-1"></i> Lihat semua hasil untuk “${escapeHtml(q)}”</a>`;
      box.innerHTML = items.length
        ? items.map((f) => `<a href="${f.url}" role="option"><span>${highlight(f.title, q)}</span><span class="meta">${escapeHtml(f.state || '')}${f.year ? ' · ' + f.year : ''}</span></a>`).join('') + all
        : `<a class="all" href="${input.form.action}?search=${encodeURIComponent(q)}">Tiada cadangan. Cari “${escapeHtml(q)}” dalam teks penuh →</a>`;
      box.classList.add('show');
      active = -1;
    };

    input.addEventListener('input', () => {
      const q = input.value.trim();
      clearTimeout(timer);
      if (q.length < 2) { close(); return; }
      timer = setTimeout(async () => {
        controller?.abort();
        controller = new AbortController();
        try {
          const res = await fetch(input.dataset.suggest + '?q=' + encodeURIComponent(q), { signal: controller.signal, headers: { Accept: 'application/json' } });
          if (res.ok) render(await res.json(), q);
        } catch (e) { /* aborted or offline: keep the plain form working */ }
      }, 180);
    });

    input.addEventListener('keydown', (e) => {
      const items = links();
      if (!box.classList.contains('show') || !items.length) return;
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        active = (active + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
        items.forEach((a, i) => a.classList.toggle('active', i === active));
        items[active].scrollIntoView({ block: 'nearest' });
      } else if (e.key === 'Enter' && active >= 0) {
        e.preventDefault();
        window.location = items[active].href;
      } else if (e.key === 'Escape') {
        close();
      }
    });

    document.addEventListener('click', (e) => { if (!input.parentElement.contains(e.target)) close(); });
  });

  /* ---------- "/" focuses the search box ---------- */
  document.addEventListener('keydown', (e) => {
    if (e.key !== '/' || e.ctrlKey || e.metaKey) return;
    const tag = (document.activeElement?.tagName || '').toLowerCase();
    if (['input', 'textarea', 'select'].includes(tag) || document.activeElement?.isContentEditable) return;
    const target = $('[data-primary-search]') || $('[data-suggest]');
    if (target) { e.preventDefault(); target.focus(); target.select(); }
  });

  /* ---------- Filters submit as soon as a dropdown changes ---------- */
  $$('form[data-autosubmit] select').forEach((sel) => sel.addEventListener('change', () => sel.form.requestSubmit()));

  /* ---------- Toasts from flash messages ---------- */
  if (window.bootstrap) {
    $$('.toast').forEach((t) => new bootstrap.Toast(t, { delay: 4500 }).show());
    $$('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
  }

  /* ---------- Reading tools on a fatwa page ---------- */
  const docEl = $('.fatwa-doc');
  $$('[data-font]').forEach((btn) => btn.addEventListener('click', () => {
    if (!docEl) return;
    const current = parseFloat(getComputedStyle(docEl).getPropertyValue('--doc-size')) || 1;
    if (btn.dataset.font === 'serif') { docEl.classList.toggle('font-serif'); return; }
    const next = Math.min(1.4, Math.max(0.85, current + (btn.dataset.font === 'up' ? 0.1 : -0.1)));
    docEl.style.setProperty('--doc-size', next.toFixed(2) + 'rem');
  }));

  $$('[data-copy]').forEach((btn) => btn.addEventListener('click', async () => {
    const original = btn.innerHTML;
    try {
      await navigator.clipboard.writeText(btn.dataset.copy || window.location.href);
      btn.innerHTML = '<i class="bi bi-check2"></i> Disalin';
    } catch (e) {
      btn.innerHTML = '<i class="bi bi-x"></i> Gagal';
    }
    setTimeout(() => { btn.innerHTML = original; }, 1800);
  }));
})();
