<?php

global $post;

use Mioweb\Shop\Document\Document;

$document = Document::createNew($post);
if (!$document) {
	exit;
}

if ($document->getHash() !== ($_REQUEST['hash'] ?? '')) {
	exit;
}

// generate PDF file for download
if ($_REQUEST['downloadPdf'] ?? false) {
	$dompdf = MWS()->getInvoicePdfGenerator()->generate($document);
	$dompdf->stream($document->getName() . '.pdf', ['Attachment' => false]);
}

exit;
