/* e-Fatwa interactions: small, dependency-free, progressive enhancement. */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  // Interface strings in the current language, provided by the layout (window.efI18n).
  const t = (key, vars = {}) => (window.efI18n?.[key] ?? key).replace(/:(\w+)/g, (m, k) => (k in vars ? vars[k] : m));

  /* ---------- Theme (light / dark), remembered per browser ---------- */
  const themeBtn = $('#themeToggle');
  const applyThemeIcon = () => {
    if (!themeBtn) return;
    const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    themeBtn.innerHTML = dark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
    themeBtn.setAttribute('aria-label', dark ? t('Tukar ke mod cerah') : t('Tukar ke mod gelap'));
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
      const all = `<a class="all" href="${input.form.action}?search=${encodeURIComponent(q)}"><i class="bi bi-search me-1"></i> ${t('Lihat semua hasil untuk “:q”', { q: escapeHtml(q) })}</a>`;
      box.innerHTML = items.length
        ? items.map((f) => `<a href="${f.url}" role="option"><span>${highlight(f.title, q)}</span><span class="meta">${escapeHtml(f.state || '')}${f.year ? ' · ' + f.year : ''}</span></a>`).join('') + all
        : `<a class="all" href="${input.form.action}?search=${encodeURIComponent(q)}">${t('Tiada cadangan. Cari “:q” dalam teks penuh →', { q: escapeHtml(q) })}</a>`;
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

  /* ---------- Prayer times (JAKIM e-Solat via /waktu-solat) ---------- */
  $$('[data-prayer-widget]').forEach((widget) => {
    const select = $('[data-prayer-zone]', widget);
    const list = $('[data-prayer-list]', widget);
    const next = $('[data-prayer-next]', widget);
    const countdown = $('[data-prayer-countdown]', widget);
    const errorEl = $('[data-prayer-error]', widget);
    let timer = null;
    let data = null;

    // Prayer times are Malaysian local time, whatever the visitor's own timezone is.
    const nowInMalaysia = () => new Date(new Date().toLocaleString('en-US', { timeZone: 'Asia/Kuala_Lumpur' }));
    const at = (hhmm) => { const d = nowInMalaysia(); const [h, m] = hhmm.split(':').map(Number); d.setHours(h, m, 0, 0); return d; };
    const pad = (n) => String(n).padStart(2, '0');

    const tick = () => {
      if (!data) return;
      const now = nowInMalaysia();
      // Imsak and Syuruk are times, not prayers: skip them when picking "next".
      const order = Object.keys(data.times).filter((k) => !['imsak', 'syuruk'].includes(k));
      let nextKey = order.find((k) => at(data.times[k]) > now);
      let target = nextKey ? at(data.times[nextKey]) : null;
      if (!nextKey) { nextKey = order[0]; target = at(data.times[nextKey]); target.setDate(target.getDate() + 1); }

      $$('li', list).forEach((li) => li.classList.toggle('is-next', li.dataset.prayer === nextKey));
      $('[data-prayer-next-name]', widget).textContent = data.labels[nextKey];
      $('[data-prayer-next-time]', widget).textContent = data.times[nextKey];
      const s = Math.max(0, Math.floor((target - now) / 1000));
      countdown.textContent = pad(Math.floor(s / 3600)) + ':' + pad(Math.floor((s % 3600) / 60)) + ':' + pad(s % 60);
      next.hidden = false;
    };

    const load = async (zone) => {
      errorEl.hidden = true;
      $('[data-prayer-date]', widget).textContent = t('Memuatkan…');
      try {
        const res = await fetch(widget.dataset.endpoint + '?zone=' + encodeURIComponent(zone), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || t('Ralat'));
        data = json;
        $$('li', list).forEach((li) => { $('b', li).textContent = data.times[li.dataset.prayer] || '--:--'; });
        $('[data-prayer-date]', widget).textContent = data.date + (data.hijri ? ' · ' + data.hijri + 'H' : '');
        clearInterval(timer);
        tick();
        timer = setInterval(tick, 1000);
      } catch (e) {
        data = null;
        next.hidden = true;
        errorEl.textContent = e.message || t('Waktu solat tidak dapat dimuatkan.');
        errorEl.hidden = false;
        $('[data-prayer-date]', widget).textContent = '';
      }
    };

    let zone = widget.dataset.defaultZone;
    try { zone = localStorage.getItem('ef-prayer-zone') || zone; } catch (e) { /* storage blocked */ }
    if (![...select.options].some((o) => o.value === zone)) zone = widget.dataset.defaultZone;
    select.value = zone;
    select.addEventListener('change', () => {
      try { localStorage.setItem('ef-prayer-zone', select.value); } catch (e) { /* storage blocked */ }
      load(select.value);
    });
    load(zone);
  });

  /* ---------- Bookmark button: save without reloading ---------- */
  $$('form[data-bookmark]').forEach((form) => form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = $('button', form);
    btn.disabled = true;
    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': $('input[name=_token]', form).value },
      });
      if (!res.ok) throw new Error();
      const { saved } = await res.json();
      btn.classList.toggle('btn-gold', saved);
      btn.classList.toggle('btn-outline-primary', !saved);
      btn.setAttribute('aria-pressed', saved);
      $('i', btn).className = 'bi ' + (saved ? 'bi-bookmark-check-fill' : 'bi-bookmark');
      $('span', btn).textContent = saved ? btn.dataset.labelOn : btn.dataset.labelOff;
    } catch (err) {
      form.submit(); // fall back to a normal form post
    } finally {
      btn.disabled = false;
    }
  }));

  /* ---------- Tanya AI: ask without reloading ---------- */
  $$('form[data-ask]').forEach((form) => {
    const textarea = $('textarea', form);
    const output = $('[data-ask-result]');
    const button = $('button[type=submit]', form);

    $$('[data-ask-example]', form).forEach((chip) => chip.addEventListener('click', () => {
      textarea.value = chip.textContent.trim();
      textarea.focus();
    }));

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!form.reportValidity()) return;
      button.disabled = true;
      const label = button.innerHTML;
      button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + t('Sedang berfikir…');
      output.innerHTML = '<div class="surface p-4"><div class="placeholder-glow"><span class="placeholder col-10"></span><span class="placeholder col-8"></span><span class="placeholder col-11"></span><span class="placeholder col-6"></span></div></div>';
      try {
        const res = await fetch(form.action, {
          method: 'POST',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': $('input[name=_token]', form).value },
          body: new FormData(form),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || (json.errors && Object.values(json.errors)[0][0]) || t('Ralat'));
        output.innerHTML = json.card;
        output.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
      } catch (err) {
        output.innerHTML = '';
        const alert = document.createElement('div');
        alert.className = 'alert alert-warning';
        alert.textContent = err.message;
        output.appendChild(alert);
      } finally {
        button.disabled = false;
        button.innerHTML = label;
      }
    });
  });

  /* ---------- Show / hide password ---------- */
  $$('[data-toggle-password]').forEach((btn) => btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.togglePassword);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    $('i', btn).className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  }));

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
      btn.innerHTML = '<i class="bi bi-check2"></i> ' + t('Disalin');
    } catch (e) {
      btn.innerHTML = '<i class="bi bi-x"></i> ' + t('Gagal');
    }
    setTimeout(() => { btn.innerHTML = original; }, 1800);
  }));
})();
