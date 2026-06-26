<?php

declare(strict_types=1);

use Mioweb\Tus\Tus;

require_once __DIR__ . '/Tus.php';

function tus(): Tus
{
	if (preg_match('/(wp-content\/themes\/)(.)+(\/library\/Tus\/server.php)$/', $_SERVER['SCRIPT_FILENAME'])) {
		$tmpDir = realpath(dirname($_SERVER['SCRIPT_FILENAME'], 6)) . '/tmp/tus';
	} elseif (defined('ABSPATH')) {
		$tmpDir = ABSPATH . 'tmp/tus';
	} else {
		throw new \Exception('Cannot get temp directory');
	}

	return Tus::getInstance($tmpDir);
}

// Clear tus cache and uploaded files
if (defined('ABSPATH') && current_user_can('edit_pages')) {
	$transient = 'tus_cache_cleared';
	$cacheCleared = get_transient($transient);
	if (!$cacheCleared) {
		tus()->getServer()->handleExpiration();
		set_transient($transient, '1', HOUR_IN_SECONDS);
	}

	// Revoke access on logout
	add_action('wp_logout', function () {
		tus()->getAuthorizator()->revokeAccess();
	});
}
