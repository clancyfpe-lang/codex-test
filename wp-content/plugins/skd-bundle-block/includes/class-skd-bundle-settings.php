<?php
namespace SKD\BundleBlock;

/**
 * Handles settings and admin UI for the bundle block.
 */
class Settings {
    const OPTION_KEY          = 'skd_bundle_block_options';
    const OPTION_DISCOUNT_KEY = 'discount';
    const OPTION_SLOT_PREFIX  = 'slot_category_';

    /**
     * Singleton instance.
     *
     * @var Settings|null
     */
    private static $instance = null;

    /**
     * Cached options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Retrieve a singleton instance.
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register hooks.
     */
    public function register() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
    }

    /**
     * Register the settings, sections and fields.
     */
    public function register_settings() {
        register_setting( 'skd_bundle_block', self::OPTION_KEY, [ $this, 'sanitize_options' ] );

        add_settings_section(
            'skd_bundle_block_general',
            __( 'Настройки комплекта', 'skd-bundle-block' ),
            '__return_null',
            'skd_bundle_block'
        );

        add_settings_field(
            self::OPTION_DISCOUNT_KEY,
            __( 'Размер скидки (%)', 'skd-bundle-block' ),
            [ $this, 'render_discount_field' ],
            'skd_bundle_block',
            'skd_bundle_block_general'
        );

        for ( $i = 1; $i <= 3; $i++ ) {
            add_settings_field(
                self::OPTION_SLOT_PREFIX . $i,
                sprintf( __( 'Категория слота %d', 'skd-bundle-block' ), $i ),
                [ $this, 'render_slot_field' ],
                'skd_bundle_block',
                'skd_bundle_block_general',
                [ 'slot' => $i ]
            );
        }
    }

    /**
     * Register the settings page.
     */
    public function register_settings_page() {
        $parent = class_exists( '\WooCommerce' ) ? 'woocommerce' : 'options-general.php';

        add_submenu_page(
            $parent,
            __( 'Комплект товаров', 'skd-bundle-block' ),
            __( 'Комплект товаров', 'skd-bundle-block' ),
            'manage_options',
            'skd-bundle-block',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Sanitize settings values.
     *
     * @param array $value Raw option value.
     *
     * @return array
     */
    public function sanitize_options( $value ) {
        $sanitized = [
            self::OPTION_DISCOUNT_KEY => isset( $value[ self::OPTION_DISCOUNT_KEY ] ) ? floatval( $value[ self::OPTION_DISCOUNT_KEY ] ) : 0,
        ];

        for ( $i = 1; $i <= 3; $i++ ) {
            $key                = self::OPTION_SLOT_PREFIX . $i;
            $sanitized[ $key ] = isset( $value[ $key ] ) ? absint( $value[ $key ] ) : 0;
        }

        return $sanitized;
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Комплект товаров', 'skd-bundle-block' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'skd_bundle_block' );
                do_settings_sections( 'skd_bundle_block' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the discount field.
     */
    public function render_discount_field() {
        $value = $this->get_discount();
        ?>
        <input type="number" step="0.1" min="0" name="<?php echo esc_attr( self::OPTION_KEY . '[' . self::OPTION_DISCOUNT_KEY . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>" />
        <p class="description"><?php esc_html_e( 'Укажите значение скидки для комплекта. Например, 15 означает 15% скидки.', 'skd-bundle-block' ); ?></p>
        <?php
    }

    /**
     * Render the slot category field.
     *
     * @param array $args Field arguments.
     */
    public function render_slot_field( $args ) {
        $slot          = isset( $args['slot'] ) ? absint( $args['slot'] ) : 1;
        $current_value = $this->get_slot_category( $slot );

        if ( ! taxonomy_exists( 'product_cat' ) ) {
            printf(
                '<p class="description">%s</p>',
                esc_html__( 'Таксономия категорий товаров недоступна. Убедитесь, что активирован WooCommerce.', 'skd-bundle-block' )
            );
            return;
        }

        wp_dropdown_categories(
            [
                'taxonomy'         => 'product_cat',
                'name'             => self::OPTION_KEY . '[' . self::OPTION_SLOT_PREFIX . $slot . ']',
                'id'               => self::OPTION_SLOT_PREFIX . $slot,
                'hide_empty'       => false,
                'show_option_none' => __( '— Выберите категорию —', 'skd-bundle-block' ),
                'option_none_value' => 0,
                'selected'         => $current_value,
            ]
        );
    }

    /**
     * Get all plugin options.
     */
    private function get_options() {
        if ( empty( $this->options ) ) {
            $this->options = get_option( self::OPTION_KEY, [] );
        }

        return $this->options;
    }

    /**
     * Get the configured discount value.
     */
    public function get_discount() {
        $options = $this->get_options();

        return isset( $options[ self::OPTION_DISCOUNT_KEY ] ) ? floatval( $options[ self::OPTION_DISCOUNT_KEY ] ) : 0;
    }

    /**
     * Retrieve slot category assignments.
     *
     * @return array
     */
    public function get_slot_categories() {
        $categories = [];

        for ( $i = 1; $i <= 3; $i++ ) {
            $categories[ $i ] = $this->get_slot_category( $i );
        }

        return $categories;
    }

    /**
     * Get category ID for given slot.
     *
     * @param int $slot Slot number.
     */
    public function get_slot_category( $slot ) {
        $options = $this->get_options();
        $key     = self::OPTION_SLOT_PREFIX . $slot;

        return isset( $options[ $key ] ) ? absint( $options[ $key ] ) : 0;
    }
}

