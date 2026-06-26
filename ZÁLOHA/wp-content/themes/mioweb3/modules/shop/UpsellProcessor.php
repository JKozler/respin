<?php declare(strict_types=1);

namespace Mioweb\Shop;

use Mioweb\Shop\Exceptions\UpsellProcessException;
use MioShop;
use MwsAjax;
use MwsForm;

final class UpsellProcessor
{

	private FormProcessor $formProcessor;

	public function __construct(FormProcessor $formProcessor)
	{
		$this->formProcessor = $formProcessor;
	}

	public function processUpsell(): void
	{
//		$nonce = $_POST['nonce'] ?? '';
//		if (!wp_verify_nonce($nonce, MioShop::MWS_FORM_NONCE)) {
//			$this->sendErrorAndDie(__('Neověřený požadavek.', 'mwshop'));
//		}
		if (!isset($_POST['addToCart'])) {
			// TODO #upsell error (call $this->sendErrorAndDie()
			return;
		}

		if (!isset($_POST['upsellId'])) {
			// TODO #upsell error (call $this->sendErrorAndDie()
			return;
		}

		$addToCart = (bool) filter_var($_POST['addToCart'], FILTER_VALIDATE_BOOLEAN);
		$upsellId = (int) $_POST['upsellId'];

		$upsell = Upsell::getOneById($upsellId);
		\assert($upsell instanceof Upsell);
		$form = MwsForm::getOneById($upsell->getFormId());
		\assert($form instanceof MwsForm);

		$cart = MWS()->getFormCart($form);

		if (!$cart->isFormProcessed()) {
			// TODO #upsell error (call $this->sendErrorAndDie()
			return;
		}

		$actualSecurityCode = $_POST[Upsell::SECURITY_CODE_QUERY_PARAMETER] ?? null;
		if ($actualSecurityCode === null) {
			// TODO #upsell error (call $this->sendErrorAndDie()
			return;
		}

		$expectedSecurityCode = $cart->securityCode();
		if ($expectedSecurityCode === null || $actualSecurityCode !== $expectedSecurityCode) {
			// TODO #upsell error (call $this->sendErrorAndDie()
			return;
		}

		if (!$upsell->isValid()) {
			// TODO #upsell error (call $this->sendErrorAndDie()
			return;
		}
//		$unprocessed = false;
//		foreach ($cart->getValidUnprocessedUpsells() as $unprocessedUpsell) {
//			if ($unprocessedUpsell->getId() === $upsell->getId()) {
//				$unprocessed = true;
//				break;
//			}
//		}
//
//		if (!$unprocessed) {
//			return;
//		}

		try {
			$cart->processUpsell($upsell, $addToCart);
		} catch (UpsellProcessException $e) {
			// TODO #upsell error (call $this->sendErrorAndDie()
			return;
		}

		$cart->save();
		$nextUpsell = $cart->getNextValidUnprocessedUpsell();
		if ($nextUpsell !== null) {
			wp_send_json_success([
				'nextUrl' => add_query_arg([Upsell::SECURITY_CODE_QUERY_PARAMETER => $cart->securityCode()], $nextUpsell->getUrl()),
			]);
		} else {
			$this->formProcessor->finishOrder($cart);
		}
	}

	/**
	 * Send flash error message and die. Optionally add detailed message, different for user and for administrator.
	 *
	 * @param string $mainMsg Overridable message
	 * @param string $detailUser Optional message for user.
	 * @param string $detailAdmin Optional message for admin. If empty, then $detailUser is used.
	 */
	private function sendErrorAndDie(string $mainMsg = '', string $detailUser = '', string $detailAdmin = '')
	{
		if (empty($mainMsg)) {
			$mainMsg = __('Objednávku se nepodařilo odeslat. %s', 'mwshop');
		}

		wp_send_json_error([
			'flashMessage' => '<div class="mws_error">' . sprintf(
					$mainMsg,
					(MWS()->edit_mode ? (empty($detailAdmin) ? $detailUser : $detailAdmin) : $detailUser)
				) . '</div>',
		]);
	}

}
