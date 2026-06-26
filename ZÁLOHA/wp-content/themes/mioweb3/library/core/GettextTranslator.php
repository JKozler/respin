<?php declare(strict_types=1);

namespace Mioweb\Core;

use Nette\Localization\Translator;

class GettextTranslator implements Translator
{

	/** @inheritDoc */
	function translate($message, ...$parameters): string
	{
		// TODO implement more filters for _x(), _n(), etc....
		$domain = $parameters[0] ?? 'default';

		return __($message, $domain);
	}

}
