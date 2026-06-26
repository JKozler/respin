<?php declare(strict_types=1);

namespace Mioweb\HttpClient\Rest\Exceptions;

use Mioweb\HttpClient\Rest\Exceptions;

/**
 * The exception that is thrown when an HTTP response with unexpected HTTP status code is received.
 */
class InvalidStatusCodeException extends Exceptions\RestClientException
{

}
