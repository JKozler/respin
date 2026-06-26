<?php

namespace Mioweb\VisualEditor\Lib;
use mwFrontComponents;

final class Link
{

	private ?int $_pageId;

	private string $_url;

	private bool $_useUrl;

	private bool $_targetBlank;

	public function __construct(array $link)
	{
		$this->_pageId = isset($link['page']) ? (int) $link['page'] : null;
		$this->_url = $link['link'] ?? '';
		$this->_useUrl = isset($link['use_url']) || (!isset($link['use_url']) && $this->_pageId === null && $this->_url) ? true : false;
		$this->_targetBlank = isset($link['target']) ? true : false;
	}

	public function getLink($hash = false): string
	{
		$link = '';
		if ($this->_useUrl) {
			$link = $this->_url;
		} elseif ($this->_pageId) {
			$link = get_permalink($this->_pageId);
		}

		if (!$link && $hash) {
			$link = '#';
		}

		return $link;
	}

	public function getTarget(): string
	{
		return $this->_targetBlank ? 'target="_blank"' : '';
	}

	public function getTargetVal(): string
	{
		return $this->_targetBlank ? '_blank' : '';
	}

	public function printLink(array $args, string $class = ''): string
	{
		$args['link'] = $this->getLink(true);
		$args['target'] = $this->getTargetVal();

		return mwFrontComponents::link($args, $class);
	}

	// back compatibility old function @TODO remove
	public static function create_link($link, $add_args = true, $hash = false): string
	{
		$new_link = '';
		$args = [];

		if (!is_array($link)) {
			$old = $link;
			$link = [];
			$link['link'] = $old;
		}

		if (!isset($link['page']) && isset($link['link']) && $link['link']) {
			$link['use_url'] = 1;
		}

		if (isset($link['use_url'])) {
			$new_link = $link['link'];
		} elseif (isset($link['page']) && $link['page']) {
			$new_link = get_permalink($link['page']);
		}

		if ($hash && !$new_link) {
			$new_link = '#';
		}

		//dont include atributes used by wordpress
		/*if ($add_args && $new_link && substr($new_link, 0, 1) !== '#') {
			foreach ($_GET as $key => $val) {
				if ($key != 'page_id' && $key != 's' && $key != 'p' && $key != 'author' && $key != 'tag' && $key != 'cat' && $key != 'paged' && $key != 'mw_preview') {
					$args[$key] = $val;
				}
			}
			if (count($args)) {
				$new_link = esc_url(add_query_arg($args, $new_link));
			}
		}*/

		return $new_link;
	}

}
