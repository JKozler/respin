<?php
/**
 * Template Title: Jednoduchá textová vstupní stránka 1
 * Template Description: Jednoduchá textová vstupní stránka obsahující pouze text a tlačítko s formulářem.
 */
__('Jednoduchá textová vstupní stránka 1', 'cms_ve');
__('Jednoduchá textová vstupní stránka obsahující pouze text a tlačítko s formulářem.', 'cms_ve');


if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
