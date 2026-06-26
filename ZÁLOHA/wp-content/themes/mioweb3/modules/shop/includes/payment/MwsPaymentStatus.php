<?php

abstract class MwsPaymentStatus extends MwsBasicEnum
{

	const Created = 'created';
	const Paid = 'paid';
	const Canceled = 'canceled';

	/** @return array<string, string> */
	protected static function doInitCaptions(): array
	{
		return [
			self::Created => __('Nezaplacená', 'mwshop'),
			self::Paid => __('Zaplacená', 'mwshop'),
			self::Canceled => __('Stornovaná', 'mwshop'),
		];
	}

}
