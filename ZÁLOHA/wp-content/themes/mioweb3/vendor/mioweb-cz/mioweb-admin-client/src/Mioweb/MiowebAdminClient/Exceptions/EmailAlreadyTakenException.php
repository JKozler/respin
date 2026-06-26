<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * The exception that is thrown when an email with the same address already exists.
 */
class EmailAlreadyTakenException extends InvalidStateException
{

}
