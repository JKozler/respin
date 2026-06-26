<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Hosting is not in CREATED state which is necessary to process change of domain name.
 */
class HostingStatusNotCreatedException extends InvalidStateException
{

}
