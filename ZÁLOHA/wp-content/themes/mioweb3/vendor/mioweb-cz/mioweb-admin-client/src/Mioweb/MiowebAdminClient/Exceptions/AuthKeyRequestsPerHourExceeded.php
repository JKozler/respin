<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Maximum number of reset-password requests per hour exceeded.
 */
class AuthKeyRequestsPerHourExceeded extends InvalidStateException
{

}
