<?php declare(strict_types=1);

use Mioweb\Shop\FormCart;
use Mioweb\Shop\Upsell;

global $post;

global $vePage;

$edit_mode = $vePage->edit_mode;

$upsell = Upsell::createNew($post);
\assert($upsell instanceof Upsell);

$form = MwsForm::getOneById($upsell->getFormId());
\assert($form instanceof MwsForm);

$formCart = MWS()->getFormCart($form);

if (!$edit_mode) {
	if (!$formCart->isFormProcessed()) {
		// Main form is not processed - redirect back to form
		errorRedirect($formCart);
	}

	$gw = MWS()->getSelectedGatewayId();
	if ($gw !== 'mioweb') {
		errorRedirect($formCart);
	}

	$urlCode = $_GET[Upsell::SECURITY_CODE_QUERY_PARAMETER] ?? null;
	if ($urlCode === null) {
		errorRedirect($formCart);
	}

	$securityCode = $formCart->securityCode();
	if ($securityCode === null || $urlCode !== $securityCode) {
		errorRedirect($formCart);
	}

	if ($formCart->isUpsellProcessed($upsell)) {
		$nextUpsell = $formCart->getNextValidUnprocessedUpsell();
		if ($nextUpsell !== null) {
			wp_redirect(add_query_arg([Upsell::SECURITY_CODE_QUERY_PARAMETER => $formCart->securityCode()], $nextUpsell->getUrl()));
			die();
		}

		errorRedirect($formCart);
	}
}

get_header();

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}

get_footer();

function errorRedirect(FormCart $formCart): void
{
	$formUrl = $formCart->getSource() !== null ? $formCart->getSource()->getUrl() : null;
	$errorRedirectUrl = $formUrl ?? get_home_url();
	wp_redirect($errorRedirectUrl);
	die();
}
