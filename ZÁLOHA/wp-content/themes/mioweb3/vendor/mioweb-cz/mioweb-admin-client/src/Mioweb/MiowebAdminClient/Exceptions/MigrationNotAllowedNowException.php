<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Migration failure - hosting not in state for migration. Possibly migration is running already?
 */
class MigrationNotAllowedNowException extends InvalidStateException
{

}
