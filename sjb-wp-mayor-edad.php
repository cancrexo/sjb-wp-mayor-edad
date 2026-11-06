<?php
/**
 * Plugin Name: SJB Aviso Mayor de Edad
 * Plugin URI: https://www.sjbdixital.es
 * Description: Modal de verificación de mayoría de edad (cookie + redirección). Compatible con WooCommerce.
 * Version: 1.1.0
 * Author: Daniel "Cancrexo" Prol
 * Author URI: https://www.sjbdixital.es
 * Author Email: cancrexo@gmail.com
 * Text Domain: sjb-wp-mayor-edad
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package SJB_WP_Mayor_Edad
 */

defined('ABSPATH') || exit;

/**
 * Plugin aviso mayor de edad (singleton).
 */
class SJB_WP_Mayor_Edad {

    const VERSION = '1.1.0';

    /** Nombre de la cookie de aceptación. */
    const COOKIE_NAME = 'mayor_edad';

    /** Días que se recuerda la aceptación. */
    const COOKIE_DAYS = 7;

    /** Destino si el visitante indica que es menor. */
    const REJECT_URL = 'https://www.google.com/';

    /** Carpeta de plantillas en el tema (hijo o padre). */
    const THEME_DIR = 'sjb-wp-mayor-edad';

    /** @var self|null */
    protected static $instance = null;

    /** @var string URL base del plugin (sin slash final) */
    private $plugin_url;

    /** @var string Ruta absoluta del plugin (con slash final) */
    private $plugin_dir;

    /**
     * Instancia única.
     *
     * @return self
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->plugin_url = untrailingslashit(plugins_url('', __FILE__));
        $this->plugin_dir = plugin_dir_path(__FILE__);

        add_action('init', [$this, 'load_textdomain']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_modal'], 5);
    }

    /**
     * Carga las traducciones desde /languages.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'sjb-wp-mayor-edad',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Encola JS y CSS solo en frontend, y solo si aún no hay aceptación.
     */
    public function enqueue_assets() {
        if (is_admin() || $this->ha_aceptado()) {
            return;
        }

        $css = $this->recurso_estilo();

        wp_enqueue_style(
            'sjb-wp-mayor-edad',
            $css['url'],
            [],
            $css['ver']
        );

        wp_enqueue_script(
            'sjb-wp-mayor-edad',
            $this->plugin_url . '/assets/js/aviso-mayor-edad.js',
            [],
            self::VERSION,
            true
        );

        wp_add_inline_script(
            'sjb-wp-mayor-edad',
            'window.sjbWpMayorEdad = ' . wp_json_encode($this->datos_script()) . ';',
            'before'
        );
    }

    /**
     * Imprime el modal (plantilla del tema o del plugin).
     */
    public function render_modal() {
        if (is_admin() || $this->ha_aceptado()) {
            return;
        }

        $textos = $this->textos();
        $plantilla = $this->localizar('modal.php');

        if ('' === $plantilla || ! is_readable($plantilla)) {
            return;
        }

        include $plantilla;
    }

    /**
     * Textos del aviso (traducibles + filtro).
     *
     * @return array{titulo:string,subtitulo:string,txtBtnAceptar:string,txtBtnRechazar:string}
     */
    private function textos() {
        $textos = [
            'titulo' => __('You must be of legal age to access this site', 'sjb-wp-mayor-edad'),
            'subtitulo' => __('Please confirm that you are over 18 years old', 'sjb-wp-mayor-edad'),
            'txtBtnAceptar' => __('Yes, I am of legal age', 'sjb-wp-mayor-edad'),
            'txtBtnRechazar' => __('No, I am underage', 'sjb-wp-mayor-edad'),
        ];

        /**
         * Filtra los textos del modal.
         *
         * @param array $textos Claves: titulo, subtitulo, txtBtnAceptar, txtBtnRechazar.
         */
        $textos = apply_filters('sjb_wp_mayor_edad_textos', $textos);

        return [
            'titulo' => isset($textos['titulo']) ? (string) $textos['titulo'] : '',
            'subtitulo' => isset($textos['subtitulo']) ? (string) $textos['subtitulo'] : '',
            'txtBtnAceptar' => isset($textos['txtBtnAceptar']) ? (string) $textos['txtBtnAceptar'] : '',
            'txtBtnRechazar' => isset($textos['txtBtnRechazar']) ? (string) $textos['txtBtnRechazar'] : '',
        ];
    }

