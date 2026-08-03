(function () {
    function ensureStyles() {
        if (document.getElementById('emx-modal-styles')) return;
        const style = document.createElement('style');
        style.id = 'emx-modal-styles';
        style.textContent = `
        .emx-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.58);backdrop-filter:blur(5px);z-index:99999;display:flex;align-items:center;justify-content:center;padding:18px;animation:emxFade .14s ease-out}
        .emx-modal-card{width:min(94vw,460px);background:#fff;border:1px solid #e2e8f0;border-radius:22px;box-shadow:0 24px 70px rgba(15,23,42,.28);overflow:hidden;animation:emxPop .16s ease-out;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
        .emx-modal-head{padding:22px 24px 12px;display:flex;gap:14px;align-items:flex-start}
        .emx-modal-mark{width:42px;height:42px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;font-size:18px}
        .emx-modal-mark.info{background:#dbeafe;color:#1d4ed8}.emx-modal-mark.warning{background:#fef3c7;color:#b45309}.emx-modal-mark.danger{background:#fee2e2;color:#b91c1c}.emx-modal-mark.success{background:#dcfce7;color:#15803d}
        .emx-modal-title{margin:0;color:#0f172a;font-size:18px;font-weight:800;line-height:1.2}.emx-modal-text{margin:8px 0 0;color:#475569;font-size:14px;line-height:1.55;white-space:pre-line}
        .emx-modal-actions{padding:16px 24px 22px;display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap}.emx-btn{border:0;border-radius:13px;padding:10px 16px;font-weight:800;font-size:13px;cursor:pointer;transition:.15s}.emx-btn:focus{outline:3px solid rgba(59,130,246,.25)}
        .emx-btn-secondary{background:#f1f5f9;color:#334155}.emx-btn-secondary:hover{background:#e2e8f0}.emx-btn-primary{background:#2563eb;color:#fff}.emx-btn-primary:hover{background:#1d4ed8}.emx-btn-danger{background:#dc2626;color:#fff}.emx-btn-danger:hover{background:#b91c1c}.emx-btn-success{background:#059669;color:#fff}.emx-btn-success:hover{background:#047857}
        @keyframes emxFade{from{opacity:0}to{opacity:1}}@keyframes emxPop{from{transform:translateY(8px) scale(.98);opacity:.8}to{transform:translateY(0) scale(1);opacity:1}}
        `;
        document.head.appendChild(style);
    }

    function iconFor(type) {
        if (type === 'danger') return '<i class="fas fa-triangle-exclamation"></i>';
        if (type === 'warning') return '<i class="fas fa-circle-exclamation"></i>';
        if (type === 'success') return '<i class="fas fa-circle-check"></i>';
        return '<i class="fas fa-circle-info"></i>';
    }

    function closeModal(backdrop) {
        if (!backdrop) return;
        backdrop.remove();
        document.body.style.overflow = document.body.dataset.emxPrevOverflow || '';
        delete document.body.dataset.emxPrevOverflow;
    }

    function buildModal(opts) {
        ensureStyles();
        const type = opts.type || 'info';
        const backdrop = document.createElement('div');
        backdrop.className = 'emx-modal-backdrop';
        backdrop.innerHTML = `
            <div class="emx-modal-card" role="dialog" aria-modal="true" aria-labelledby="emx-modal-title"><div class="emx-modal-head"><div class="emx-modal-mark ${type}">${iconFor(type)}</div><div><h3 id="emx-modal-title" class="emx-modal-title"></h3><p class="emx-modal-text"></p></div></div><div class="emx-modal-actions"></div></div>`;
        backdrop.querySelector('.emx-modal-title').textContent = opts.title || 'Aviso';
        backdrop.querySelector('.emx-modal-text').textContent = opts.message || '';
        document.body.dataset.emxPrevOverflow = document.body.style.overflow || '';
        document.body.style.overflow = 'hidden';
        document.body.appendChild(backdrop);
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop && opts.closeOnBackdrop !== false) closeModal(backdrop);
        });
        document.addEventListener('keydown', function esc(ev) {
            if (ev.key === 'Escape') {
                document.removeEventListener('keydown', esc);
                closeModal(backdrop);
            }
        });
        return backdrop;
    }

    window.emxAlert = function (message, title, type) {
        const backdrop = buildModal({ message: String(message || ''), title: title || 'Aviso', type: type || 'warning' });
        const actions = backdrop.querySelector('.emx-modal-actions');
        const ok = document.createElement('button');
        ok.type = 'button';
        ok.className = 'emx-btn emx-btn-primary';
        ok.textContent = 'Entendido';
        ok.addEventListener('click', function () { closeModal(backdrop); });
        actions.appendChild(ok);
        ok.focus();
    };

    window.emxConfirm = function (message, onConfirm, options) {
        options = options || {};
        const backdrop = buildModal({
            message: String(message || ''),
            title: options.title || 'Confirmar acción',
            type: options.type || 'warning',
            closeOnBackdrop: false
        });
        const actions = backdrop.querySelector('.emx-modal-actions');
        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'emx-btn emx-btn-secondary';
        cancel.textContent = options.cancelText || 'Cancelar';
        const ok = document.createElement('button');
        ok.type = 'button';
        ok.className = 'emx-btn ' + (options.danger ? 'emx-btn-danger' : 'emx-btn-success');
        ok.textContent = options.okText || 'Confirmar';
        cancel.addEventListener('click', function () { closeModal(backdrop); });
        ok.addEventListener('click', function () {
            closeModal(backdrop);
            if (typeof onConfirm === 'function') onConfirm();
        });
        actions.appendChild(cancel);
        actions.appendChild(ok);
        ok.focus();
    };

    // Confirmaciones declarativas: enlaces, botones y formularios con data-emx-confirm.
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-emx-confirm]');
        if (!el || el.dataset.emxConfirmed === '1') return;
        if (el.tagName === 'FORM') return;
        e.preventDefault();
        e.stopPropagation();
        const msg = el.getAttribute('data-emx-confirm') || '¿Confirmas esta acción?';
        emxConfirm(msg, function () {
            el.dataset.emxConfirmed = '1';
            if (el.tagName === 'A' && el.href) {
                window.location.href = el.href;
            } else if ((el.tagName === 'BUTTON' || el.tagName === 'INPUT') && el.form) {
                if (el.name && !el.form.querySelector('input[type="hidden"][data-emx-submit-name="' + CSS.escape(el.name) + '"]')) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = el.name;
                    hidden.value = el.value || '';
                    hidden.setAttribute('data-emx-submit-name', el.name);
                    el.form.appendChild(hidden);
                }
                el.form.submit();
            } else {
                el.click();
            }
        }, { danger: el.classList.contains('text-red-600') || el.classList.contains('bg-red-600') });
    }, true);

    document.addEventListener('submit', function (e) {
        const form = e.target.closest('form[data-emx-confirm]');
        if (!form || form.dataset.emxConfirmed === '1') return;
        e.preventDefault();
        const msg = form.getAttribute('data-emx-confirm') || '¿Confirmas esta acción?';
        emxConfirm(msg, function () {
            form.dataset.emxConfirmed = '1';
            form.submit();
        }, { danger: form.getAttribute('data-emx-danger') === '1' });
    }, true);
})();
