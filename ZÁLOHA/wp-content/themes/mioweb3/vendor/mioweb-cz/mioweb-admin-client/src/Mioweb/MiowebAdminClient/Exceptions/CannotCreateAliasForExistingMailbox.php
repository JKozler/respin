<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * The exception that is thrown when a user tries to create an alias for e-mail address which is the existing mailbox
 */
class CannotCreateAliasForExistingMailbox extends InvalidStateException
{

}
