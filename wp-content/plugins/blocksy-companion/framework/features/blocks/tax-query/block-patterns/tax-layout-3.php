<?php

$pattern = [
	'title'      => __( 'Taxonomies - Layout 3', 'blocksy-companion' ),
	'categories' => ['blocksy'],
	'blockTypes' => ['blocksy/tax-query'],

	'content' => '<!-- wp:blocksy/tax-query {"uniqueId":"d27d7623"} -->
	<div class="wp-block-blocksy-tax-query"><!-- wp:blocksy/tax-template -->
	<!-- wp:columns {"verticalAlignment":"center"} -->
	<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"25%"} -->
	<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:25%"><!-- wp:blocksy/dynamic-data {"field":"wp:term_image","has_field_link":"yes"} /--></div>
	<!-- /wp:column -->

	<!-- wp:column {"verticalAlignment":"center","width":"75%"} -->
	<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:75%"><!-- wp:blocksy/dynamic-data {"tagName":"h2","field":"wp:term_title","has_field_link":"yes","fontSize":"medium","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} /-->

	<!-- wp:blocksy/dynamic-data {"field":"wp:term_description"} /--></div>
	<!-- /wp:column --></div>
	<!-- /wp:columns -->
	<!-- /wp:blocksy/tax-template --></div>
	<!-- /wp:blocksy/tax-query -->'
];