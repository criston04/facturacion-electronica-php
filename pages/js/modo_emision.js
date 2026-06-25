// Pantalla "Modo de Emisión": elegir tercero/certificado y configurar el emisor propio.
(function () {
    const URL = '../admin/classes/ConfigEmision.php';
    const data = window.MODO_DATA || { modo: 'tercero', sunat: {}, cert: {} };
    const el = (id) => document.getElementById(id);

    const state = {
        modo: data.modo || 'tercero',
        entorno: data.sunat.entorno || 'beta',
        certNombre: data.sunat.cert_nombre || 'certificado_prueba.pem',
        dirty: false,
    };

    const RES = {
        ok:   { bg: 'bg-emerald-50', bd: 'border-emerald-200', dot: 'bg-emerald-500', tx: 'text-emerald-700', icon: 'fa-check' },
        warn: { bg: 'bg-amber-50',   bd: 'border-amber-200',   dot: 'bg-amber-500',   tx: 'text-amber-700',   icon: 'fa-triangle-exclamation' },
        err:  { bg: 'bg-red-50',     bd: 'border-red-200',     dot: 'bg-red-500',     tx: 'text-red-700',     icon: 'fa-xmark' },
    };

    function post(body) {
        const opts = { method: 'POST' };
        opts.body = body instanceof FormData ? body : new URLSearchParams(body);
        return fetch(URL, opts).then((r) => r.json());
    }

    function marcarSucio() {
        state.dirty = true;
        el('dirtyHint').classList.remove('hidden');
    }

    function actualizarPill() {
        el('pillModo').textContent = state.modo === 'certificado' ? 'Certificado propio' : 'Proveedor (OSE/PSE)';
        el('pillEntorno').textContent = state.modo === 'certificado' ? '· ' + state.entorno.toUpperCase() : '';
    }

    // ---- Selección de modo ----
    function seleccionarModo(modo) {
        state.modo = modo;
        document.querySelectorAll('.me-card').forEach((c) => {
            const activo = c.dataset.modo === modo;
            c.classList.toggle('activa', activo);
            const radio = c.querySelector('input[type=radio]');
            if (radio) radio.checked = activo;
        });
        el('panelCertificado').classList.toggle('hidden', modo !== 'certificado');
        el('panelTercero').classList.toggle('hidden', modo !== 'tercero');
        actualizarPill();
        marcarSucio();
    }

    // ---- Estado del certificado ----
    function renderCert(cert) {
        const icon = el('certIcon'), txt = el('certEstadoTxt'), badge = el('certBadge');
        if (!cert || !cert.cargado) {
            icon.className = 'w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white shrink-0 bg-gray-400';
            txt.textContent = 'No hay certificado cargado. Sube tu .pem o genera uno de prueba.';
            badge.className = 'text-xs font-semibold px-3 py-1.5 rounded-full self-center bg-gray-100 text-gray-500';
            badge.textContent = 'No cargado';
            badge.classList.remove('hidden');
            return;
        }
        const vencido = !!cert.vencido;
        icon.className = 'w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white shrink-0 ' +
            (vencido ? 'bg-red-500' : 'bg-gradient-to-br from-teal-400 to-emerald-600');
        txt.innerHTML = 'CN: <b>' + (cert.cn || '—') + '</b> · vigencia hasta <b>' + (cert.hasta || '—') + '</b>' +
            ' · <span class="text-gray-400">' + (cert.nombre || '') + '</span>';
        if (vencido) {
            badge.className = 'text-xs font-semibold px-3 py-1.5 rounded-full self-center bg-red-100 text-red-600';
            badge.textContent = 'Vencido';
        } else {
            badge.className = 'text-xs font-semibold px-3 py-1.5 rounded-full self-center bg-emerald-100 text-emerald-600';
            badge.textContent = 'Vigente';
        }
        badge.classList.remove('hidden');
    }

    function refrescarCert() {
        post({ get_config: 1 }).then((d) => {
            renderCert(d.certificado);
            if (d.config && d.config.sunat) state.certNombre = d.config.sunat.cert_nombre || state.certNombre;
        });
    }

    // ---- Probar emisión ----
    function renderResultado(r) {
        const box = el('resultadoProbar');
        const ok = !!r.success;
        const st = ok ? RES.ok : (r.estado === 'rechazado' ? RES.err : RES.warn);
        box.innerHTML =
            '<div class="rounded-xl border p-4 flex items-start gap-3 ' + st.bg + ' ' + st.bd + '">' +
            '<div class="w-9 h-9 rounded-full ' + st.dot + ' text-white flex items-center justify-center shrink-0"><i class="fas ' + st.icon + '"></i></div>' +
            '<div><p class="font-semibold ' + st.tx + '">' + (ok ? 'Aceptado por SUNAT' : 'No aceptado') + (r.serie ? ' · ' + r.serie : '') + '</p>' +
            '<p class="text-sm ' + st.tx + ' opacity-90">' + (r.mensaje || '') + '</p>' +
            '<p class="text-xs text-gray-400 mt-1">Emisor: ' + (r.clase || '-') + ' · estado: ' + (r.estado || '-') + '</p></div></div>';
        box.classList.remove('hidden');
        box.classList.remove('me-fade'); void box.offsetWidth; box.classList.add('me-fade');
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderCert(data.cert);

        // Tarjetas de modo
        document.querySelectorAll('.me-card').forEach((c) => {
            c.addEventListener('click', () => seleccionarModo(c.dataset.modo));
        });

        // Segmentado entorno
        document.querySelectorAll('#segEntorno .seg').forEach((b) => {
            b.addEventListener('click', () => {
                state.entorno = b.dataset.entorno;
                document.querySelectorAll('#segEntorno .seg').forEach((x) => {
                    const on = x.dataset.entorno === state.entorno;
                    x.classList.toggle('on', on);
                    x.classList.toggle('text-gray-500', !on);
                });
                actualizarPill();
                marcarSucio();
            });
        });

        // Inputs → dirty
        ['ruc', 'usuario_sol', 'clave_sol'].forEach((id) => el(id).addEventListener('input', marcarSucio));

        // Mostrar/ocultar clave
        el('toggleClave').addEventListener('click', () => {
            const inp = el('clave_sol');
            inp.type = inp.type === 'password' ? 'text' : 'password';
            el('toggleClave').innerHTML = '<i class="fas fa-eye' + (inp.type === 'text' ? '-slash' : '') + '"></i>';
        });

        // Subir certificado
        el('fileCert').addEventListener('change', (e) => {
            const f = e.target.files[0];
            if (!f) return;
            const fd = new FormData();
            fd.append('subir_certificado', '1');
            fd.append('certificado', f);
            post(fd).then((r) => {
                if (r.status === 202) { toastr.success(r.message); refrescarCert(); }
                else toastr.error(r.message || 'No se pudo cargar el certificado');
            }).catch(() => toastr.error('Error al subir el certificado'));
        });

        // Generar certificado de prueba
        el('btnGenerarCert').addEventListener('click', () => {
            const b = el('btnGenerarCert');
            b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando…';
            post({ generar_cert_prueba: 1 }).then((r) => {
                if (r.status === 202) { toastr.success(r.message); refrescarCert(); }
                else toastr.error(r.message);
            }).finally(() => { b.disabled = false; b.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> Generar de prueba'; });
        });

        // Probar emisión
        el('btnProbar').addEventListener('click', () => {
            const b = el('btnProbar');
            b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando a SUNAT…';
            post({ probar_emision: 1 }).then((r) => {
                renderResultado(r);
                if (r.success) toastr.success('SUNAT aceptó el comprobante de prueba');
                else toastr.warning('SUNAT no aceptó (revisa el detalle)');
            }).catch(() => toastr.error('Error al probar la emisión'))
              .finally(() => { b.disabled = false; b.innerHTML = '<i class="fas fa-paper-plane"></i> Probar emisión a SUNAT'; });
        });

        // Guardar
        el('btnGuardar').addEventListener('click', () => {
            const b = el('btnGuardar');
            b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';
            post({
                guardar_config: 1,
                modo: state.modo,
                entorno: state.entorno,
                ruc: el('ruc').value,
                usuario_sol: el('usuario_sol').value,
                clave_sol: el('clave_sol').value,
                cert_nombre: state.certNombre,
            }).then((r) => {
                if (r.status === 202) {
                    toastr.success(r.message);
                    state.dirty = false;
                    el('dirtyHint').classList.add('hidden');
                } else toastr.error(r.message);
            }).catch(() => toastr.error('No se pudo guardar'))
              .finally(() => { b.disabled = false; b.innerHTML = '<i class="fas fa-floppy-disk"></i> Guardar cambios'; });
        });
    });
})();
