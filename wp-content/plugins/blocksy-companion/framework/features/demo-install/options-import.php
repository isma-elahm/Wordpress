<?php

namespace Blocksy;

class DemoInstallOptionsInstaller {
	protected $demo_name = null;

	protected $sideloaded_images = [];
	protected $is_ajax_request = true;

	public function __construct($args = []) {
		$args = wp_parse_args($args, [
			'demo_name' => null,
			'is_ajax_request' => true,
		]);

		if (
			! $args['demo_name']
			&&
			isset($_REQUEST['demo_name'])
			&&
			$_REQUEST['demo_name']
		) {
			$args['demo_name'] = $_REQUEST['demo_name'];
		}

		$this->demo_name = $args['demo_name'];
		$this->is_ajax_request = $args['is_ajax_request'];
	}

	public function import() {
		if (
			! current_user_can('edit_theme_options')
			&&
			$this->is_ajax_request
		) {
			wp_send_json_error([
				'message' => __("Sorry, you don't have permission to install options.", 'blocksy-companion')
			]);
		}

		if (! $this->demo_name) {
			if ($this->is_ajax_request) {
				wp_send_json_error([
					'message' => __("No demo to install", 'blocksy-companion')
				]);
			} else {
				return new \WP_Error(
					'blocksy_demo_install_options_no_demo',
					__("No demo to install", 'blocksy-companion')
				);
			}
		}

		$demo_name = explode(':', $this->demo_name);

		if (! isset($demo_name[1])) {
			$demo_name[1] = '';
		}

		$demo = $demo_name[0];
		$builder = $demo_name[1];

		$demo_to_install = Plugin::instance()->demo->get_currently_installing_demo();

		if (
			empty($demo_to_install)
			||
			! isset($demo_to_install['demo'])
			||
			! isset($demo_to_install['demo']['options'])
		) {
			if ($this->is_ajax_request) {
				wp_send_json_error([
					'message' => __("No demo to install", 'blocksy-companion')
				]);
			} else {
				return new \WP_Error(
					'blocksy_demo_install_options_no_demo',
					__("No demo to install", 'blocksy-companion')
				);
			}
		}

		$options = $demo_to_install['demo']['options'];
		$this->import_options($options, $demo_to_install['demo']);

		if ($this->is_ajax_request) {
			wp_send_json_success();
		}
	}

