<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Pending operation could not be created. Other unprocessed operation is already present.
 */
class PendingAlreadyPresent extends InvalidStateException
{

}
