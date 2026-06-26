<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Exceptions;

/**
 * Lack of contact information to order new domain. Contact info of the customer must be fulfilled before proceeding.
 * Alternativnately contact for hosting is missing and can not be created automatically.
 */
class InsufficientContactInfoException extends InvalidStateException
{

}
