<?php

namespace MwShop\HttpClient;


interface IHttpClient
{

	/**
	 * @param HttpRequest $httpRequest
	 * @return HttpResponse
	 */
	function sendHttpRequest(HttpRequest $httpRequest);
}
