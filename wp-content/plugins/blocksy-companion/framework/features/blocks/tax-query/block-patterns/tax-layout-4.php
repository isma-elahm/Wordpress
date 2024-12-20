<?php

$pattern = [
	'title'      => __( 'Taxonomies - Layout 4', 'blocksy-companion' ),
	'categories' => ['blocksy'],
	'blockTypes' => ['blocksy/tax-query'],

	'content' => '<!-- wp:blocksy/tax-query {"uniqueId":"d27d7623"} -->
	<div class="wp-block-blocksy-tax-query"><!-- wp:blocksy/tax-template {"layout":{"type":"grid","columnCount":3}} -->
	<!-- wp:blocksy/dynamic-data {"field":"wp:term_image","aspectRatio":"1","has_field_link":"yes","style":{"border":{"radius":"100%"}}} /-->

	<!-- wp:blocksy/dynamic-data {"tagName":"h5","field":"wp:term_title","align":"center","has_field_link":"yes","style":{"spacing":{"margin":{"bottom":"0px"}}}} /-->

	<!-- wp:blocksy/dynamic-data {"field":"wp:term_count","after":" categories","align":"center"} /-->
	<!-- /wp:blocksy/tax-template --></div>
	<!-- /wp:blocksy/tax-query -->'
];