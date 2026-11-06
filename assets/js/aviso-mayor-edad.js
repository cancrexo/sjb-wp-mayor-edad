/**
 * Modal de verificación de mayoría de edad.
 * Textos, cookie y URL de rechazo llegan desde PHP (window.sjbWpMayorEdad).
 *
 * @author Daniel "Cancrexo" Prol <cancrexo@gmail.com>
 */
(() => {
    const config = window.sjbWpMayorEdad;

    if (!config || !config.cookieName || !config.textos) {
        return;
    }

    const getCookie = (name) => {
        const cookies = document.cookie ? document.cookie.split('; ') : [];
        const prefijo = `${name}=`;
        const encontrada = cookies.find((fila) => fila.startsWith(prefijo));

        if (!encontrada) {
            return undefined;
        }

        return decodeURIComponent(encontrada.slice(prefijo.length));
    };

    const setCookie = (nombre, valor, dias, secure) => {
        const caduca = new Date();
        caduca.setTime(caduca.getTime() + (dias * 24 * 60 * 60 * 1000));
        const secureFlag = secure ? ';Secure' : '';

        document.cookie = `${nombre}=${encodeURIComponent(valor)};expires=${caduca.toUTCString()};path=/;SameSite=Lax${secureFlag}`;
    };

    const displayAviso = () => {
        if (getCookie(config.cookieName)) {
            return;
        }

        const textos = config.textos;

        const capa = document.createElement('div');
        capa.className = 'modal-mayor-edad';
        capa.setAttribute('role', 'dialog');
        capa.setAttribute('aria-modal', 'true');
        capa.setAttribute('aria-labelledby', 'sjb-mayor-edad-titulo');
        capa.setAttribute('aria-describedby', 'sjb-mayor-edad-subtitulo');

        const contenido = document.createElement('div');
        contenido.className = 'modal-content';

        const titulo = document.createElement('p');
        titulo.id = 'sjb-mayor-edad-titulo';
        titulo.className = 'titulo';
        titulo.textContent = textos.titulo;

        const botones = document.createElement('div');
        botones.className = 'modal-buttons';

        const btnAceptar = document.createElement('button');
        btnAceptar.type = 'button';
        btnAceptar.className = 'btn btn-aceptar';
        btnAceptar.textContent = textos.txtBtnAceptar;

        const btnRechazar = document.createElement('button');
        btnRechazar.type = 'button';
        btnRechazar.className = 'btn btn-rechazar';
        btnRechazar.textContent = textos.txtBtnRechazar;

        const subtitulo = document.createElement('p');
        subtitulo.id = 'sjb-mayor-edad-subtitulo';
        subtitulo.className = 'subtitulo';
        subtitulo.textContent = textos.subtitulo;

        botones.append(btnAceptar, btnRechazar);
        contenido.append(titulo, botones, subtitulo);
        capa.append(contenido);
        document.body.prepend(capa);
        document.body.classList.add('sjb-mayor-edad-abierto');

        const focables = [btnAceptar, btnRechazar];

        capa.addEventListener('keydown', (event) => {
            if (event.key !== 'Tab') {
                return;
            }

            const primero = focables[0];
            const ultimo = focables[focables.length - 1];

            if (event.shiftKey && document.activeElement === primero) {
                event.preventDefault();
                ultimo.focus();
                return;
            }

            if (!event.shiftKey && document.activeElement === ultimo) {
                event.preventDefault();
                primero.focus();
            }
        });

        btnAceptar.addEventListener('click', () => {
            setCookie(
                config.cookieName,
                '1',
                Number(config.cookieDays) || 7,
                Boolean(config.secure)
            );
            document.body.classList.remove('sjb-mayor-edad-abierto');
            capa.remove();
        });

        btnRechazar.addEventListener('click', () => {
            window.location.assign(config.rejectUrl);
        });

        btnAceptar.focus();
    };

    document.addEventListener('DOMContentLoaded', displayAviso);
})();
