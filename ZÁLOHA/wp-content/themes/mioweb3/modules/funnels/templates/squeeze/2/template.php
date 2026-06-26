<?php
/**
 * Template Title: Vstupní stránka s videem 2
 * Template Description: Vstupní stránka s videem, formulářem a popisem obsahu nabízeného produktu.
 */
__('Vstupní stránka s videem 2', 'cms_ve');
__('Vstupní stránka s videem, formulářem a popisem obsahu nabízeného produktu.', 'cms_ve');
global $vePage;


if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
