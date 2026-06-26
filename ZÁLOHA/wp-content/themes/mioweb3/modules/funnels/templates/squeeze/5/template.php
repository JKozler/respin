<?php
/**
 * Template Title: Jednoduchá textová vstupní stránka
 * Template Description: Jednoduchá textová vstupní stránka obsahující pouze text a tlačítko s formulářem.
 */
__('Jednoduchá textová vstupní stránka', 'cms_ve');
__('Jednoduchá textová vstupní stránka obsahující pouze text a tlačítko s formulářem.', 'cms_ve');


if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
