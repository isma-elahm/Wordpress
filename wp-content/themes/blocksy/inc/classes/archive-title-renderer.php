<?php

namespace Blocksy;

class ArchiveTitleRenderer {
	private $has_label = false;

	public function __construct($args = []) {
		$args = wp_parse_args($args, [
			'has_label' => false
		]);

		$this->has_label = $args['has_label'];
	}

	public function render_title($title, $original_title, $prefix) {
		if (! $this->has_label) {
			return $original_title;
		}

		return blocksy_html_tag(
			'span',
			[
				'class' => 'ct-title-label'
			],
			rtrim(trim($prefix), ':')
		) . ' ' . $original_title;
	}

	public function get_the_archive_title() {
		$this->start_rendering();

		$title = get_the_archive_title();

		$this->finish_rendering();

		return $title;
	}

	private function start_rendering() {
		add_filter(
			'get_the_archive_title',
			[$this, 'render_title'],

			// Start rendering early, so that someone that alters the title
			// later will get our changes.
			1,
			3
		);
	}

	private function finish_rendering() {
		remove_filter(
			'get_the_archive_title',
			[$this, 'render_title'],

			// Start rendering early, so that someone that alters the title
			// later will get our changes.
			1,
			3
		);
	}
}
