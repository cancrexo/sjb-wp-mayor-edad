# SJB Aviso Mayor de Edad

Modal de verificación de mayoría de edad en todo el frontend. Si el visitante confirma que es mayor de 18 años, se guarda una cookie y el aviso no vuelve a salir. Si indica que es menor, se le envía fuera del sitio.

Versión actual: **1.1.0**

Autor: Daniel "Cancrexo" Prol · [cancrexo@gmail.com](mailto:cancrexo@gmail.com)

---

## Requisitos

- WordPress ≥ 5.8
- PHP ≥ 7.4

No tiene pantalla de ajustes. Cubre cualquier página pública, tienda de WooCommerce incluida. No distingue productos ni toca el carrito.

---

## Instalación

1. Copia la carpeta del plugin en `wp-content/plugins/sjb-wp-mayor-edad`.
2. En el escritorio de WordPress: **Plugins → Plugins instalados**.
3. Activa **SJB Aviso Mayor de Edad**.

---

## Qué hace

En cada página pública, si no existe la cookie `mayor_edad`:

1. Muestra un cuadro a pantalla completa. Los textos siguen el idioma de WordPress (locale del sitio, o el de la página si usas Polylang o WPML). Hay traducciones empaquetadas; si no hay una para el locale activo, salen en inglés.
2. **Sí, soy mayor de edad** guarda la cookie 7 días (`path=/`, `SameSite=Lax`, y `Secure` si la página va por HTTPS) y cierra el aviso.
3. **No, soy menor de edad** lleva a `https://www.google.com/`.

Con la cookie ya puesta, no se cargan el CSS ni el JS.

El foco entra en el botón de aceptar, el tabulador no sale de los dos botones y la página de detrás no hace scroll.

Duración, nombre de cookie y URL de rechazo están en `sjb-wp-mayor-edad.php` (`COOKIE_DAYS`, `COOKIE_NAME` y `REJECT_URL`) y se pueden filtrar (ver más abajo).

---

## Personalizar (tema)

Copia lo que quieras cambiar a una carpeta `sjb-wp-mayor-edad/` en el tema activo (hijo o padre). El plugin busca primero ahí y, si no hay archivo, usa el suyo.

| Archivo en el tema | Efecto |
|--------------------|--------|
| `sjb-wp-mayor-edad/modal.php` | Sustituye el HTML del aviso |
| `sjb-wp-mayor-edad/aviso-mayor-edad.css` | Sustituye por completo la hoja de estilos del plugin |

La plantilla por defecto está en `templates/modal.php` del plugin. Recibe `$textos` con `titulo`, `subtitulo`, `txtBtnAceptar` y `txtBtnRechazar`. Conserva las clases `.modal-mayor-edad`, `.btn-aceptar` y `.btn-rechazar` si quieres que el JS siga funcionando.

### Solo colores (sin copiar el CSS)

En el CSS del tema puedes redefinir las variables del modal:

```css
.modal-mayor-edad {
    --sjb-me-overlay: rgba(0, 0, 0, 0.55);
    --sjb-me-bg: #ffffff;
    --sjb-me-border: #222222;
    --sjb-me-btn: #1a1a1a;
}
```

### Filtros PHP

```php
add_filter('sjb_wp_mayor_edad_textos', function ($textos) {
    $textos['titulo'] = 'Solo mayores de 18';
    return $textos;
});

add_filter('sjb_wp_mayor_edad_reject_url', function () {
    return 'https://www.example.com/';
});

add_filter('sjb_wp_mayor_edad_cookie_days', function () {
    return 30;
});
```

También existe `sjb_wp_mayor_edad_cookie_name` para el nombre de la cookie.

---

## Traducciones

Dominio de texto: `sjb-wp-mayor-edad`. Los textos base están en inglés y se traducen con los archivos de `languages/`:

| Archivo | Uso |
|---------|-----|
| `sjb-wp-mayor-edad.pot` | Plantilla con las cadenas |
| `sjb-wp-mayor-edad-es_ES.po` / `.mo` | Español |
| `sjb-wp-mayor-edad-gl_ES.po` / `.mo` | Gallego |
| `sjb-wp-mayor-edad-fr_FR.po` / `.mo` | Francés |
| `sjb-wp-mayor-edad-it_IT.po` / `.mo` | Italiano |
| `sjb-wp-mayor-edad-de_DE.po` / `.mo` | Alemán |
| `sjb-wp-mayor-edad-pl_PL.po` / `.mo` | Polaco |
| `sjb-wp-mayor-edad-da_DK.po` / `.mo` | Danés |
| `sjb-wp-mayor-edad-sv_SE.po` / `.mo` | Sueco |
| `sjb-wp-mayor-edad-fi.po` / `.mo` | Finlandés |
| `sjb-wp-mayor-edad-ru_RU.po` / `.mo` | Ruso |
| `sjb-wp-mayor-edad-nb_NO.po` / `.mo` | Noruego (bokmål) |

Para añadir un idioma, copia el `.pot` como `sjb-wp-mayor-edad-<locale>.po`, tradúcelo y compílalo a `.mo`:

```bash
msgfmt -o languages/sjb-wp-mayor-edad-<locale>.mo languages/sjb-wp-mayor-edad-<locale>.po
```

Si cambias o añades un texto en el código, regenera la plantilla:

```bash
wp i18n make-pot . languages/sjb-wp-mayor-edad.pot --slug=sjb-wp-mayor-edad --exclude=.git
```

Si la web cachea el HTML, una visita que ya aceptó puede dejar en caché la página sin el aviso. En ese caso un visitante nuevo no lo vería.
