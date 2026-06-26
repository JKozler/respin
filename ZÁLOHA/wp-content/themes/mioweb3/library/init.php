<?php

use Mioweb\Lib\Installer;

define('API_FILE_URL', get_bloginfo('template_url') . '/library/api/');

define('VS_DIR', get_bloginfo('template_url') . '/library/visualeditor/');
define('VS_SERVER_DIR', get_template_directory() . '/library/visualeditor/');
define('VS_DEFAULT_DIR', str_replace(home_url(), '', get_bloginfo('template_url')) . '/library/visualeditor/');
define('MW_IMAGE_LIBRARY', 'https://media.mioweb.com/images/');
define('MW_UI_ICONS', get_template_directory() . '/library/visualeditor/images/ui_icons/');
define('MW_UI_ICONS_URL', get_bloginfo('template_url') . '/library/visualeditor/images/ui_icons/');
define('MW_UI_ICONS_DEF', get_bloginfo('template_url') . '/library/visualeditor/images/ui_icons/symbol-defs.svg');
define('MW_ICONS_URL', get_bloginfo('template_url') . '/library/visualeditor/images/icons/');
/** Slug name for category of event. */
define('MW_EVENT_CAT_SLUG', 'mw_event_category');
define('MW_EVENT_SLUG', 'mw_event');

define('MW_MINIMUM_WP_VERSION', '6.0');
define('MW_MINIMUM_PHP_VERSION_SOFT', '8.0');
define('MW_MINIMUM_PHP_VERSION_HARD', '8.0');
$phpVersion = phpversion();
if (version_compare($phpVersion, MW_MINIMUM_PHP_VERSION_HARD, '<')) {
	die(__('Používáte nepodporovanou verzi PHP (verzi ' . $phpVersion . '). Mioweb vyžaduje verzi ' . MW_MINIMUM_PHP_VERSION_HARD . '. Obraťte se na podporu svého hostingu a proveďte aktualizaci na vyšší verzi PHP.', 'cms_ve'));
}

define('MW_DEFAULT_PER_PAGE', 20);
define('MW_HELP_URL', 'https://napoveda.mioweb.cz/');
define('MW_SUPPORT_URL', 'https://podpora.mioweb.cz');
if (!defined('MY_ACCOUNT_URL')) {
	define('MY_ACCOUNT_URL', 'https://mujucet.mioweb.cz');
}

if (!defined('MW_LOG_DIR')) {
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	define('MW_LOG_DIR', trailingslashit(get_home_path()) . 'log/mioweb/');
}

define('CMS_VERSION', '1.0');
if (!defined('LICENSE_SERVER')) {
	define('LICENSE_SERVER', 'https://admin.smartcluster.net/public/');
}

if (!defined('MW_DEBUG')) {
	define('MW_DEBUG', false);
}

$tempDir = get_temp_dir();
if (rtrim($tempDir, '/') === '/tmp' && !defined('WP_TEMP_DIR')) {
	define('WP_TEMP_DIR', ABSPATH . 'tmp');
}

require_once(__DIR__ . '/../vendor/autoload.php');

require_once(__DIR__ . '/Config/init.php');
require_once(__DIR__ . '/Mailing/init.php');
require_once(__DIR__ . '/core/init.php');
require_once(__DIR__ . '/lib/lazy/init.php');
require_once(__DIR__ . '/Database/init.php');

require_once(__DIR__ . '/lib/Installer.php');
Installer::installUpdates(); // must be run before calling core()

core();

add_theme_support('post-thumbnails');

// Must be loaded after calling `core()`
require_once(__DIR__ . '/Tus/init.php');

// ********* LOAD LANGUAGE

load_theme_textdomain('cms', get_template_directory() . '/library/languages');
$locale = get_locale();

$locale_file = get_template_directory() . "/library/languages/$locale.php";
if (is_readable($locale_file)) {
	require_once($locale_file);
}

