<?php

return [
	'title' => __('Klasická kampaň', 'mw_funnels'),
	'desc' => __('Získejte důvěru obsahem zdarma a prodávejte nejen své online kurzy.', 'mw_funnels'),
	'icon' => 'film',
	'items' => [
		[
			'title' => __('Vstupní stránka', 'mw_funnels'),
			'type' => 'squeeze',
			'page' => 'squeeze',
		],
		[
			'title' => __('Obsah 1', 'mw_funnels'),
			'type' => 'content',
			'page' => 'content1',
			'limited_access' => 1,
			'order' => 1,
		],
		[
			'title' => __('Obsah 2', 'mw_funnels'),
			'type' => 'content',
			'page' => 'content2',
			'limited_access' => 1,
			'order' => 2,
		],
		[
			'title' => __('Obsah 3', 'mw_funnels'),
			'type' => 'content',
			'page' => 'content3',
			'limited_access' => 1,
			'order' => 3,
		],
		[
			'title' => __('Objednávka', 'mw_funnels'),
			'type' => 'order',
			'page' => 'order',
			'nav_hide' => 1,
			'order' => 5,
		],
		[
			'title' => __('Prodejní stránka', 'mw_funnels'),
			'type' => 'sale',
			'page' => 'sale',
			'nav_hide' => 1,
			'order' => 4,
		],
		[
			'title' => __('Poděkování po nákupu', 'mw_funnels'),
			'type' => 'thanks',
			'page' => 'thanks',
			'nav_hide' => 1,
			'order' => 6,
		],
	],
];
