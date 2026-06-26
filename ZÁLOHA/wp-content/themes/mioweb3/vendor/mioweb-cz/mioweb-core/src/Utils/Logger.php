<?php declare(strict_types=1);

namespace Mioweb\Core\Utils;

class Logger
{

	/** Temporarily disables error output to the screen (because of lifetime users) */
	public static function triggerSilentError(string $message, int $level): void
	{
		if (!function_exists('ini_get') || !function_exists('ini_set')) {
			return;
		}

		$displayErrors = ini_get('display_errors');
		if ($displayErrors !== '0') {
			ini_set('display_errors', '0');

			// Check if `ini_set` really works. It could be blocked on some webhostings.
			if (ini_get('display_errors') !== '0') {
				return;
			}
		}

		trigger_error($message, $level);

		if ($displayErrors !== '0') {
			ini_set('display_errors', $displayErrors);
		}
	}

}
