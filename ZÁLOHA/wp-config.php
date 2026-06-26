<?php//Begin Really Simple Security key
define('RSSSL_KEY', 'ABNjKZegAsyLaTUKTqPkHloHIBjhTF6kRtxJJhajN8EasJmIKFusZvTumyd28YVN');
//END Really Simple Security key

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL

//Begin Really Simple SSL Load balancing fix
if ((isset($_ENV["HTTPS"]) && ("on" == $_ENV["HTTPS"]))
|| (isset($_SERVER["HTTP_X_FORWARDED_SSL"]) && (strpos($_SERVER["HTTP_X_FORWARDED_SSL"], "1") !== false))
|| (isset($_SERVER["HTTP_X_FORWARDED_SSL"]) && (strpos($_SERVER["HTTP_X_FORWARDED_SSL"], "on") !== false))
|| (isset($_SERVER["HTTP_CF_VISITOR"]) && (strpos($_SERVER["HTTP_CF_VISITOR"], "https") !== false))
|| (isset($_SERVER["HTTP_CLOUDFRONT_FORWARDED_PROTO"]) && (strpos($_SERVER["HTTP_CLOUDFRONT_FORWARDED_PROTO"], "https") !== false))
|| (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && (strpos($_SERVER["HTTP_X_FORWARDED_PROTO"], "https") !== false))
|| (isset($_SERVER["HTTP_X_PROTO"]) && (strpos($_SERVER["HTTP_X_PROTO"], "SSL") !== false))
) {
$_SERVER["HTTPS"] = "on";
}
//END Really Simple SSL

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'd230071_iodqul' );

/** MySQL database username */
define( 'DB_USER', 'a230071_iodqul' );

/** MySQL database password */
define( 'DB_PASSWORD', 'uDQuL8DX' );

/** MySQL hostname */
define( 'DB_HOST', 'md66.wedos.net' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'bmV6td03OfExatsrWeRBIHlJaq6qJFs8nrwjRRbSRT4z4xtI3e7nCdLRLKt5K9hu');
define('SECURE_AUTH_KEY',  'fG0GWFBG1FPhI6v8CKzgQ0uUjsWEBbJBfrrIGkJOuITabDYNF5w57yQU2vY80vz8');
define('LOGGED_IN_KEY',    'LDetXplRkDuivrKVfUNNbIycxrsserhetRDEGQgLyOfU0QkyvcoI2gf5dpEOXY1R');
define('NONCE_KEY',        'L8dHvooQsv21IXEKi6fLlVsme5pDyj0qwp33prD64B5wGDoRFl6OkmWzjK4eiXma');
define('AUTH_SALT',        'dYxjiccbeX0kNWwlvKywUXgjHFKo6DtlXWDUOpojiOdn2Eq4VZRbEVtGGDUgw7Gz');
define('SECURE_AUTH_SALT', 'whWXEiVheGaAJ0OdLm2ukTwnhXFFaxo12ydVRCb84VLCKY4sXnalcUJxuIIlLsCv');
define('LOGGED_IN_SALT',   'ALMxq5MtDTTCXBdSLvokNjC7KnhT7sJD45xV5ckY3hxOZl6I7oaI2DGjDjzFA1gj');
define('NONCE_SALT',       'xYvcXYsmiVdY5ZlAr3moJOrE5yr2uPW5p6bD3PgsrQeRmHzLTpL7ZRVPdflb7O4I');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');
define('FS_CHMOD_DIR',0755);
define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed externally by Installatron.
 * If you remove this define() to re-enable WordPress's automatic background updating
 * then it's advised to disable auto-updating in Installatron.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'qfyl_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
