<?php

add_action('wpcvt_before_add_to_cart', function() {
    ob_start();
});

add_action('wpcvt_after_add_to_cart', function() {
    $content = str_replace(
        '<div class="wpcvt-add-to-cart',
        '<div class="wpcvt-add-to-cart ct-product-add-to-cart',
        ob_get_clean()
    );

    echo $content;
});