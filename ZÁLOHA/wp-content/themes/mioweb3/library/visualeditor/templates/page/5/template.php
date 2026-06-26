<?php
/**
 * Template Title: Stránka s podmenu
 * Template Description: Jednoduchá stránka s nadpisem a s menu s podstránkama na levé straně.
 */
__('Stránka s podmenu', 'cms_ve');
__('Jednoduchá stránka s nadpisem a s menu s podstránkama na levé straně.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
