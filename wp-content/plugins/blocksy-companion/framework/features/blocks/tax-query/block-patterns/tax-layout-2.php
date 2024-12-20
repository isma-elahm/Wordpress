<?php

$pattern = [
	'title'      => __( 'Taxonomies - Layout 2', 'blocksy-companion' ),
	'categories' => ['blocksy'],
	'blockTypes' => ['blocksy/tax-query'],

	'content' => '<!-- wp:blocksy/tax-query {"uniqueId":"d27d7623"} -->
	<div class="wp-block-blocksy-tax-query"><!-- wp:blocksy/tax-template {"layout":{"type":"grid","columnCount":3}} -->
	<!-- wp:columns {"verticalAlignment":"center"} -->
	<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"33.33%"} -->
	<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:33.33%"><!-- wp:blocksy/dynamic-data {"field":"wp:term_image","has_field_link":"yes"} /--></div>
	<!-- /wp:column -->

	<!-- wp:column {"verticalAlignment":"center","width":"66.66%"} -->
	<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:66.66%"><!-- wp:blocksy/dynamic-data {"tagName":"h5","field":"wp:term_title","has_field_link":"yes","style":{"spacing":{"margin":{"bottom":"0px"}}}} /-->

	<!-- wp:blocksy/dynamic-data {"field":"wp:term_count","after":" categories"} /--></div>
	<!-- /wp:column --></div>
	<!-- /wp:columns -->
	<!-- /wp:blocksy/tax-template --></div>
	<!-- /wp:blocksy/tax-query -->'
];