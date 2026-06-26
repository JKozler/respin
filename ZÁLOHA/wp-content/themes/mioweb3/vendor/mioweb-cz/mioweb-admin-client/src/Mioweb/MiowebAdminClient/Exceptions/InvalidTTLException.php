<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Invalid TTL - There is already another DNS row with the same name and type but different TTL.
 */
class InvalidTTLException extends OrderCreationException
{

}