    /**
     * Datos que consume el modal en el navegador.
     *
     * @return array<string, mixed>
     */
    private function datos_script() {
        $cookie_name = (string) apply_filters('sjb_wp_mayor_edad_cookie_name', self::COOKIE_NAME);
        $cookie_days = (int) apply_filters('sjb_wp_mayor_edad_cookie_days', self::COOKIE_DAYS);
        $reject_url = (string) apply_filters('sjb_wp_mayor_edad_reject_url', self::REJECT_URL);

        return [
            'cookieName' => $cookie_name !== '' ? $cookie_name : self::COOKIE_NAME,
            'cookieDays' => $cookie_days > 0 ? $cookie_days : self::COOKIE_DAYS,
            'rejectUrl' => $reject_url !== '' ? $reject_url : self::REJECT_URL,
            'secure' => is_ssl(),
        ];
    }

    /**
     * ¿El visitante ya aceptó (cookie presente)?
     */
    private function ha_aceptado() {
        $nombre = (string) apply_filters('sjb_wp_mayor_edad_cookie_name', self::COOKIE_NAME);
        if ('' === $nombre) {
            $nombre = self::COOKIE_NAME;
        }

        $cookie = isset($_COOKIE[$nombre])
            ? sanitize_text_field(wp_unslash($_COOKIE[$nombre]))
            : '';

        return '' !== $cookie;
    }

    /**
     * Ruta de un archivo: tema hijo → tema padre → plugin.
     *
     * @param string $nombre modal.php | aviso-mayor-edad.css
     * @return string Ruta absoluta o cadena vacía.
     */
    private function localizar($nombre) {
        $nombre = ltrim((string) $nombre, '/');
        $rel = self::THEME_DIR . '/' . $nombre;

        $candidatos = [
            trailingslashit(get_stylesheet_directory()) . $rel,
            trailingslashit(get_template_directory()) . $rel,
        ];

        foreach ($candidatos as $ruta) {
            if (is_readable($ruta)) {
                return $ruta;
            }
        }

        if ('modal.php' === $nombre) {
            return $this->plugin_dir . 'templates/modal.php';
        }

        if ('aviso-mayor-edad.css' === $nombre) {
            return $this->plugin_dir . 'assets/css/aviso-mayor-edad.css';
        }

        return '';
    }

    /**
     * URL y versión del CSS (tema o plugin).
     *
     * @return array{url:string,ver:string}
     */
    private function recurso_estilo() {
        $ruta = $this->localizar('aviso-mayor-edad.css');
        $estilo_tema = trailingslashit(get_stylesheet_directory()) . self::THEME_DIR . '/aviso-mayor-edad.css';
        $padre_tema = trailingslashit(get_template_directory()) . self::THEME_DIR . '/aviso-mayor-edad.css';

        if ($ruta === $estilo_tema) {
            return [
                'url' => trailingslashit(get_stylesheet_directory_uri()) . self::THEME_DIR . '/aviso-mayor-edad.css',
                'ver' => (string) filemtime($ruta),
            ];
        }

        if ($ruta === $padre_tema) {
            return [
                'url' => trailingslashit(get_template_directory_uri()) . self::THEME_DIR . '/aviso-mayor-edad.css',
                'ver' => (string) filemtime($ruta),
            ];
        }

        return [
            'url' => $this->plugin_url . '/assets/css/aviso-mayor-edad.css',
            'ver' => self::VERSION,
        ];
    }

    private function __clone() {}

    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }
}

SJB_WP_Mayor_Edad::get_instance();
