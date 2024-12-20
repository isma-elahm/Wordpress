<?php

namespace Blocksy;

class ConditionsManagerAPI {
	public function __construct() {
		add_action('wp_ajax_blocksy_conditions_get_all_entities', function () {
			$capability = blc_get_capabilities()->get_wp_capability_by('conditions');

			if (! current_user_can($capability)) {
				wp_send_json_error();
			}

			$maybe_input = json_decode(file_get_contents('php://input'), true);

			if (
				! $maybe_input
				||
				! isset($maybe_input['entity'])
			) {
				wp_send_json_error();
			}

			$result = false;

			if ($maybe_input['entity'] === 'posts') {
				$result = $this->get_all_posts($maybe_input);
			}

			if ($maybe_input['entity'] === 'users') {
				$result = $this->get_all_users($maybe_input);
			}

			if ($maybe_input['entity'] === 'taxonomies') {
				$result = $this->get_all_taxonomies($maybe_input);
			}

			if (! $result) {
				wp_send_json_error();
			}

			wp_send_json_success($result);
		});

		add_action('wp_ajax_blc_retrieve_conditions_data', function () {
			$capability = blc_get_capabilities()->get_wp_capability_by('conditions');

			if (! current_user_can($capability)) {
				wp_send_json_error();
			}

			$filter = 'all';

			$allowed_filters = [
				'archive',
				'singular',
				'product_tabs',
				'product_waitlist',
				'maintenance-mode',
				'content_block_hook'
			];

			if (
				$_REQUEST['filter']
				&&
				in_array($_REQUEST['filter'], $allowed_filters)
			) {
				$filter = $_REQUEST['filter'];
			}

			$languages = [];

			if (function_exists('blocksy_get_current_language')) {
				$languages = blocksy_get_all_i18n_languages();
			}

			$conditions_manager = new ConditionsManager();

			wp_send_json_success([
				'languages' => $languages,
				'conditions' => $conditions_manager->get_all_rules([
					'filter' => $filter
				]),
			]);
		});
	}

	private function get_all_taxonomies($maybe_input) {
		$cpts = blocksy_manager()->post_types->get_supported_post_types();

		$cpts[] = 'post';
		$cpts[] = 'page';
		// $cpts[] = 'product';

		$taxonomies = [];

		if (
			isset($maybe_input['post_type'])
			&&
			$maybe_input['post_type'] === 'product'
		) {
			$cpts = ['product'];
		}

		foreach ($cpts as $cpt) {
			$taxonomies = array_merge($taxonomies, array_values(array_diff(
				get_object_taxonomies($cpt),
				['post_format']
			)));
		}

		$terms = [];
		$terms_query_args = [
			'number' => 10,
			'hide_empty' => false,
		];

		if (
			isset($maybe_input['search_query'])
			&&
			! empty($maybe_input['search_query'])
		) {
			$terms_query_args['search'] = $maybe_input['search_query'];
		}

		foreach ($taxonomies as $taxonomy) {
			$taxonomy_object = get_taxonomy($taxonomy);

			if (! $taxonomy_object->public) {
				continue;
			}

			$local_terms = array_map(function ($tax) {
				return [
					'id' => $tax->term_id,
					'label' => $tax->name,
					'group' => get_taxonomy($tax->taxonomy)->label,
					'post_types' => get_taxonomy($tax->taxonomy)->object_type
				];
			}, blc_get_terms(
				array_merge(
					[
						'taxonomy' => $taxonomy,
					],
					$terms_query_args
				),
				[
					'all_languages' => true
				]
			));

			if (empty($local_terms)) {
				continue;
			}

			$terms = array_merge($terms, $local_terms);
		}

		if (isset($maybe_input['alsoInclude'])) {
			$maybe_term = get_term($maybe_input['alsoInclude']);

			if ($maybe_term) {
				$terms[] = [
					'id' => $maybe_term->term_id,
					'label' => $maybe_term->name,
					'group' => get_taxonomy($maybe_term->taxonomy)->label,
					'post_types' => get_taxonomy($maybe_term->taxonomy)->object_type
				];
			}
		}

		return [
			'entities' => $this->with_uniq_ids($terms)
		];
	}

	private function get_all_users($maybe_input) {
		$users = [];

		$user_query_args = [
			'number' => 10,
			'fields' => ['ID', 'user_nicename'],
		];

		if (
			isset($maybe_input['search_query'])
			&&
			! empty($maybe_input['search_query'])
		) {
			$user_query_args['search'] = '*' . $maybe_input['search_query'] . '*';
		}

		$user_query = new \WP_User_Query($user_query_args);

		foreach ($user_query->get_results() as $user) {
			$users[] = [
				'id' => $user->ID,
				'label' => $user->user_nicename
			];
		}

		if (isset($maybe_input['alsoInclude'])) {
			$maybe_user = get_user_by('id', $maybe_input['alsoInclude']);

			if ($maybe_user) {
				$users[] = [
					'id' => $maybe_user->ID,
					'label' => $maybe_user->user_nicename
				];
			}
		}

		return [
			'entities' => $this->with_uniq_ids($users)
		];
	}

	private function get_all_posts($maybe_input) {
		if (! isset($maybe_input['post_type'])) {
			return false;
		}

		$query_args = [
			'posts_per_page' => 10,
			'post_type' => $maybe_input['post_type'],
			'suppress_filters' => true,
			'lang' => '',
		];

		if (
			isset($maybe_input['search_query'])
			&&
			! empty($maybe_input['search_query'])
		) {
			if (intval($maybe_input['search_query'])) {
				$query_args['p'] = intval($maybe_input['search_query']);
			} else {
				$query_args['s'] = $maybe_input['search_query'];
			}
		}

		$initial_query_args_post_type = $query_args['post_type'];

		if (strpos($initial_query_args_post_type, 'ct_cpt') !== false) {
			$query_args['post_type'] = blocksy_manager()->post_types->get_all([
				'exclude_built_in' => true,
				'exclude_woo' => true
			]);
		}

		if (strpos($initial_query_args_post_type, 'ct_all_posts') !== false) {
			$query_args['post_type'] = blocksy_manager()->post_types->get_all();
		}

		if (! is_array($query_args['post_type'])) {
			$query_args['post_type'] = [$query_args['post_type']];
		}

		$query = new \WP_Query($query_args);

		$posts_result = $query->posts;

		if (isset($maybe_input['alsoInclude'])) {
			$maybe_post = get_post($maybe_input['alsoInclude'], 'display');

			if (
				$maybe_post
				&&
				in_array($maybe_post->post_type, $query_args['post_type'])
			) {
				$posts_result[] = $maybe_post;
			}
		}

		$posts_result = array_map(function ($post) {
			return [
				'id' => $post->ID,
				'label' => $post->post_title,
				'post_type' => $post->post_type
			];
		}, $posts_result);

		return [
			'entities' => $this->with_uniq_ids($posts_result)
		];
	}

	private function with_uniq_ids($items = []) {
		$uniq_ids = [];

		return array_filter($items, function ($item) use (&$uniq_ids) {
			if (in_array($item['id'], $uniq_ids)) {
				return false;
			}

			$uniq_ids[] = $item['id'];

			return true;
		});
	}
}

