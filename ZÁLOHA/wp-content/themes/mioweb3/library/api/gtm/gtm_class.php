<?php

function MWGTM(): MwGoogleTagManager
{
   return MwGoogleTagManager::instance();
}

class MwGoogleTagManager
{

	private static $instance = null;

	/** @var string */
	private $containerId;

	/** @var array */
	private $currentPageDataLayer = null;

	/** @var array */
	private $dataLayer = [];

	private function __construct()
	{
		if ($this->isActive()) {
			$this->containerId = htmlspecialchars(mwApiConnect()->getApi('gtm')->getOption()['container_id'] ?? '', ENT_QUOTES);
		}
	}

	public function isActive(): bool
	{
		return mwApiConnect()->getApi('gtm')->isConnected();
	}

	public function getHeaderCode(): string
	{
		$code = '';
		if (!mwApiConnect()->getApi('google_analytics')->isConnected()) {
			$code .= '<script>';
			$code .= 'window.dataLayer = window.dataLayer || [];';
			$code .= $this->getDataLayer();
			$code .= '</script>';
		}
		$code .= sprintf("
			<!-- Google Tag Manager -->
			<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','dataLayer','%s');</script>
			<!-- End Google Tag Manager -->
		", $this->containerId);

		return $code;
	}

	public function getBodyCode(): string
	{
		return sprintf('
			<!-- Google Tag Manager (noscript) -->
			<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
			<!-- End Google Tag Manager (noscript) -->
		', $this->containerId);
	}

	/** @param array $data */
	public function pushDataLayer($data): void
	{
		$this->dataLayer[] = $data;
	}

	public function getDataLayer(): string
	{
		$codes = '';
		if ($this->isActive()) {
			$codes = sprintf('dataLayer.push(%s);', json_encode($this->getCurrentPageDataLayer()));
			foreach ($this->dataLayer as $data) {
				$codes .= sprintf('dataLayer.push(%s);', json_encode($data));
			}
		}

		return $codes;
	}

	public function getCurrentPageDataLayer()
	{
		if (!$this->currentPageDataLayer) {
			$this->setDefaultCurrentPageDataLayer();
		}

		return $this->currentPageDataLayer;
	}

	private function setDefaultCurrentPageDataLayer()
	{
		$data = [];

		if (is_home()) {
			$data = [
				'pageType' => 'blogHome',
			];
		} elseif (is_page()) {
			global $post;
			$data = [
				'pageType' => 'page',
				'pageInfo' => [
					'id' => $post->ID ?? '',
					'name' => $post->post_title ?? '',
				],
			];
		} elseif (is_singular('post')) {
			global $post;
			$data = [
				'pageType' => 'article',
				'pageInfo' => [
					'id' => $post->ID ?? '',
					'name' => $post->post_title ?? '',
				],
			];
		} elseif (is_category()) {
			$category = get_queried_object();
			$data = [
				'pageType' => 'blogCategory',
				'pageInfo' => [
					'id' => $category->term_id,
					'name' => $category->name,
				],
			];
		} elseif (is_author()) {
			$data = [
				'pageType' => 'author',
				'pageInfo' => [
					'id' => get_the_author_meta('ID'),
					'name' => get_the_author_meta('display_name'),
				],
			];
		} elseif (is_tag()) {
			$tag = get_queried_object();
			$data = [
				'pageType' => 'blogTag',
				'pageInfo' => [
					'id' => $tag->term_id,
					'name' => $tag->name,
				],
			];
		} elseif (is_archive()) {
			$data = [
				'pageType' => 'blogArchive',
			];
		} elseif (is_search()) {
			$data = [
				'pageType' => 'search',
				'searchFor' => $_GET['s'] ?? '',
			];
		} elseif (is_404()) {
			$data = [
				'pageType' => '404',
			];
		}

		$this->setCurrentPageDataLayer($data);
	}

	public function setCurrentPageDataLayer(array $data = []): void
	{
		$this->currentPageDataLayer = array_replace($data, $this->getBaseData());
	}

	public function getBaseData()
	{
		return [
			'ad_consent' => MwCookies()->isPermitted('marketing') ? 'granted' : 'denied',
			'analytics_consent' => MwCookies()->isPermitted('analytics') ? 'granted' : 'denied',
			'homePage' => is_front_page() ? 1 : 0,
		];
	}

	/** @return MwGoogleTagManager */
	public static function instance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