	public function import_options($options, $demo_content = null) {
		global $wp_customize;

		do_action('customize_save', $wp_customize);

		foreach ($options['mods'] as $key => $val) {
			if ($key === 'sidebars_widgets') continue;
			if ($key === 'custom_css_post_id') continue;
			do_action('customize_save_' . $key, $wp_customize);
			set_theme_mod($key, $val);
		}

		if (
			isset($options['custom_palettes'])
			&&
			is_array($options['custom_palettes'])
			&&
			! empty($options['custom_palettes'])
		) {
			update_option('blocksy_custom_palettes', $options['custom_palettes']);
		}

		do_action('customize_save_after', $wp_customize);

		foreach ($options['options'] as $key => $val) {
			if ($key === 'blocksy_active_extensions') {
				if ($val && is_array($val)) {
					foreach ($val as $single_extension) {
						Plugin::instance()->extensions->activate_extension(
							$single_extension
						);
					}
				}
			} else {
				if (
					strpos($key, 'woocommerce') !== false
					&&
					$key !== 'woocommerce_thumbnail_cropping'
				) {
					add_option($key, $val);
					update_option($key, $val);
				} else {
					update_option($key, $val);
				}
			}
		}

		/*
		$all = get_option('sidebars_widgets');
		$all['sidebar-1'] = [];
		update_option('sidebars_widgets', $all);

		$all = blocksy_get_theme_mod('sidebars_widgets');

		if ($all) {
			$all['data']['sidebar-1'] = [];
			set_theme_mod('sidebars_widgets', $all);
		}
		 */

		if (
			class_exists('\FluentBooking\App\Services\ImportService')
			&&
			isset($options['fluent_booking_data'])
		) {
			\FluentBooking\Database\DBMigrator::run(false);
			\FluentBooking\Database\DBSeeder::run();


			try {
				$import_service = new \FluentBooking\App\Services\ImportService();
				$calendar = $import_service->importHostJson($options['fluent_booking_data']);

				if (isset($calendar['calendar'])) {
					$current_demo = get_option('blocksy_ext_demos_current_demo', []);
					$current_demo['fluent_booking_calendar'] = $calendar['calendar']->id;
					update_option('blocksy_ext_demos_current_demo', $current_demo);
				}
			} catch (\Throwable $e) {
			}
		}

		if (
			class_exists('\FluentForm\App\Hooks\Handlers\ActivationHandler')
			&&
			isset($options['fluent_form_forms'])
		) {
			$fluentFormActivation = new \FluentForm\App\Hooks\Handlers\ActivationHandler();
			$fluentFormActivation->migrate();

			$forms = $options['fluent_form_forms'];

			$insertedForms = [];

			if ($forms && is_array($forms)) {
				foreach ($forms as $formItem) {
					$formFields = json_encode([]);

					if ($fields = \FluentForm\Framework\Support\Arr::get($formItem, 'form', '')) {
						$formFields = json_encode($fields);
					} elseif ($fields = \FluentForm\Framework\Support\Arr::get($formItem, 'form_fields', '')) {
						$formFields = json_encode($fields);
					} else {
					}

					$form = [
						'title'       => \FluentForm\Framework\Support\Arr::get($formItem, 'title'),
						'form_fields' => $formFields,
						'status'      => \FluentForm\Framework\Support\Arr::get($formItem, 'status', 'published'),
						'has_payment' => \FluentForm\Framework\Support\Arr::get($formItem, 'has_payment', 0),
						'type'        => \FluentForm\Framework\Support\Arr::get($formItem, 'type', 'form'),
						'created_by'  => get_current_user_id(),
					];

					if (\FluentForm\Framework\Support\Arr::get($formItem, 'conditions')) {
						$form['conditions'] = \FluentForm\Framework\Support\Arr::get($formItem, 'conditions');
					}

					if (isset($formItem['appearance_settings'])) {
						$form['appearance_settings'] = \FluentForm\Framework\Support\Arr::get($formItem, 'appearance_settings');
					}

					$formId = \FluentForm\App\Models\Form::insertGetId($form);
					$insertedForms[$formId] = [
						'title'    => $form['title'],
						'edit_url' => admin_url('admin.php?page=fluent_forms&route=editor&form_id=' . $formId),
					];

					if (isset($formItem['metas'])) {
						foreach ($formItem['metas'] as $metaData) {
							$metaKey = \FluentForm\Framework\Support\Arr::get($metaData, 'meta_key');
							$metaValue = \FluentForm\Framework\Support\Arr::get($metaData, 'value');
							if ("ffc_form_settings_generated_css" == $metaKey || "ffc_form_settings_meta" == $metaKey) {
								$metaValue = str_replace('ff_conv_app_' . \FluentForm\Framework\Support\Arr::get($formItem, 'id'), 'ff_conv_app_' . $formId, $metaValue);
							}
							$settings = [
								'form_id'  => $formId,
								'meta_key' => $metaKey,
								'value'    => $metaValue,
							];
							\FluentForm\App\Models\FormMeta::insert($settings);
						}
					} else {
						$oldKeys = [
							'formSettings',
							'notifications',
							'mailchimp_feeds',
							'slack',
						];
						foreach ($oldKeys as $key) {
							if (isset($formItem[$key])) {
								\FluentForm\App\Models\FormMeta::persist($formId, $key, json_encode(\FluentForm\Framework\Support\Arr::get($formItem, $key)));
							}
						}
					}
					do_action_deprecated(
						'fluentform_form_imported',
						[
							$formId
						],
						FLUENTFORM_FRAMEWORK_UPGRADE,
						'fluentform/form_imported',
						'Use fluentform/form_imported instead of fluentform_form_imported.'
					);
					do_action('fluentform/form_imported', $formId);
				}
			}
		}

		if (
			function_exists('wc_get_attribute_taxonomies')
			&&
			isset($options['woocommerce_attribute_taxonomies'])
		) {
			$current = wc_get_attribute_taxonomies();

			foreach ($options['woocommerce_attribute_taxonomies'] as $attr) {
				$found = false;

				foreach (array_values($current) as $current_attr) {
					if ($current_attr->attribute_name === $attr['attribute_name']) {
						$found = true;
						break;
					}
				}

				if (! $found) {
					wc_create_attribute([
						'name' => $attr['attribute_label'],
						'slug' => $attr['attribute_name'],
						'type' => $attr['attribute_type'],
						'order_by' => $attr['attribute_orderby'],
						'has_archives' => !! $attr['attribute_public']
					]);
				}
			}
		}

		if (
			function_exists('wp_update_custom_css_post')
			&&
			isset($options['wp_css'])
			&&
			$options['wp_css']
		) {
			wp_update_custom_css_post($options['wp_css']);
		}

		/**
		 * Temporary work around until Elementor comes up with something better
		 */
		if (class_exists('\Elementor\Plugin')) {
			$default_post_id = \Elementor\Plugin::$instance->kits_manager->get_active_id();

			if (
				! empty($default_post_id)
				&&
				isset($options['elementor_active_kit_settings'])
				&&
				! empty($options['elementor_active_kit_settings'])
			) {
				update_post_meta(
					$default_post_id,
					'_elementor_page_settings',
					$options['elementor_active_kit_settings']
				);
			}
		}
	}
}

