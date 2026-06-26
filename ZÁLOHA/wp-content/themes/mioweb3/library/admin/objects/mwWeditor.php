<?php

class mwWeditor extends mwPost
{

	private $_content;

	public function getContent(): string
	{
		if (!$this->_content) {
			$this->_content = MWDB()->getLayer($this->getID(), $this->getPostType());
		}

		return $this->_content;
	}

	public function setContent($content)
	{
		$this->_content = $content;
		MWDB()->updatePost(['ID' => $this->getID(), 'post_content' => $content]);
		MWDB()->setLayer($this->getID(), $this->getPostType(), $content);
	}

	public static function registerWeditorObjects()
	{
		$wp_args = [
			'labels' => [],
			'public' => false,
			'publicly_queryable' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => ['title', 'revisions'],
		];

		$mw_args = [
			'class' => 'mwWeditor',
			'supports' => ['visualeditor'],
			'public' => false,
			'weditor' => true,
			'labels' => [],
		];

		mwSetting()->registerPostType('cms_footer', $mw_args, $wp_args);
		mwSetting()->registerPostType('weditor', $mw_args, $wp_args);
		mwSetting()->registerPostType('ve_header', $mw_args, $wp_args);
		mwSetting()->registerPostType('ve_elvar', $mw_args, $wp_args);
		mwSetting()->registerPostType('mw_slider', $mw_args, $wp_args);
	}

}
