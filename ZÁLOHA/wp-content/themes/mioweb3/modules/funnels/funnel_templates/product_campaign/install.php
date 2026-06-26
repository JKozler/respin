<?php

return [
	'title' => __('Produktová kampaň', 'mw_funnels'),
	'desc' => __('Prodávejte efektivně a&nbsp;automatizovaně své fyzické i&nbsp;digitální produkty.', 'mw_funnels'),
	'icon' => 'dollar-sign',
	'items' => [
		[
			'title' => __('Vstupní stránka', 'mw_funnels'),
			'type' => 'squeeze',
			'page' => 'squeeze',
		],
		[
			'title' => __('Objednávka', 'mw_funnels'),
			'type' => 'order',
			'page' => 'order',
			'nav_hide' => 1,
			'order' => 3,
		],
		[
			'title' => __('Prodejní stránka', 'mw_funnels'),
			'type' => 'sale',
			'page' => 'sale',
			'nav_hide' => 1,
			'order' => 2,
		],
		[
			'title' => __('Poděkování po registraci', 'mw_funnels'),
			'type' => 'thanks',
			'page' => 'reg_thanks',
			'limited_access' => 1,
			'nav_hide' => 1,
			'order' => 1,
		],
		[
			'title' => __('Poděkování po nákupu', 'mw_funnels'),
			'type' => 'thanks',
			'page' => 'order_thanks',
			'nav_hide' => 1,
			'order' => 4,
		],
	],
];
