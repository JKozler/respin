<?php
/**
 * Template Title: Stránka s ebookem zdarma
 * Template Description: Stránka zaměřenená na prodej Vaší knihy.
 */

__('Stránka s ebookem zdarma', 'cms_ve');
__('Stránka zaměřenená na stažení ebooku zdarma.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
