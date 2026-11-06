# Changelog

Los cambios notables de SJB Aviso Mayor de Edad. La versión del plugin es la de la cabecera (`1.0.0`).

## 1.0.0 — 2026-11-06

- Modal de mayoría de edad en el frontend, también en WooCommerce.
- Aceptar guarda la cookie `mayor_edad` durante 7 días (`path=/`, `SameSite=Lax` y `Secure` en HTTPS).
- Rechazar redirige a `https://www.google.com/`.
- Textos traducibles (dominio `sjb-wp-mayor-edad`, inglés como base) con plantilla `.pot` y traducciones en `languages/` a español, gallego, francés, italiano, alemán, polaco, danés, sueco, finlandés, ruso y noruego (`nb_NO`). Siguen el idioma de WordPress.
- Duración, nombre de cookie y URL de rechazo salen de PHP.
- Con la cookie ya puesta no se cargan el CSS ni el JS.
- El aviso es un diálogo: foco en aceptar, tabulador atrapado y sin scroll de la página de detrás.
