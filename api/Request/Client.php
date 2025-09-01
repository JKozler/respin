<?php

/**
 * This file is part of ArtFocus ArtCMS.
 * Copyright © 2021 Ján Forgáč <forgac@artfocus.cz>
 */

namespace Api\Request;

use GuzzleHttp\RequestOptions;

class Client
{

	const PROTOCOL_HTTP = 'http';
	const PROTOCOL_HTTPS = 'https';
	const PROTOCOL_TYPES = [
		self::PROTOCOL_HTTP => self::PROTOCOL_HTTP,
		self::PROTOCOL_HTTPS => self::PROTOCOL_HTTPS,
	];

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const METHOD_TYPES = [
		self::METHOD_GET => self::METHOD_GET,
		self::METHOD_POST => self::METHOD_POST,
		self::METHOD_PUT => self::METHOD_PUT,
		self::METHOD_DELETE => self::METHOD_DELETE,
	];

	const BODY_TEXT = 'text/plain';
	const BODY_JSON = 'application/json';
	const BODY_XML = 'application/xml';
	const BODY_XML_TEXT = 'text/xml';
	const BODY_SIMPLE_FORM = 'application/x-www-form-urlencoded';
	const BODY_COMPLEX_FORM = 'multipart/form-data';
	const BODY_BINARY = 'application/octet-stream';
	const BODY_IMAGE_ANY = 'image/*';
	const BODY_IMAGE_SVG = 'image/svg';
	const BODY_IMAGE_PNG = 'image/png';
	const BODY_IMAGE_JPEG = 'image/jpeg';
	const BODY_IMAGE_GIF = 'image/gif';
	const BODY_TYPES = [
		self::BODY_TEXT => self::BODY_TEXT,
		self::BODY_JSON => self::BODY_JSON,
		self::BODY_XML => self::BODY_XML,
		self::BODY_XML_TEXT => self::BODY_XML_TEXT,
		self::BODY_SIMPLE_FORM => self::BODY_SIMPLE_FORM,
		self::BODY_COMPLEX_FORM => self::BODY_COMPLEX_FORM,
		self::BODY_BINARY => self::BODY_BINARY,
		self::BODY_IMAGE_ANY => self::BODY_IMAGE_ANY,
		self::BODY_IMAGE_SVG => self::BODY_IMAGE_SVG,
		self::BODY_IMAGE_PNG => self::BODY_IMAGE_PNG,
		self::BODY_IMAGE_JPEG => self::BODY_IMAGE_JPEG,
		self::BODY_IMAGE_GIF => self::BODY_IMAGE_GIF,
	];

	private string $protocol;

	private string $domain;

	private string $path;

	private array $queryParameters = [];

	private string $method;

	private int $connectTimeout = 15; // sec

	private int $maxTime = 50; // sec

	private ?bool $decodeContent = null;

	/** @var string|array|null */
	private $body = null;

	private ?string $bodyType = null;

	private bool $ignoreSslErrors = false;

	private array $headers = [];

	private ?string $bearerToken = null;

	private ?string $basicAuth = null;

	public function __construct(
		string $protocol,
		string $domain,
		string $path,
		string $method,
		string $port = null,
	) {
		if (!array_key_exists($protocol, self::PROTOCOL_TYPES)) {
			throw new ClientException('Unsupported protocol: ' . $protocol);
		}
		if (!array_key_exists($method, self::METHOD_TYPES)) {
			throw new ClientException('Unsupported method: ' . $method);
		}
		if (strpos($path, '/') !== 0) {
			$path = '/' . $path;
		}
		$domain = str_replace('/', '', $domain);

		$this->protocol = $protocol;
		$this->domain = $domain . ($port ? ':' . $port : '');
		$this->path = $path;
		$this->method = $method;
	}

