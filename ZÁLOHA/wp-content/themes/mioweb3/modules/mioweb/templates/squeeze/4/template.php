<?php
/**
 * Template Title: Textová vstupní stránka
 * Template Description: Vstupní stránka s obsahem na středu obrazovky a s obrázkem na pozadí.
 */
__('Textová vstupní stránka', 'cms_ve');
__('Vstupní stránka s obsahem na středu obrazovky a s obrázkem na pozadí.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
