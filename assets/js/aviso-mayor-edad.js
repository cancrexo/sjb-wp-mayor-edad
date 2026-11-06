/**
 * Modal de verificación de mayoría de edad.
 * El HTML sale de la plantilla PHP; aquí solo cookie, foco y botones.
 *
 * @author Daniel "Cancrexo" Prol <cancrexo@gmail.com>
 */
(() => {
    const config = window.sjbWpMayorEdad;

    if (!config || !config.cookieName) {
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

    const iniciarAviso = () => {
        const capa = document.querySelector('.modal-mayor-edad');

        if (!capa) {
            return;
        }

        if (getCookie(config.cookieName)) {
            capa.remove();
            return;
        }

        const btnAceptar = capa.querySelector('.btn-aceptar');
        const btnRechazar = capa.querySelector('.btn-rechazar');

        if (!btnAceptar || !btnRechazar) {
            return;
        }

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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarAviso);
    } else {
        iniciarAviso();
    }
})();
