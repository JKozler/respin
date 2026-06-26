<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Pending operation could not be created. Source hosting is in deleted status.
 */
class PendingCannotCreateForDeleted extends InvalidStateException
{

}
