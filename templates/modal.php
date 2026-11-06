<?php
/**
 * Plantilla del aviso de mayoría de edad.
 *
 * Sobrescribir en el tema: sjb-wp-mayor-edad/modal.php
 *
 * @package SJB_WP_Mayor_Edad
 * @var array{titulo:string,subtitulo:string,txtBtnAceptar:string,txtBtnRechazar:string} $textos
 */

defined('ABSPATH') || exit;
?>
<div class="modal-mayor-edad" role="dialog" aria-modal="true" aria-labelledby="sjb-mayor-edad-titulo" aria-describedby="sjb-mayor-edad-subtitulo">
    <div class="modal-content">
        <p id="sjb-mayor-edad-titulo" class="titulo"><?php echo esc_html($textos['titulo']); ?></p>
        <div class="modal-buttons">
            <button type="button" class="btn btn-aceptar"><?php echo esc_html($textos['txtBtnAceptar']); ?></button>
            <button type="button" class="btn btn-rechazar"><?php echo esc_html($textos['txtBtnRechazar']); ?></button>
        </div>
        <p id="sjb-mayor-edad-subtitulo" class="subtitulo"><?php echo esc_html($textos['subtitulo']); ?></p>
    </div>
</div>
