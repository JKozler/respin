<?php
/**
 * Template Title: Jednoduchá vstupní stránka s videem
 * Template Description: Vstupní stránka s obsahem na středu obrazovky a s obrázkem na pozadí.
 */
__('Jednoduchá vstupní stránka s videem', 'cms_ve');
__('Vstupní stránka s obsahem na středu obrazovky a s obrázkem na pozadí.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
