(() => {
  const forms = document.querySelectorAll('[data-emx-search-form]');
  if (!forms.length) return;

  const money = (value) => {
    const n = Number(value || 0);
    return '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  const normalizeDiscount = (value) => {
    const n = Number(value || 0);
    if (n > 0 && n <= 1) return n * 100;
    return n;
  };

  const debounce = (fn, wait = 220) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), wait);
    };
  };

  const escapeHtml = (str) => String(str || '').replace(/[&<>"']/g, (ch) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  }[ch]));

  forms.forEach((form) => {
    const input = form.querySelector('[data-emx-search-input]');
    const box = form.querySelector('[data-emx-search-results]');
    if (!input || !box) return;

    let controller = null;
    let activeIndex = -1;
    let currentItems = [];

    const close = () => {
      box.classList.add('hidden');
      box.innerHTML = '';
      activeIndex = -1;
      currentItems = [];
    };

    const open = () => box.classList.remove('hidden');

    const setActive = (next) => {
      const items = box.querySelectorAll('[data-emx-result-link]');
      if (!items.length) return;
      activeIndex = Math.max(0, Math.min(next, items.length - 1));
      items.forEach((el, idx) => {
        el.classList.toggle('bg-blue-50', idx === activeIndex);
        el.classList.toggle('text-blue-800', idx === activeIndex);
      });
    };

    const render = (data) => {
      const productos = Array.isArray(data.productos) ? data.productos : [];
      const categorias = Array.isArray(data.categorias) ? data.categorias : [];
      const marcas = Array.isArray(data.marcas) ? data.marcas : [];

      currentItems = [
        ...productos.map((p) => ({ type: 'producto', url: p.url, label: p.nombre })),
        ...categorias.map((c) => ({ type: 'categoria', url: c.url, label: c.nombre })),
        ...marcas.map((m) => ({ type: 'marca', url: m.url, label: m.nombre })),
      ];

      if (!productos.length && !categorias.length && !marcas.length) {
        box.innerHTML = `
          <div class="p-4 text-sm text-slate-500">
            No encontré coincidencias exactas.
            <button type="submit" class="block mt-2 font-bold text-blue-700 hover:text-blue-900">Buscar de todas formas</button>
          </div>`;
        open();
        return;
      }

      const productHtml = productos.length ? `
        <div class="p-2">
          <p class="px-3 py-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Productos encontrados</p>
          ${productos.map((p) => {
            const disc = normalizeDiscount(p.descuento_porcentaje);
            return `
              <a href="${escapeHtml(p.url)}" data-emx-result-link class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-blue-50 transition">
                <img src="${escapeHtml(p.imagen)}" alt="" class="w-11 h-11 rounded-xl object-cover bg-slate-100 border border-slate-100">
                <span class="min-w-0 flex-1">
                  <span class="block font-bold text-sm text-slate-900 truncate">${escapeHtml(p.nombre)}</span>
                  <span class="block text-xs text-slate-500 truncate">${escapeHtml(p.marca || 'Marca')} · ${escapeHtml(p.categoria || 'Categoría')}</span>
                </span>
                <span class="text-right shrink-0">
                  <span class="block text-sm font-black text-slate-900">${money(p.precio_base)}</span>
                  ${disc > 0 ? `<span class="block text-[10px] font-black text-red-600">${disc.toFixed(0)}% OFF</span>` : ''}
                </span>
              </a>`;
          }).join('')}
        </div>` : '';

      const categoriesHtml = categorias.length ? `
        <div class="border-t border-slate-100 p-2">
          <p class="px-3 py-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Categorías</p>
          ${categorias.map((c) => `
            <a href="${escapeHtml(c.url)}" data-emx-result-link class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-50 transition text-sm font-semibold text-slate-700">
              <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center"><i class="fas fa-layer-group text-xs"></i></span>
              ${escapeHtml(c.nombre)}
            </a>`).join('')}
        </div>` : '';

      const brandsHtml = marcas.length ? `
        <div class="border-t border-slate-100 p-2">
          <p class="px-3 py-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Marcas</p>
          ${marcas.map((m) => `
            <a href="${escapeHtml(m.url)}" data-emx-result-link class="inline-flex items-center gap-2 m-1 px-3 py-2 rounded-xl bg-slate-50 hover:bg-blue-50 text-sm font-bold text-slate-700 transition">
              <i class="fas fa-certificate text-blue-500 text-xs"></i>${escapeHtml(m.nombre)}
            </a>`).join('')}
        </div>` : '';

      box.innerHTML = `
        <div class="max-h-[480px] overflow-y-auto">
          ${productHtml}
          ${categoriesHtml}
          ${brandsHtml}
          <div class="border-t border-slate-100 p-2">
            <button type="submit" data-emx-result-link class="w-full text-left px-3 py-2 rounded-xl hover:bg-slate-50 text-sm font-black text-blue-700">
              Ver todos los resultados para "${escapeHtml(input.value.trim())}"
            </button>
          </div>
        </div>`;
      open();
    };

    const search = debounce(async () => {
      const q = input.value.trim();
      if (q.length < 2) {
        close();
        return;
      }

      if (controller) controller.abort();
      controller = new AbortController();

      box.innerHTML = '<div class="p-4 text-sm text-slate-500">Buscando sugerencias...</div>';
      open();

      try {
        const res = await fetch(`buscar_sugerencias.php?q=${encodeURIComponent(q)}`, {
          signal: controller.signal,
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        render(data);
      } catch (err) {
        if (err.name === 'AbortError') return;
        box.innerHTML = '<div class="p-4 text-sm text-slate-500">No se pudieron cargar sugerencias.</div>';
        open();
      }
    }, 180);

    input.addEventListener('input', search);
    input.addEventListener('focus', () => {
      if (input.value.trim().length >= 2) search();
    });

    input.addEventListener('keydown', (e) => {
      if (box.classList.contains('hidden')) return;
      const items = box.querySelectorAll('[data-emx-result-link]');
      if (!items.length) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        setActive(activeIndex + 1);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        setActive(activeIndex <= 0 ? items.length - 1 : activeIndex - 1);
      } else if (e.key === 'Enter' && activeIndex >= 0) {
        e.preventDefault();
        items[activeIndex].click();
      } else if (e.key === 'Escape') {
        close();
      }
    });

    document.addEventListener('click', (e) => {
      if (!form.contains(e.target)) close();
    });
  });
})();