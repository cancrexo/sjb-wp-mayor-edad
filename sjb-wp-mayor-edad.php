<?php
/**
 * Plugin Name: SJB Aviso Mayor de Edad
 * Plugin URI: https://www.sjbdixital.es
 * Description: Modal de verificación de mayoría de edad (cookie + redirección). Compatible con WooCommerce.
 * Version: 1.0.0
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

    const VERSION = '1.0.0';

    /** Nombre de la cookie de aceptación. */
    const COOKIE_NAME = 'mayor_edad';

    /** Días que se recuerda la aceptación. */
    const COOKIE_DAYS = 7;

    /** Destino si el visitante indica que es menor. */
    const REJECT_URL = 'https://www.google.com/';

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
        if (is_admin()) {
            return;
        }

        $cookie = isset($_COOKIE[self::COOKIE_NAME])
            ? sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_NAME]))
            : '';

        if ('' !== $cookie) {
            return;
        }

        $assets = $this->plugin_url . '/assets';

        wp_enqueue_style(
            'sjb-wp-mayor-edad',
            $assets . '/css/aviso-mayor-edad.css',
            [],
            self::VERSION
        );

        wp_enqueue_script(
            'sjb-wp-mayor-edad',
            $assets . '/js/aviso-mayor-edad.js',
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
     * Datos que consume el modal en el navegador.
     *
     * @return array<string, mixed>
     */
    private function datos_script() {
        return [
            'cookieName' => self::COOKIE_NAME,
            'cookieDays' => self::COOKIE_DAYS,
            'rejectUrl' => self::REJECT_URL,
            'secure' => is_ssl(),
            'textos' => [
                'titulo' => __('You must be of legal age to access this site', 'sjb-wp-mayor-edad'),
                'subtitulo' => __('Please confirm that you are over 18 years old', 'sjb-wp-mayor-edad'),
                'txtBtnAceptar' => __('Yes, I am of legal age', 'sjb-wp-mayor-edad'),
                'txtBtnRechazar' => __('No, I am underage', 'sjb-wp-mayor-edad'),
            ],
        ];
    }

    private function __clone() {}

    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }
}

SJB_WP_Mayor_Edad::get_instance();
