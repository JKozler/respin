<?php

require_once __DIR__ . '/lazy.php';

/** @return Lazy */
function lazy(callable $callback)
{
	return new Lazy($callback);
}
