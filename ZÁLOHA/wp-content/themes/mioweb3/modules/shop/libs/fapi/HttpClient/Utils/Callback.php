<?php

namespace MwShop\HttpClient\Utils;


/**
 * PHP callable tools.
 *
 * This solution is mostly based on Nette Framework (c) David Grudl (http://davidgrudl.com), new BSD license
 *
 * @author David Grudl
 */
class Callback
{

	/**
	 * Invokes internal PHP function with own error handler.
	 *
	 * @param callable $function
	 * @param array $args
	 * @param callable $onError function($message, $severity)
	 * @return mixed
	 * @throws \Exception
	 */
	public static function invokeSafe($function, array $args, $onError)
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$prev = set_error_handler(function ($severity, $message, $file) use ($onError, &$prev) {
			if ($file === __FILE__ && $onError($message, $severity) !== false) {
				return null;
			}

			if ($prev) {
				return call_user_func_array($prev, func_get_args());
			}

			return false;
		});

		try {
			$res = call_user_func_array($function, $args);
			restore_error_handler();

			return $res;
		} catch (\Exception $e) {
			restore_error_handler();

			throw $e;
		}
	}
}
