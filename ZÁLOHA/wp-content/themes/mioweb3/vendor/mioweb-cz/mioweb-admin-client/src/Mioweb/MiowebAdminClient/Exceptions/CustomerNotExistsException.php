<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Customer with entered email or customerId does not exists in the database.
 */
class CustomerNotExistsException extends InvalidStateException
{

}
