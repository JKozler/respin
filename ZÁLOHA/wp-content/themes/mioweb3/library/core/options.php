<?php

final class Options implements \Mioweb\Core\Utils\Options
{

	public function getOption(string $option)
	{
		return get_option($option);
	}

	public function setOption(string $option, $value): void
	{
		update_option($option, $value);
	}
}
