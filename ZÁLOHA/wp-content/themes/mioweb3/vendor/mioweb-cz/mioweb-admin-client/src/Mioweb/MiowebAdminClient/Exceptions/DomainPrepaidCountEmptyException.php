<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Raised when change of domain request received but domain prepaid count is empty and change of domain can not proceed.
 */
class DomainPrepaidCountEmptyException extends InvalidStateException
{

}
