<?php
$web_setting = [

	've_header' => [
		'appearance' => 'type1',
		'logo_setting' => 'text',
		'logo' => '',
		'logo_text' => 'NÁZEV<b>WEBU</b>',
		'logo_font' => [
			'font-size' => '24',
			'font-family' => '',
			'weight' => '',
			'color' => '',
		],
		'before_header' => '',
		'background_color' => [
			'color1' => '#ffffff',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
			'imageid' => '',
		],
		'menu' => $installed_menus['main_header'],
		'menu_font' => [
			'font-size' => '15',
			'font-family' => '',
			'weight' => '',
			'color' => '#575757',
		],
		'menu_active_color' => '#158ebf',
		'menu_submenu_text_color' => '#ffffff',
		'menu_bg' => [
			'color1' => '#121212',
			'color2' => '',
		],
		'header_width' => [
			'size' => '',
			'unit' => 'px',
		],
		'header_padding' => '20',
		'fixed_header' => '1',
		'background_color_fix' => [
			'color1' => '',
			'color2' => '',
			'transparency' => '100',
		],
		'header_padding_fix' => '15',
		'header_shadow_fix' => '1',
	],


	've_footer' => [
		'custom_footer' => $installed_contents['footer'],
		'hide_footer_end' => '1',
		'appearance' => 'type1',
		'text' => '',
		'menu' => '',
		'background_color' => [
			'color1' => '#121212',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
			'imageid' => '',
		],
		'font' => [
			'font-size' => '10',
			'font-family' => '',
			'weight' => '',
			'color' => '#7a7a7a',
		],
		'footer_width' => [
			'size' => '',
			'unit' => 'px',
		],
	],
	've_appearance' => [
		'background_color' => '#158ebf',
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'cover' => 'cover',
			'mobile_hide' => 'mobile_hide',
			'image' => '',
			'imageid' => '',
			'pattern' => '',
		],
		'font' => [
			'font-size' => '16',
			'font-family' => 'Open Sans',
			'weight' => '400',
			'line-height' => '',
			'color' => '#111111',
		],
		'link_color' => '#158ebf',
		'title_font' => [
			'font-family' => 'Open Sans',
			'weight' => '700',
			'color' => '',
		],
		'h1_font' => [
			'font-size' => '50',
			'color' => '',
		],
		'h2_font' => [
			'font-size' => '40',
			'color' => '',
		],
		'h3_font' => [
			'font-size' => '35',
			'color' => '',
		],
		'h4_font' => [
			'font-size' => '30',
			'color' => '',
		],
		'h5_font' => [
			'font-size' => '25',
			'color' => '',
		],
		'h6_font' => [
			'font-size' => '20',
			'color' => '',
		],
		'li' => '6',
	],

	'blog_comments' => [
		'blog_logolink' => 'web',
		'comments' => [
			'wordpress' => 'wordpress',
		],
		'comments_order' => 'facebook',
		'show_share' => [
			'facebook' => 'facebook',
			'facebook_share' => 'facebook_share',
		],
		'show_share_list' => [
			'facebook' => 'facebook',
			'facebook_share' => 'facebook_share',
		],
		'hide' => [
			'related_posts' => 'related_posts',
			'date' => 'date',
		],
	],

	'blog_sidebars' => [
		'sidebar_blog' => $installed_sidebars['main'],
		'sidebar_category' => $installed_sidebars['main'],
		'sidebar_post' => $installed_sidebars['main'],
		'sidebar_author' => $installed_sidebars['main'],
		'sidebar_tag' => $installed_sidebars['main'],
		'sidebar_search' => $installed_sidebars['main'],
	],


	'blog_appearance' => [
		'appearance' => 'style1',
		'structure' => 'right',
		'post_look' => '1',
		'background_color' => '#ebebeb',
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
			'imageid' => '',
			'pattern' => '',
		],
		'font' => [
			'font-size' => '16',
			'font-family' => 'Open Sans',
			'weight' => '400',
			'line-height' => '',
			'color' => '#111111',
		],
		'link_color' => '#158ebf',
		'title_font' => [
			'font-family' => 'Open Sans',
			'weight' => '700',
			'color' => '',
		],
		'tb_background' => [
			'color1' => '#158ebf',
			'color2' => '',
		],
		'tb_font' => [
			'font-size' => '45',
			'font-family' => '',
			'weight' => '',
			'line-height' => '',
			'color' => '#ffffff',
		],
		'article_font' => [
			'font-size' => '35',
			'font-family' => '',
			'weight' => '',
			'line-height' => '',
			'color' => '',
		],
		'article_font_text' => [
			'font-size' => '',
			'font-family' => '',
			'weight' => '',
			'line-height' => '',
			'color' => '',
		],
		'sidebar_font' => [
			'font-size' => '20',
			'font-family' => '',
			'weight' => '',
			'line-height' => '',
			'color' => '#158ebf',
		],
		'h1_font' => [
			'font-size' => '50',
			'color' => '',
		],
		'h2_font' => [
			'font-size' => '40',
			'color' => '',
		],
		'h3_font' => [
			'font-size' => '35',
			'color' => '',
		],
		'h4_font' => [
			'font-size' => '30',
			'color' => '',
		],
		'h5_font' => [
			'font-size' => '25',
			'color' => '',
		],
		'h6_font' => [
			'font-size' => '20',
			'color' => '',
		],
		'li' => '6',
	],

	'blog_footer' => [
		'show' => 'global',
		'custom_footer' => '',
		'appearance' => 'type1',
		'text' => '',
		'menu' => '',
		'background_color' => [
			'color1' => '',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
			'imageid' => '',
		],
		'font' => [
			'font-size' => '13',
			'font-family' => '',
			'weight' => '',
			'color' => '#7a7a7a',
		],
		'footer_width' => [
			'size' => '',
			'unit' => 'px',
		],
	],

	'blog_header' => [
		'show' => 'global',
	],
];
