<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Migration failure - another migration is already in the queue
 */
class MigrationAlreadyRunningException extends InvalidStateException
{

}
