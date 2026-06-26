<?php declare(strict_types=1);

namespace Mioweb\Config\Sources;

interface ISource
{

	public function parse(): array;

}
