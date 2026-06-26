<?php declare(strict_types=1);

use Mioweb\Mailing\Mailer;
use Mioweb\Mailing\MiowebMailerFactory;
use Mioweb\Mailing\TransactionalMailRequirementStatus;
use Mioweb\Mailing\WordpressMailer;
use Nette\Mail\FallbackMailer;

require_once __DIR__ . '/MiowebMailerFactory.php';
require_once __DIR__ . '/TransactionalMailer.php';
require_once __DIR__ . '/WordpressMailer.php';
require_once __DIR__ . '/EmailStatus.php';
require_once __DIR__ . '/Mailer.php';

$editMode = current_user_can('edit_pages');
if ($editMode && isset($_GET['checkTransactionalEmailStatus']) && (bool) $_GET['checkTransactionalEmailStatus']) {
	delete_transient(TransactionalMailRequirementStatus::TRANSIENT);
}

function miowebMailer(): Mailer
{
	return MiowebMailerFactory::getMailer();
}

function transactionalMailer(): FallbackMailer
{
	return MiowebMailerFactory::getTransactional();
}

function wpMailer(): WordpressMailer
{
	return MiowebMailerFactory::getWordpress();
}
