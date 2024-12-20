<?php

add_action('blocksy:account:user-flow:before-lostpassword', function() {
	add_filter('secupress.plugins.move_login.deny_bypass', '__return_true');
});

add_action('blocksy:account:user-flow:before-registration', function() {
	add_filter('secupress.plugins.move_login.deny_bypass', '__return_true');
});

add_action('blocksy:account:user-flow:before-login', function() {
	add_filter('secupress.plugins.move_login.deny_bypass', '__return_true');
});
