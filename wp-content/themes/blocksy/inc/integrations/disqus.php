<?php

// https://wordpress.org/plugins/disqus-comment-system/

add_action('dsq_before_comments', function() {
	$prefix = blocksy_manager()->screen->get_prefix();

	$html_atts = [
		'class' => 'ct-comments',
	];

	if (blocksy_get_theme_mod(
		$prefix . '_comments_containment',
		'separated'
	) === 'contained'
	) {
		$html_atts['class'] .= ' is-width-constrained';
	}

	echo '<div ' . blocksy_attr_to_html($html_atts) . '>';
});
	
add_action('dsq_after_comments', function() {
	echo '</div>';
});