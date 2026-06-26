<?php
/**
 * Template Title: Stránka s ebookem zdarma
 * Template Description: Vstupní stránka nabízející ebook za kontakt.
 */
__('Stránka s ebookem zdarma', 'cms_ve');
__('Vstupní stránka nabízející ebook za kontakt.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
