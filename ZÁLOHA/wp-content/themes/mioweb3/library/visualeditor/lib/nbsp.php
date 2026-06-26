<?php

function mw_add_element_nbsp($content, $element)
{
	$disable = [
		'html',
		'fapi',
		'video',
		'seform',
		'box',
		'twocols', 'variable_content',
	];

	return in_array($element, $disable) ? $content : mw_add_nbsp($content);
}

function mw_add_nbsp($content)
{
	global $mwContainer;
	$phr_list = [
		'cz' => [
			'phrases' => [
				'a',
				'i',
				's',
				'v',
				'z',
				'o',
				'u',
				'k',
				'na',
				'do',
				'za',
				'na',
				'po',
				'a i',
				'i v',
			],
		],
	];
	$phrases = $phr_list['cz']['phrases'];
	$phrases = mw_get_all_phrases_variants($phrases);

	$pattern = [];

	foreach ($phrases as $phrase) {
		$pattern[] = '/\\s+' . $phrase . '+\\s+/';
	}

	return preg_replace_callback($pattern, 'mw_format_matches', $content);
}

function mw_get_all_phrases_variants($phrases)
{
	$all_variants = [];

	foreach ($phrases as $phrase) {
		$all_variants[] = strtolower($phrase);

		$all_variants[] = strtoupper($phrase);

		if (strlen($phrase) > 1) {
			$all_variants[] = ucfirst($phrase);
		}
	}

	return $all_variants;
}

function mw_format_matches($matches)
{
	$match = trim($matches[0]);
	$match = preg_replace('/\\s/', '&nbsp;', $match);

	$phrase = ' ' . $match . '&nbsp;';

	return $phrase;
}

add_filter('the_content', 'mw_add_nbsp');
add_filter('widget_text', 'mw_add_nbsp');
add_filter('comment_text', 'mw_add_nbsp');
add_filter('get_the_excerpt', 'mw_add_nbsp');
add_filter('the_title', 'mw_add_nbsp');
add_filter('widget_title', 'mw_add_nbsp');
