<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'wordpress' );

/** Database hostname */
define( 'DB_HOST', 'database' );

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
define( 'AUTH_KEY',         '%=jKSZ+N7j_)s8(]?#LN/~MS2l{jrZ0xV8!FQ/!{,7cj>5y|yp&2c:.`RSKD?i+!' );
define( 'SECURE_AUTH_KEY',  ' S.`JnDw_8Z@H}R{U*Ri,2fj7%[(|C1C>|,bi&g)2SW?!2rx|BakdB7S`d8Nwl}c' );
define( 'LOGGED_IN_KEY',    'nr/cyo6[nLXN CzMk6t%)7~_U.;mz}!0?)Y<raxQE2nGTqj.8}r0}%LcyfjV^k8J' );
define( 'NONCE_KEY',        'SuBcFB41[*YSn_3ecZ`A$9n+9qh<T/WEtfr|?%;LeeC?F+>-*()zyzyWt:l*:{B0' );
define( 'AUTH_SALT',        'VH<=y{c(5h?W:k);+JeHc0>T` )}@u*w3VL$rDt3Ap){FeT81*| klDps hy0*C_' );
define( 'SECURE_AUTH_SALT', 'TnpQ7ulN=<p?i.Kv{62>P]RyQ+=BQ[LSUkH{`5BE3AER+PhTE{XZusUmXICHR:ML' );
define( 'LOGGED_IN_SALT',   'FHLs+c%7T2nSly/--:%)T*nuv{Vtu` :Ln/yc}Lwo<1O1^(F!+=V(Lco{0d:]5}f' );
define( 'NONCE_SALT',       'Q#Qj%,b]*}v0uTvEl`ENfk3GzXSi|hX!ojFXi&y{GiR-[mg[r+l?)m4zO3tW47Y0' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
