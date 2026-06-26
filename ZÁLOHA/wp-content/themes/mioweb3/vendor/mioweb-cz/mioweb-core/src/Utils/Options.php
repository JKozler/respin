<?php declare(strict_types = 1);

namespace Mioweb\Core\Utils;

interface Options
{
	public function getOption(string $option);

	public function setOption(string $option, $value): void;
}