	/**
	 * @param bool|null $ignoreSslErrors
	 * @param resource|null $debug file where to store debug info
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendRequest(bool $ignoreSslErrors = null, $debug = null)
	{
		if ($debug) {
			$guzzleClient = new \GuzzleHttp\Client(['debug' => $debug]);
		} else {
			$guzzleClient = new \GuzzleHttp\Client();
		}

		// timeouts
		$options = [
			RequestOptions::CONNECT_TIMEOUT => $this->maxTime,
			RequestOptions::TIMEOUT => $this->maxTime,
		];

		// SSL
		if ($ignoreSslErrors ?? $this->ignoreSslErrors) {
			$options[RequestOptions::VERIFY] = false;
		}

		// headers
		foreach ($this->headers as $key => $value) {
			$options[RequestOptions::HEADERS][$key] = $value;
		}

		// authorization
		$auth = '';
		if ($this->basicAuth) {
			$auth = 'Basic ' . $this->basicAuth;
		}
		if ($this->bearerToken) {
			if ($auth) {
				$auth .= ',Bearer ' . $this->bearerToken;
			} else {
				$auth = 'Bearer ' . $this->bearerToken;
			}
		}
		if ($auth) {
			$options[RequestOptions::HEADERS]['Authorization'] = $auth;
		}

		// options
		if (isset($this->decodeContent)) {
			$options[RequestOptions::DECODE_CONTENT] = $this->decodeContent;
		}

		// content
		switch ($this->bodyType ?? self::BODY_TEXT) {
			case self::BODY_TEXT:
			case self::BODY_XML:
			case self::BODY_XML_TEXT:
				if (isset($this->body)) {
					$options[RequestOptions::BODY] = $this->body;
				}
				break;
			case self::BODY_JSON:
				$options[RequestOptions::JSON] = $this->body;
				break;
			case self::BODY_SIMPLE_FORM:
			case self::BODY_COMPLEX_FORM:
				$options[RequestOptions::FORM_PARAMS] = $this->body;
				break;
			case self::BODY_BINARY:
			case self::BODY_IMAGE_ANY:
			case self::BODY_IMAGE_SVG:
			case self::BODY_IMAGE_PNG:
			case self::BODY_IMAGE_JPEG:
			case self::BODY_IMAGE_GIF:
				throw new ClientException('IMAGES to be implemented.');

		}

		try {
			return $guzzleClient->request(
				$this->method,
				$this->buildUrl(),
				$options
			);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			return $e->getResponse();
		}
	}

	private function buildUrl(): string
	{
		$query = http_build_query($this->queryParameters);
		return $this->protocol . '://' . $this->domain . $this->path . ($query ? '?' . $query : '');
	}

	// ------------ Setters

	/**
	 * @param array $queryParameters
	 * @return $this
	 */
	public function setQueryParameters(array $queryParameters)
	{
		foreach ($queryParameters as $name => $val) {
			if (!is_scalar($val)) {
				throw new ClientException('Query parameter value can be only scalars.');
			}
		}
		$this->queryParameters = $queryParameters;
		return $this;
	}

	public function addQueryParameter(string $name, string $value)
	{
		$this->queryParameters[$name] = $value;
	}

	/**
	 * @param int $connectTimeout
	 * @return $this
	 */
	public function setConnectTimeout(int $connectTimeout)
	{
		if ($connectTimeout < 1) {
			throw new ClientException('ConnectTimeout must be greater than zero.');
		}
		$this->connectTimeout = $connectTimeout;
		return $this;
	}

	/**
	 * @param int $maxTime
	 * @return $this
	 */
	public function setMaxTime($maxTime)
	{
		if ($maxTime < 1) {
			throw new ClientException('MaxTime must be greater than zero.');
		}
		$this->maxTime = $maxTime;
		return $this;
	}

	public function setDecodeContent(?bool $decodeContent):static
	{
		$this->decodeContent = $decodeContent;
		return $this;
	}

	/**
	 * @param mixed $body
	 * @param string $bodyType
	 * @return $this
	 */
	public function setBody($body, string $bodyType)
	{
		if (!array_key_exists($bodyType, self::BODY_TYPES)) {
			throw new ClientException('Unsupported body type: ' . $bodyType);
		}

		switch ($bodyType) {
			case self::BODY_TEXT:
			case self::BODY_XML:
			case self::BODY_XML_TEXT:
				if (is_string($body)) {
					$this->body = $body;
					$this->bodyType = $bodyType;
				} else {
					throw new ClientException('Text body must be string');
				}
				break;
			case self::BODY_SIMPLE_FORM:
			case self::BODY_COMPLEX_FORM:
			case self::BODY_JSON:
				if (is_array($body)) {
					$this->body = $body;
					$this->bodyType = $bodyType;
				} else {
					throw new ClientException("'$bodyType' body must be array");
				}
				break;
			case self::BODY_BINARY:
			case self::BODY_IMAGE_ANY:
			case self::BODY_IMAGE_SVG:
			case self::BODY_IMAGE_PNG:
			case self::BODY_IMAGE_JPEG:
			case self::BODY_IMAGE_GIF:
				if (is_string($body)) {
					$this->body = $body;
					$this->bodyType = $bodyType;
				} else {
					throw new ClientException("'$bodyType' body must be passed to the string (already converted).");
				}
		}

		return $this;
	}

	/**
	 * @param bool $ignoreSslErrors
	 * @return $this
	 */
	public function setIgnoreSslErrors(bool $ignoreSslErrors)
	{
		$this->ignoreSslErrors = $ignoreSslErrors;
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $val
	 * @return $this
	 */
	public function addHeader(string $key, string $val)
	{
		$this->headers[$key] = $val;
		return $this;
	}

	public function addApiKey(string $headerName, string $apiKeyValue)
	{
		$this->addHeader($headerName, $apiKeyValue);
		return $this;
	}

	public function setBearerToken(string $token): static
	{
		$this->bearerToken = $token;
		return $this;
	}

	public function setBasicAuth(string $username, string $password): static
	{
		$this->basicAuth = base64_encode("$username:$password");
		return $this;
	}

}
