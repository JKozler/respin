<?php declare(strict_types=1);

namespace Mioweb\HttpClient;

interface IHttpClient
{

	public function sendHttpRequest(HttpRequest $httpRequest): HttpResponse;

}
