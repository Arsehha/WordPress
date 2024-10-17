<?php
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
define( 'DB_NAME', 'WordPress' );

/** Database username */
define( 'DB_USER', 'Admin' );

/** Database password */
define( 'DB_PASSWORD', 'giveme5' );

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
define( 'AUTH_KEY',         ';kU-f*Vl6Cc<];X,lJn%QypU$TMk}K4=e&{+.2t,z-w&,U)qP#Sq<z)J;3XDROie' );
define( 'SECURE_AUTH_KEY',  '7!_Il=MWGp0Zn %), Fu2b/O%Ca`88.Lv%;N*vFoibfJ21C$]BdP(0$YY5ypaT4|' );
define( 'LOGGED_IN_KEY',    '>;BQ+&[oa<V3wl=( 0/S0R{%n#zjZnLHYz,.~~l(^yVL%j27oxcib5LjQ7<$S[wr' );
define( 'NONCE_KEY',        'HNvjvXOG@7dI/InzN$Vp09[+{BN56ed(hfvW-t22vKuv/MK3+K_tTGr9cxYWZUq$' );
define( 'AUTH_SALT',        '|Mf|x0M&A/90V9wFvHQ5p3qL^c$@`#k}eDk6oxO[g%@+nbcM_z|r>GuQ@Q]J~M*]' );
define( 'SECURE_AUTH_SALT', 'X,POW4mVUY`WfnR0IYy^cF7}JlgLI;*.BG+@JvhODCijyv,6U-IfItf5qk4DbN?4' );
define( 'LOGGED_IN_SALT',   'MAe%xdfx^7]-vud7/G-C6ykiYb%^ix[gnts]<aG_g!mx[~Z&j0smBqw7-O$lU`hk' );
define( 'NONCE_SALT',       '8]HkeYnGn;Q?=?|d]hM ?vsxkj0Rmk]Ml$g$pyLCZfJa.6?o7sE$0&?EP-Llg:V=' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
