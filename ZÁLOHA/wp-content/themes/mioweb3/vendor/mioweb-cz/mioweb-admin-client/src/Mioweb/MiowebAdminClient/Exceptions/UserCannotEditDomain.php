<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * The exception that is thrown when user cannot edit data within domain, e.g. user1 tries to modify emails of user2.
 */
class UserCannotEditDomain extends InvalidStateException
{

}
