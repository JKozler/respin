<?php

return [
	'title' => __('Webinářový prodej', 'mw_funnels'),
	'desc' => __('Získejte nové kontakty a&nbsp;prodávejte díky živým i&nbsp;evergreen webinářům.', 'mw_funnels'),
	'icon' => 'mic',
	'items' => [
		[
			'title' => __('Vstupní stránka', 'mw_funnels'),
			'type' => 'squeeze',
			'page' => 'landing',
		],
		[
			'title' => __('Poděkování po registraci', 'mw_funnels'),
			'type' => 'thanks',
			'page' => 'reg_thanks',
			'nav_hide' => 1,
			'order' => 1,
		],
		[
			'title' => __('Vysílání webináře', 'mw_funnels'),
			'type' => 'content',
			'page' => 'webinar',
			'nav_hide' => 1,
			'order' => 2,
		],
		[
			'title' => __('Objednávka', 'mw_funnels'),
			'type' => 'order',
			'page' => 'order',
			'nav_hide' => 1,
			'order' => 4,
		],
		[
			'title' => __('Prodejní stránka', 'mw_funnels'),
			'type' => 'sale',
			'page' => 'sale',
			'nav_hide' => 1,
			'order' => 3,
		],

		[
			'title' => __('Poděkování po nákupu', 'mw_funnels'),
			'type' => 'thanks',
			'page' => 'order_thanks',
			'nav_hide' => 1,
			'order' => 5,
		],
	],
];
