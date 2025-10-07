( function( wp, settings ) {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment } = wp.element;

    const discount = settings && settings.discount ? settings.discount : 0;
    const slotCategories = settings && settings.slotCategories ? settings.slotCategories : {};

    registerBlockType( 'skd/bundle-block', {
        edit: function() {
            return el(
                'div',
                { className: 'skd-bundle-block skd-bundle-block--editor' },
                el( 'div', { className: 'skd-bundle-block__header' },
                    el( 'div', { className: 'skd-bundle-block__title' }, __( 'Скидка за комплект', 'skd-bundle-block' ) ),
                    discount ? el( 'div', { className: 'skd-bundle-block__discount' },
                        __( 'Скидка: ', 'skd-bundle-block' ) + discount + '%' ) : null
                ),
                el( 'div', { className: 'skd-bundle-block__slots' },
                    [1, 2, 3].map( function( slot ) {
                        const category = slotCategories[ slot ] || 0;
                        return el( 'div', { className: 'skd-bundle-block__slot', key: slot },
                            el( 'div', { className: 'skd-bundle-block__placeholder' },
                                el( Fragment, {},
                                    el( 'span', { className: 'skd-bundle-block__placeholder-label' },
                                        __( 'Слот ', 'skd-bundle-block' ) + slot
                                    ),
                                    category ? el( 'span', { className: 'skd-bundle-block__placeholder-category' },
                                        __( 'Категория ID: ', 'skd-bundle-block' ) + category
                                    ) : el( 'span', { className: 'skd-bundle-block__placeholder-category' },
                                        __( 'Категория не выбрана', 'skd-bundle-block' )
                                    )
                                )
                            )
                        );
                    } )
                ),
                el( 'p', { className: 'skd-bundle-block__note' },
                    __( 'Настройте скидку и категории слотов на странице настроек "Комплект товаров".', 'skd-bundle-block' )
                )
            );
        },
        save: function() {
            return null;
        }
    } );
} )( window.wp, window.skdBundleBlock || {} );
