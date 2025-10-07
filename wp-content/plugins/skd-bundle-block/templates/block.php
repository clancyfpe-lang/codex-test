<?php
/**
 * Block template for the bundle block.
 *
 * @var float $discount
 * @var array $slot_categories
 */

if ( ! function_exists( 'wc_get_products' ) ) {
    echo '<p>' . esc_html__( 'Для отображения блока требуется активный WooCommerce.', 'skd-bundle-block' ) . '</p>';
    return;
}

$products = [];

for ( $slot = 1; $slot <= 3; $slot++ ) {
    $category_id = isset( $slot_categories[ $slot ] ) ? absint( $slot_categories[ $slot ] ) : 0;
    $args        = [
        'limit'   => 1,
        'status'  => 'publish',
        'orderby' => 'date',
        'order'   => 'DESC',
    ];

    if ( $category_id ) {
        $term = get_term( $category_id, 'product_cat' );
        if ( $term && ! is_wp_error( $term ) ) {
            $args['category'] = [ $term->slug ];
        }
    }

    $results = wc_get_products( $args );
    $product = ! empty( $results ) ? current( $results ) : null;
    $products[ $slot ] = $product instanceof \WC_Product ? $product : null;
}

?>
<div class="skd-bundle-block">
    <div class="skd-bundle-block__header">
        <div class="skd-bundle-block__title"><?php esc_html_e( 'Скидка за комплект', 'skd-bundle-block' ); ?></div>
        <?php if ( $discount > 0 ) : ?>
            <div class="skd-bundle-block__discount">
                <?php
                $discount_display = function_exists( 'wc_format_localized_decimal' )
                    ? wc_format_localized_decimal( $discount )
                    : number_format_i18n( $discount );
                echo esc_html( sprintf( __( 'Соберите комплект и получите скидку %s%%', 'skd-bundle-block' ), $discount_display ) );
                ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="skd-bundle-block__slots">
        <?php foreach ( $products as $slot => $product ) : ?>
            <div class="skd-bundle-block__slot skd-bundle-block__slot--<?php echo esc_attr( $slot ); ?>">
                <?php if ( $product ) : ?>
                    <div class="skd-bundle-block__product" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
                        <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="skd-bundle-block__product-image">
                            <?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </a>
                        <div class="skd-bundle-block__product-info">
                            <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="skd-bundle-block__product-title">
                                <?php echo esc_html( $product->get_name() ); ?>
                            </a>
                            <div class="skd-bundle-block__product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="skd-bundle-block__placeholder">
                        <span class="skd-bundle-block__placeholder-label">
                            <?php esc_html_e( 'Выберите товар для этого слота', 'skd-bundle-block' ); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="skd-bundle-block__actions">
        <button type="button" class="button skd-bundle-block__add" data-discount="<?php echo esc_attr( $discount ); ?>">
            <?php esc_html_e( 'Добавить комплект в корзину', 'skd-bundle-block' ); ?>
        </button>
    </div>
</div>
