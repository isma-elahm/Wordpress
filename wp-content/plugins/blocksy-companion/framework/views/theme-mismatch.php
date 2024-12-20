<?php

$current_screen = get_current_screen();

if ($current_screen && $current_screen->id === 'update-core') {
	return;
}

if (! current_user_can('activate_plugins')) {
	return;
}

if (get_option('dismissed-blocksy_theme_version_mismatch_notice', false)) {
	return;
}

$updates_url = self_admin_url('update-core.php');

$product_name = 'Blocksy theme';
$slug = 'blocksy';

if ($is_theme_version_ok && ! $is_companion_version_ok) {
	$product_name = 'Blocksy Companion plugin';
	$data = get_plugin_data(BLOCKSY__FILE__);

	$slug = plugin_basename(BLOCKSY__FILE__);

	if (blc_can_use_premium_code()) {
		$product_name = 'Blocksy Companion Pro plugin';
	}
}

$messages = [
	'title' => sprintf(
		__('Action required - please update %s to the latest version!', 'blocksy-companion'),
		$product_name
	),

	'description' => sprintf(
		__('We detected that you are using an outdated version of %s.', 'blocksy-companion'),
		$product_name
	),

	'action' => sprintf(
		__('In order to take full advantage of all features the core has to offer - please install and activate the latest version of %s.', 'blocksy-companion'),
		$product_name
	)
];

?>

<div class="notice notice-blocksy-theme-version-mismatch" data-slug="<?php echo $slug ?>" data-product-name="<?php echo $product_name ?>">
	<div class="ct-theme-required">
		<h2>
			<span>
				<svg viewBox="0 0 24 24">
					<path d="M12,23.6c-1.4,0-2.6-1-2.8-2.3L8.9,20h6.2l-0.3,1.3C14.6,22.6,13.4,23.6,12,23.6z M24,17.8H0l3.1-2c0.5-0.3,0.9-0.7,1.1-1.3c0.5-1,0.5-2.2,0.5-3.2V7.6c0-4.1,3.2-7.3,7.3-7.3s7.3,3.2,7.3,7.3v3.6c0,1.1,0.1,2.3,0.5,3.2c0.3,0.5,0.6,1,1.1,1.3L24,17.8zM6.1,15.6h11.8c0,0-0.1-0.1-0.1-0.2c-0.7-1.3-0.7-2.9-0.7-4.2V7.6c0-2.8-2.2-5.1-5.1-5.1c-2.8,0-5.1,2.2-5.1,5.1v3.6c0,1.3-0.1,2.9-0.7,4.2C6.1,15.5,6.1,15.6,6.1,15.6z" />
				</svg>
			</span>

			<?php echo $messages['title']; ?>
		</h2>

		<p>
			<?php echo $messages['description']; ?>
		</p>

		<p>
			<?php echo $messages['action']; ?>
		</p>
	</div>

</div>
