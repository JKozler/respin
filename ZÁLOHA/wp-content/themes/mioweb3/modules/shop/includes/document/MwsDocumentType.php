<?php

abstract class MwsDocumentType extends MwsBasicEnum
{
	const Proforma = 'proforma';
	const Invoice = 'invoice';
	const SimplifiedInvoice = 'simplified-invoice';

	protected static function doInitCaptions(): array
	{
		return [
			self::Proforma => __('Zálohová faktura', 'mwshop'),
			self::Invoice => __('Faktura', 'mwshop'),
			self::SimplifiedInvoice => __('Zjednodušený daň. doklad', 'mwshop'),
		];
	}

}
