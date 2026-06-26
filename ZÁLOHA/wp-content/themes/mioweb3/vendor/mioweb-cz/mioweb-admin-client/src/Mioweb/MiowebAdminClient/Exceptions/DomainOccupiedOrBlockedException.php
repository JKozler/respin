<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Destination domain selected in change domain process can not be used. It is either already registered or an active hosting
 * for domain is already present.
 */
class DomainOccupiedOrBlockedException extends InvalidStateException
{

}