require_once(__DIR__ . '/lib/MwCode.php');
require_once(__DIR__ . '/lib/MwCodes.php');
require_once(__DIR__ . '/lib/cookie_management/MwCookieManagement.php');
require_once(__DIR__ . '/lib/mwdb.php');
require_once(__DIR__ . '/admin/mwHelp.php');
require_once(__DIR__ . '/lib/update.php');
require_once(__DIR__ . '/lib/license.php');
require_once(__DIR__ . '/lib/logger.php');
require_once(__DIR__ . '/lib/MwAdminComponents.php');
require_once(__DIR__ . '/lib/MwPrice.php');
require_once(__DIR__ . '/lib/MwVariables.php');
require_once(__DIR__ . '/lib/MwFields.php');
require_once(__DIR__ . '/admin/mwSettingGroup.php');
require_once(__DIR__ . '/admin/mwSettingPage.php');
require_once(__DIR__ . '/admin/mwSettingPageService.php');
require_once(__DIR__ . '/admin/mwSettingPageServiceComments.php');
require_once(__DIR__ . '/admin/mwSettingPageServiceBlog.php');
require_once(__DIR__ . '/lib/cookie_management/mwSettingPageServiceScriptBlocker.php');
require_once(__DIR__ . '/admin/mwSettingObject.php');
require_once(__DIR__ . '/admin/mwSettingObjectService.php');
require_once(__DIR__ . '/admin/mwObjectExport.php');
require_once(__DIR__ . '/admin/mwSetting.php');
require_once(__DIR__ . '/lib/functions.php');
require_once(__DIR__ . '/lib/Email.php');
require_once(__DIR__ . '/main_class.php');
require_once(__DIR__ . '/lib/WpPostFetchRequest.php');
require_once(__DIR__ . '/lib/MwObjectCache.php');
require_once(__DIR__ . '/lib/Exceptions/MWDBException.php');
require_once(__DIR__ . '/MwaConnect/init.php');

require_once(__DIR__ . '/admin/objects/mwPost.php');
require_once(__DIR__ . '/admin/objects/mwBlogPost.php');
require_once(__DIR__ . '/admin/objects/mwPage.php');
require_once(__DIR__ . '/admin/objects/mwEvent.php');
require_once(__DIR__ . '/admin/objects/mwUser.php');
require_once(__DIR__ . '/admin/objects/mwTerm.php');
require_once(__DIR__ . '/admin/objects/mwWeditor.php');
require_once(__DIR__ . '/admin/objects/mwComment.php');

//mwSetting();

// register objects
mwEvent::registerEventPostTypes();
mwUser::registerUserObject();
mwPage::registerPageObject();
mwBlogPost::registerBlogObjects();
mwWeditor::registerWeditorObjects();
mwComment::registerComments();

require_once(__DIR__ . '/lib/PluginType.php');
require_once(__DIR__ . '/lib/PluginCompatibilityChecker.php');
require_once(__DIR__ . '/lib/LockFactory.php');
PluginCompatibilityChecker::init();

define('FAPI_API', __DIR__ . '/api/fapi/FAPIClient.php');

require_once(__DIR__ . '/api/MwApiConnect.php');
require_once(__DIR__ . '/api/MwEmailingApi.php');
require_once(__DIR__ . '/api/MwSellingApi.php');

global $cms;
$cms = MW();
//MW($config);

require_once(__DIR__ . '/lib/field_types.php');
require_once(__DIR__ . '/init_set.php');

// GTM, GA, FBC
require_once(__DIR__ . '/api/gtm/gtm_class.php');
require_once(__DIR__ . '/api/google_analytics/ga_class.php');
require_once(__DIR__ . '/api/fbconversions/fbc_class.php');

// reCAPTCHA
require_once(__DIR__ . '/api/recaptcha/recaptcha_class.php');
require_once(__DIR__ . '/api/recaptcha/ReCaptchaValidator.php');
require_once(__DIR__ . '/api/recaptcha/ReCaptchaResponse.php');

// mPOHODA
require_once(__DIR__ . '/api/mpohoda/mpohoda_class.php');
require_once(__DIR__ . '/api/mpohoda/Exceptions/MPohodaInvalidVatRateException.php');
require_once(__DIR__ . '/api/mpohoda/MPohodaIssuer.php');

// ThePay
require_once(__DIR__ . '/api/thepay/Exceptions/ThePayException.php');


// ********* LOAD STYLES AND SCRIPTS

add_action('wp_enqueue_scripts', 'cms_register_scripts');
add_action('admin_enqueue_scripts', 'load_cms_admin_scripts');

// Fix for WP plugin RSS import. @see https://403page.com/solved-how-to-fix-uncaught-error-call-to-undefined-function-set_magic_quotes_runtime-when-importing-rss-xml-files-into-wordpress/
// This hack can be removed while this PR is merged: https://github.com/WordPress/rss-importer/pull/1
// Or while that issue is resolved: https://core.trac.wordpress.org/ticket/52074
if (!function_exists('set_magic_quotes_runtime')) {

	function set_magic_quotes_runtime($new_setting)
	{
		return true;
	}
}

// Fix for PHP 8.0 @see https://php.watch/versions/8.0/disable_functions-redeclare
if (version_compare(phpversion(), '8.0', '>=') && !function_exists('set_time_limit')) {

	function set_time_limit(int $seconds): bool
	{
		return false;
	}
}

