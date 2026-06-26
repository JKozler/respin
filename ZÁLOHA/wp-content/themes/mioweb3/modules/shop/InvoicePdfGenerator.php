<?php declare(strict_types=1);

namespace Mioweb\Shop;

use Dompdf\Dompdf;
use Latte;
use Mioweb\Shop\Document\Document;

class InvoicePdfGenerator
{

	public function generate(Document $document): Dompdf
	{
		$tempDir = untrailingslashit(get_temp_dir());

		$latte = core()->getLatte();
		$templateName = 'default'; // TODO setting in future, currently there is only one template
		$content = $latte->renderToString(__DIR__ . '/templates/invoices/' . $templateName . '.latte', [
			'document' => $document,
			'contactSettings' => MWS()->getInvoiceContactSettings(),
		]);

		$dompdf = new Dompdf([
			'tempDir' => $tempDir,
		]);

		$dompdf->loadHtml($content);

		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();

		// Add paginator
		$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
		$dompdf->getCanvas()->page_text(554, 816, '{PAGE_NUM}/{PAGE_COUNT}', $font, 9, [0.46, 0.46, 0.46]);

		return $dompdf;
	}

}
