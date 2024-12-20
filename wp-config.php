<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ismail' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '2003' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'IPO<KIomP8k)O|WD0|.T!7B-K.%c~pKOO-;oV7z@g^GN]tVbT;e w5u~kEUM9rxD' );
define( 'SECURE_AUTH_KEY',  'y&sO*x6u|Da/pNM2=<MA&MwJG_rf` };lh+IUXj!][eB:nIRC2T|P)kia}kpf7io' );
define( 'LOGGED_IN_KEY',    'zW$XN?yP,$2t(J,q`;P;q_)zC^F^Je{aVF~7Iso*&]4w1~AtUHBxF@p_fW9Y!U.#' );
define( 'NONCE_KEY',        'k=OlA`RC.iyqr9qtNm5[5Em]x*AbpAuTUjrN-UMebt6,@VesqM`LszI(}PcZw@l*' );
define( 'AUTH_SALT',        'Q&}6(2E~n8Nl^&m/a=s5yT(7X8]3wy I?:R#zrN@AqQk|<yB&kRj9p^>p88iK74E' );
define( 'SECURE_AUTH_SALT', '+hv,y:hQ~H#x()Gv<U*|,4oZn.Gj4n_b8 v*.Cy[25h_=QAzBx`jsIZS3Cs6g7-,' );
define( 'LOGGED_IN_SALT',   '}:$+ 7!y^TSEkXPE@OP.6US7,$dntzW{d^@P$W|+|n>=eQIALC=^chR|}[-Yzu*y' );
define( 'NONCE_SALT',       '2d:f+5I9~IAk{9GOER<2gZ bY2FjH(^221}~N.fH,5YpKI#s/dy-N<n/_uvkk#4E' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
