<?php
/**
 * Template Title: Jednoduchá stránka s videem
 * Template Description: Stránka obsahující video.
 */
__('Jednoduchá stránka s videem', 'cms_ve');
__('Stránka obsahující video.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
