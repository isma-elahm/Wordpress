<?php

$pattern = [
	'title'      => __( 'Taxonomies - Layout 1', 'blocksy-companion' ),
	'categories' => ['blocksy'],
	'blockTypes' => ['blocksy/tax-query'],

	'content' => '<!-- wp:blocksy/tax-query {"uniqueId":"d27d7623"} -->
	<div class="wp-block-blocksy-tax-query"><!-- wp:blocksy/tax-template {"layout":{"type":"grid","columnCount":3}} -->
	<!-- wp:blocksy/dynamic-data {"field":"wp:term_image","has_field_link":"yes"} /-->

	<!-- wp:blocksy/dynamic-data {"tagName":"h5","field":"wp:term_title","has_field_link":"yes"} /-->

	<!-- wp:blocksy/dynamic-data {"field":"wp:term_description"} /-->
	<!-- /wp:blocksy/tax-template --></div>
	<!-- /wp:blocksy/tax-query -->'
];