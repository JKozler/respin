<?php

spl_autoload_register(function ($className) {
	static $classMap = [
		'MwShop\\FapiClient\\ArgumentOutOfRangeException' => 'FapiClient/exceptions.php',
		'MwShop\\FapiClient\\DeprecatedException' => 'FapiClient/exceptions.php',
		'MwShop\\FapiClient\\FapiClient' => 'FapiClient/FapiClient.php',
		'MwShop\\FapiClient\\FapiClientFactory' => 'FapiClient/FapiClientFactory.php',
		'MwShop\\FapiClient\\IFapiClient' => 'FapiClient/IFapiClient.php',
		'MwShop\\FapiClient\\IFapiClientFactory' => 'FapiClient/IFapiClientFactory.php',
		'MwShop\\FapiClient\\InvalidArgumentException' => 'FapiClient/exceptions.php',
		'MwShop\\FapiClient\\InvalidStateException' => 'FapiClient/exceptions.php',
		'MwShop\\FapiClient\\NotImplementedException' => 'FapiClient/exceptions.php',
		'MwShop\\FapiClient\\NotSupportedException' => 'FapiClient/exceptions.php',
		'MwShop\\FapiClient\\OutOfRangeException' => 'FapiClient/exceptions.php',
		'MwShop\\FapiClient\\Rest\\FapiRestClient' => 'FapiClient/Rest/FapiRestClient.php',
		'MwShop\\FapiClient\\Rest\\FapiRestClientOptions' => 'FapiClient/Rest/FapiRestClientOptions.php',
		'MwShop\\FapiClient\\Rest\\InvalidResponseBodyException' => 'FapiClient/Rest/exceptions.php',
		'MwShop\\FapiClient\\Rest\\InvalidStatusCodeException' => 'FapiClient/Rest/exceptions.php',
		'MwShop\\FapiClient\\Rest\\RestClientException' => 'FapiClient/Rest/exceptions.php',
		'MwShop\\FapiClient\\StaticClassException' => 'FapiClient/exceptions.php',
		'MwShop\\FapiClient\\UnexpectedValueException' => 'FapiClient/exceptions.php',

		'MwShop\\HttpClient\\ArgumentOutOfRangeException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\CurlHttpClient' => 'HttpClient/CurlHttpClient.php',
		'MwShop\\HttpClient\\DeprecatedException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\HttpClientException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\HttpMethod' => 'HttpClient/HttpMethod.php',
		'MwShop\\HttpClient\\HttpRequest' => 'HttpClient/HttpRequest.php',
		'MwShop\\HttpClient\\HttpResponse' => 'HttpClient/HttpResponse.php',
		'MwShop\\HttpClient\\HttpStatusCode' => 'HttpClient/HttpStatusCode.php',
		'MwShop\\HttpClient\\IHttpClient' => 'HttpClient/IHttpClient.php',
		'MwShop\\HttpClient\\InvalidArgumentException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\InvalidStateException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\LoggingHttpClient' => 'HttpClient/LoggingHttpClient.php',
		'MwShop\\HttpClient\\NotImplementedException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\NotSupportedException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\OutOfRangeException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\Rest\\InvalidResponseBodyException' => 'HttpClient/Rest/exceptions.php',
		'MwShop\\HttpClient\\Rest\\InvalidStatusCodeException' => 'HttpClient/Rest/exceptions.php',
		'MwShop\\HttpClient\\Rest\\RestClient' => 'HttpClient/Rest/RestClient.php',
		'MwShop\\HttpClient\\Rest\\RestClientException' => 'HttpClient/Rest/exceptions.php',
		'MwShop\\HttpClient\\StaticClassException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\TimeLimitExceededException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\UnexpectedValueException' => 'HttpClient/exceptions.php',
		'MwShop\\HttpClient\\Utils\\Callback' => 'HttpClient/Utils/Callback.php',
		'MwShop\\HttpClient\\Utils\\Json' => 'HttpClient/Utils/Json.php',
		'MwShop\\HttpClient\\Utils\\JsonException' => 'HttpClient/Utils/Json.php',
	];

	if (isset($classMap[$className])) {
		require __DIR__ . '/' . $classMap[$className];
	}
});
