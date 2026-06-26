<?php declare(strict_types=1);

namespace Mioweb\Tus;

class Request extends \TusPhp\Request
{

	public function key(): string
	{
		return $this->request->query->get('key') ?? '';
	}

}
