<?php
/**
 * Template Title: Prázdná stránka s nadpisem
 * Template Description: Jednoduchá stránka s nadpisem a podnadpisem v horním pruhu.
 */
__('Prázdná stránka s nadpisem', 'cms_ve');
__('Jednoduchá stránka s nadpisem a podnadpisem v horním pruhu', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
