<?php

namespace MwShop\HttpClient;


class HttpResponse
{

	/** @var int */
	private $statusCode;

	/** @var string[][] */
	private $headers;

	/** @var string */
	private $body;

	/**
	 * @param int $statusCode
	 * @param string[][] $headers
	 * @param string $body
	 */
	public function __construct($statusCode, array $headers, $body)
	{
		if (!HttpStatusCode::isValid($statusCode)) {
			throw new InvalidArgumentException('Parameter statusCode must be an HTTP status code.');
		}

		static::validateHeaders($headers);

		if (!is_string($body)) {
			throw new InvalidArgumentException('Parameter body must be a string.');
		}

		$this->statusCode = $statusCode;
		$this->headers = $headers;
		$this->body = $body;
	}


	/**
	 * @param array $headers
	 * @return void
	 */
	private static function validateHeaders(array $headers)
	{
		foreach ($headers as $values) {
			if (!is_array($values)) {
				throw new InvalidArgumentException('Header values must be an array.');
			}

			foreach ($values as $value) {
				if (!is_string($value)) {
					throw new InvalidArgumentException('Header value must be a string.');
				}
			}
		}
	}


	/** @return int */
	public function getStatusCode()
	{
		return $this->statusCode;
	}


	/** @return string[][] */
	public function getHeaders()
	{
		return $this->headers;
	}


	/** @return string */
	public function getBody()
	{
		return $this->body;
	}
}
