<?php
/**
 * Template Title: Nástěnka
 * Template Description: S krátkým úvodním textem, videem a seznamem lekcí.
 */
__('Nástěnka', 'cms_member');
__('S krátkým úvodním textem, videem a seznamem lekcí.', 'cms_member');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
