<?php

$pattern = [
	'title'      => __( 'Posts - Layout 5', 'blocksy-companion' ),
	'categories' => ['blocksy'],
	'blockTypes' => ['blocksy/query'],

	'content' => '<!-- wp:blocksy/query {"uniqueId":"22f2864d","limit":6} -->
	<div class="wp-block-blocksy-query"><!-- wp:blocksy/post-template {"layout":{"type":"grid","columnCount":3}} -->
	<!-- wp:cover {"useFeaturedImage":true,"dimRatio":50,"isUserOverlayColor":true,"minHeight":400,"customGradient":"linear-gradient(0deg,rgb(0,0,0) 13%,rgba(0,0,0,0) 100%)","contentPosition":"bottom center","style":{"border":{"radius":"5px"},"spacing":{"padding":{"right":"var:preset|spacing|50","left":"var:preset|spacing|50","top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-cover has-custom-content-position is-position-bottom-center" style="border-radius:5px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:400px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim has-background-gradient" style="background:linear-gradient(0deg,rgb(0,0,0) 13%,rgba(0,0,0,0) 100%)"></span><div class="wp-block-cover__inner-container"><!-- wp:blocksy/dynamic-data {"tagName":"h2","align":"center","has_field_link":"yes","fontSize":"medium","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"},":hover":{"color":{"text":"var:preset|color|palette-color-1"}}}}}} /-->

	<!-- wp:blocksy/dynamic-data {"field":"wp:date","align":"center","style":{"typography":{"fontSize":"12px","textTransform":"uppercase"}}} /--></div></div>
	<!-- /wp:cover -->
	<!-- /wp:blocksy/post-template --></div>
	<!-- /wp:blocksy/query -->'
];