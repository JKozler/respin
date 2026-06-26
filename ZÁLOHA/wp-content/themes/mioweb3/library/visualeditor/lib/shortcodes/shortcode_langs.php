<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('_WP_Editors')) {
	require(ABSPATH . WPINC . '/class-wp-editor.php');
}

function mw_shortcodes_translation()
{
	$strings = [
		'title' => __('Přidat Mioweb shortcode', 'cms_ve'),
	];
	$locale = _WP_Editors::$mce_locale;
	$translated = 'tinyMCE.addI18n("' . $locale . '.mwshortcodes", ' . json_encode($strings) . ");\n";

	return $translated;
}

$strings = mw_shortcodes_translation();
