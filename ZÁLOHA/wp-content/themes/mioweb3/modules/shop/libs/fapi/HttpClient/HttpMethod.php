<?php

namespace MwShop\HttpClient;


class HttpMethod
{
	const HEAD = 'HEAD';
	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const DELETE = 'DELETE';

	/** @return string[] */
	public static function getAll()
	{
		return [
			static::HEAD,
			static::GET,
			static::POST,
			static::PUT,
			static::DELETE,
		];
	}


	/**
	 * @param string $value
	 * @return bool
	 */
	public static function isValid($value)
	{
		return in_array($value, static::getAll(), true);
	}
}
