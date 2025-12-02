<?php
/**
 * Plugin Name: Tailwind Previewer
 * Description: A developer tool to display raw Tailwind HTML with a live rendered preview and syntax-highlighted source code via shortcode.
 * Version: 1.0.0
 * Author: Chris McCoy
 * Text Domain: tw-preview
 * License: GPL-2.0+
 *
 * @package TailwindPreviewer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class Tailwind_Previewer
 *
 * Handles the registration, asset management, and rendering
 * for the Tailwind HTML previewer shortcode.
 */
class Tailwind_Previewer {

    /**
     * Plugin version for cache busting.
     */
    const VERSION = '1.0.0';

    /**
     * Tailwind_Previewer constructor.
     */
    public function __construct() {
        add_shortcode( 'tw_preview', [ $this, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Enqueues external and internal scripts and styles.
     *
     * This method checks if the current post contains the [tw_preview] shortcode
     * to prevent loading heavy assets on pages where they are not needed.
     */
    public function enqueue_assets() {
        global $post;

        // Verify we have a post object and the shortcode exists in the content.
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'tw_preview' ) ) {

            // Load Tailwind Play CDN.
            wp_enqueue_script( 'tailwindcss', 'https://cdn.tailwindcss.com', [], '3.4.0', false );

            // Load Prism.js CSS
            wp_enqueue_style( 'prism-css', 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css', [], '1.29.0' );

            // Load Prism.js Core Script.
            wp_enqueue_script( 'prism-js', 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js', [], '1.29.0', true );

            // Load Plugin CSS.
            wp_enqueue_style(
                'tw-previewer-css',
                plugin_dir_url( __FILE__ ) . 'assets/css/tw-preview.css',
                [],
                self::VERSION
            );

            // Load Plugin JS.
            wp_enqueue_script(
                'tw-previewer-js',
                plugin_dir_url( __FILE__ ) . 'assets/js/tw-preview.js',
                [],
                self::VERSION,
                true // Load in footer.
            );
        }
    }

    /**
     * Renders the [tw_preview] shortcode.
     */
    public function render_shortcode( $atts, $content = null ) {
        if ( ! $content ) {
            return '';
        }

        $uid = 'tw-' . uniqid();

        $rendered_html = do_shortcode( shortcode_unautop( $content ) );

        $clean_source = trim( $content );
        $clean_source = preg_replace( '/^<p>|<\/p>$/', '', $clean_source );
        $clean_source = preg_replace( '/<br\s*\/?>/i', "", $clean_source );
        $clean_source = str_replace( '<p></p>', '', $clean_source );
        $source_code  = esc_html( $clean_source );

        ob_start();
        ?>
        <div class="twp-wrapper" id="<?php echo esc_attr( $uid ); ?>">

            <div class="twp-header">
                <div class="twp-tabs">
                    <button class="twp-tab-btn active" onclick="TWP.switchTab('<?php echo $uid; ?>', 'preview')">
                        Preview
                    </button>
                    <button class="twp-tab-btn" onclick="TWP.switchTab('<?php echo $uid; ?>', 'code')">
                        Code
                    </button>
                </div>
                <div class="twp-actions">
                     <button class="twp-copy-btn" onclick="TWP.copyCode('<?php echo $uid; ?>', this)">
                        <span class="twp-copy-text">Copy</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                     </button>
                </div>
            </div>

            <div class="twp-content">
                <div class="twp-pane twp-preview-pane active" data-pane="preview">
                    <div class="twp-sandbox">
                        <?php echo $rendered_html; ?>
                    </div>
                </div>

                <div class="twp-pane twp-code-pane" data-pane="code">
                    <pre><code class="language-html" id="<?php echo $uid; ?>-source"><?php echo $source_code;
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new Tailwind_Previewer();
