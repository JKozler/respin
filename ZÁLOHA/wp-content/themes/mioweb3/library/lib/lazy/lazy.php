<?php

class Lazy
{

	private $callback;

	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}

	public function __toString()
	{
		return call_user_func($this->callback);
	}
}
