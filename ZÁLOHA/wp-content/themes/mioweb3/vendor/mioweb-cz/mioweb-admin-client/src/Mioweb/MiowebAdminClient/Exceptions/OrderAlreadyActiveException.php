<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Support Order creation failure - Customer already has active support.
 */
class OrderAlreadyActiveException extends OrderCreationException
{

}