function cms_register_scripts()
{
	$js_texts = require_once(__DIR__ . '/admin/js/js_texts.php');

	wp_register_style('cms_admin_styles', get_template_directory_uri() . '/library/admin/css/admin.css', [], MW()->script_version);

	wp_register_style('cms_datepicker_style', get_template_directory_uri() . '/library/includes/datepicker/datepicker.css', [], MW()->script_version);

	wp_register_script('cms_admin_script', get_template_directory_uri() . '/library/admin/js/admin.js', ['jquery', 'media-upload', 'thickbox', 'jquery-ui-sortable'], MW()->script_version);
	wp_localize_script('cms_admin_script', 'MioAdminjs', $js_texts['admin']);
	wp_localize_script('cms_admin_script', 've_used_colors', $_SESSION['ve_used_colors'] ?? []);

	wp_register_script('cms_datepicker_cs', get_template_directory_uri() . '/library/includes/datepicker/jquery.ui.datepicker-cs.js', ['jquery-ui-datepicker'], MW()->script_version);
	wp_localize_script('cms_datepicker_cs', 'datepicker_texts', $js_texts['datepicker']);

	wp_register_script('ve_weditor_admin_script', get_bloginfo('template_url') . '/library/visualeditor/lib/weditor/weditor_admin.js', ['jquery'], MW()->script_version);

	wp_register_script('cms_lightbox_script', get_bloginfo('template_url') . '/library/includes/cms_lightbox/lightbox.js', ['jquery'], MW()->script_version);
	wp_register_style('cms_lightbox_style', get_bloginfo('template_url') . '/library/includes/cms_lightbox/lightbox.css', [], MW()->script_version);

	wp_register_script('mw-croll-script', get_bloginfo('template_url') . '/library/visualeditor/js/perfect-scrollbar.min.js', ['jquery'], 3, true);
	wp_register_style('mw-croll-style', get_bloginfo('template_url') . '/library/visualeditor/css/perfect-scrollbar.css', [], 2);

	wp_register_script('mw-chosen-support', get_bloginfo('template_url') . '/library/includes/chosen/chosen.jquery.min.js', ['jquery'], MW()->script_version, false);
	wp_register_style('mw-chosen-styles', get_bloginfo('template_url') . '/library/includes/chosen/chosen.min.css', [], MW()->script_version);

	wp_register_script('cms_minicolor_script', get_template_directory_uri() . '/library/includes/minicolors/jquery.minicolors.js', ['jquery'], MW()->script_version);
	wp_localize_script('cms_minicolor_script', 've_used_colors', $_SESSION['ve_used_colors'] ?? []);
	wp_register_style('cms_minicolor_css', get_template_directory_uri() . '/library/includes/minicolors/jquery.minicolors.css', [], MW()->script_version);
}

function load_cms_admin_scripts()
{
	cms_register_scripts();

	$current_screen = get_current_screen();

	if ($current_screen->id == 'widgets') {
		wp_enqueue_script('cms_widgets_script', get_template_directory_uri() . '/library/admin/js/widgets.js', ['jquery'], MW()->script_version);
		wp_enqueue_media();
	}

	if (MW()->getLicense()->isHosting() && $current_screen->id == 'options-general') {
		wp_enqueue_script('cms_hide_options', get_template_directory_uri() . '/library/admin/js/hide_options.js', ['jquery'], MW()->script_version);
	}

	if (isset($_GET['page']) || isset($_GET['post']) || ($current_screen->action == 'add' && $current_screen->base == 'post') || $current_screen->id == 'widgets') {
		wp_enqueue_script('cms_minicolor_script');
		wp_enqueue_style('cms_minicolor_css');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-slider');
		//wp_enqueue_script('cms_lightbox_script');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('cms_datepicker_style');
		wp_enqueue_script('cms_datepicker_cs');

		wp_enqueue_script('cms_admin_script');
		wp_enqueue_style('cms_admin_styles');

		//wp_enqueue_script('jquery-ui-sortable');
		//wp_enqueue_script('jquery-ui-droppable');

		// chosen - autocomplete
		wp_enqueue_script('mw-chosen-support');
		wp_enqueue_style('mw-chosen-styles');

		// perfect scrollbar
		wp_enqueue_script('mw-croll-script');
		wp_enqueue_style('mw-croll-style');

		wp_enqueue_script('ve_weditor_admin_script');
	}
}

// Prolongate wp_remote_get timeout
if (defined('MW_HTTP_REMOTE_GET_TIMEOUT') && is_int(MW_HTTP_REMOTE_GET_TIMEOUT) && MW_HTTP_REMOTE_GET_TIMEOUT > 1) {
	add_filter('http_request_timeout', 'mw_prolongate_wp_remote_get_timeout');

	/**
	 * @param int $timeoutSec
	 * @return int
	 */
	function mw_prolongate_wp_remote_get_timeout($timeoutSec)
	{
		return MW_HTTP_REMOTE_GET_TIMEOUT;
	}
}
