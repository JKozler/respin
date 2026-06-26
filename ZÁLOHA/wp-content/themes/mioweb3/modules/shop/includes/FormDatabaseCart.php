<?php declare(strict_types=1);

namespace Mioweb\Shop;

use MwObjectCache;
use MwsException;
use MwsForm;
use MwsSession;
use WP_Post;
use function delete_post_meta;
use function get_post_meta;
use const MWS_FORM_CART_META_KEY_DELAYED_AUTO_PROCESS_RESPONSE;
use const MWS_FORM_CART_META_KEY_IS_FORM_PROCESSED;

class FormDatabaseCart extends FormCart
{

	private ?WP_Post $_post;

	/** @var mixed[]|null */
	private ?array $delayAutoProcessResponse = null;

	private bool $deleted = false;

	public function __construct(MwsForm $form, ?WP_Post $post = null)
	{
		$this->_post = $post;

		$data = $post !== null ? get_post_meta($post->ID, MWS_FORM_CART_META_KEY, true) ?: null : null;

		parent::__construct($form, $data);
	}

	public function getId(): ?int
	{
		return $this->_post ? $this->_post->ID : null;
	}

	public function getCreatedAt(): ?\DateTimeImmutable
	{
		if ($this->_post === null) {
			return null;
		}

		return (new \DateTimeImmutable($this->_post->post_date_gmt, new \DateTimeZone('GMT')))->setTimezone(wp_timezone());
	}

	public function clear(bool $reload = true): void
	{
		$this->getSession()->destroy();

		$sessionId = $this->getSessionId();
		if ((bool) $sessionId) {
			$posts = self::getAllPostsBySessionId($sessionId);

			foreach ($posts as $post) {
				if ($this->_post !== null && $post->ID === $this->_post->ID) {
					$this->_post = null;
					$this->getItems()->clear();
					$this->clearProcessedUpsellIds();
					$this->setStoredTotalPrice(null);
					$this->setShipping(null);
					$this->setPaymentMethod(null);
					$this->setFormProcessed(false);
				}

				wp_delete_post($post->ID);
			}
		}

		if ($reload) {
			$this->loadFromSession(true);
		}
	}

	public function delete(): void
	{
		$this->deleted = true;

		if ($this->_post !== null) {
			wp_delete_post($this->_post->ID);
			MwObjectCache::remove($this, $this->getId());
		}
	}

	protected function getSession(): MwsSessionSection
	{
		if ($this->_session === null) {
			$session = MwsSession::getInstance()->getSection('form-' . $this->getForm()->getId());
			$session->setExpiration('1 DAY');
			$this->_session = $session;
		}

		return $this->_session;
	}

	protected function loadFromSession(bool $reload = false): void
	{
		if ($this->_loaded && !$reload) {
			return;
		}

		$sessionId = $this->getSessionId();
		if ((bool) $sessionId) {
			$post = self::getOnePostBySessionId($sessionId);

			if ($post !== null) {
				$this->_post = $post;
				$data = get_post_meta($post->ID, MWS_FORM_CART_META_KEY, true);

				$this->loadFromArray($data, $reload);
			}
		}

		$this->_loaded = true;
	}

	public function save(): void
	{
		if ($this->deleted) {
			return;
		}

		parent::save();

		if ($this->_post === null) {
			// Create new order.
			$sessionId = $this->getSessionId();
			if (!$sessionId) {
				throw new \Exception('No session ID provided.');
			}

			$args = [
				'post_title' => __('Nedokončená objednávka z prodejního formuláře', 'mwshop'),
				'post_status' => 'publish',
				'post_type' => MWS_FORM_CART_SLUG,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_name' => sanitize_title(__('nedokoncena-objednávka', 'mwshop') . sprintf('_%s', $sessionId)),
				'meta_input' => [
					MWS_FORM_CART_META_KEY => $this->toArray(),
					MWS_FORM_CART_META_KEY_SESSION_ID => $sessionId,
					MWS_FORM_CART_META_KEY_IS_FORM_PROCESSED => $this->isFormProcessed(),
//					MWS_FORM_CART_META_KEY_DELAYED_AUTO_PROCESS_RESPONSE => $this->getDelayAutoProcessResponse(),
				],
			];
			$postId = wp_insert_post($args, false);
			if ($postId) {
				$this->_post = get_post($postId);
			}
		} else {
			update_post_meta($this->_post->ID, MWS_FORM_CART_META_KEY, $this->toArray());
			update_post_meta($this->_post->ID, MWS_FORM_CART_META_KEY_IS_FORM_PROCESSED, $this->isFormProcessed());

			$delayAutoProcessResponse = $this->getDelayAutoProcessResponse();
			if ($delayAutoProcessResponse !== null) {
				update_post_meta($this->_post->ID, MWS_FORM_CART_META_KEY_DELAYED_AUTO_PROCESS_RESPONSE, $delayAutoProcessResponse);
			} else {
				delete_post_meta($this->_post->ID, MWS_FORM_CART_META_KEY_DELAYED_AUTO_PROCESS_RESPONSE);
			}
		}
	}

	/** @return mixed[]|null */
	public function getDelayAutoProcessResponse(): ?array
	{
		return $this->delayAutoProcessResponse;
	}

	/** @param mixed[]|null $delayAutoProcessResponse */
	public function setDelayAutoProcessResponse(?array $delayAutoProcessResponse): void
	{
		$this->delayAutoProcessResponse = $delayAutoProcessResponse;
	}

	public static function getOneById(int $id): ?self
	{
		$post = get_post($id);
		if ($post) {
			try {
				$form = self::getFormByPost($post);

				return static::createNew($form, $post);
			} catch (MwsException $e) {
				mwshoplog(
					sprintf(__('Nepodařilo se vytvořit instanci nedokončené objednávky [%d] se zprávou: %s', 'mwshop'), $id, $e->getMessage()),
					MWLL_ERROR
				);
			}
		}

		return null;
	}

	/** @return array<self> */
	public static function getAll(array $args): array
	{
		$posts = get_posts($args);
		$result = [];

		foreach ($posts as $post) {
			$form = self::getFormByPost($post);
			$result[] = self::createNew($form, $post);
		}

		return $result;
	}

	public static function getOneBySessionId(string $sessionId): ?self
	{
		$post = self::getOnePostBySessionId($sessionId);

		if ($post) {
			$form = self::getFormByPost($post);

			return self::createNew($form, $post);
		}

		return null;
	}

	/** @return array<WP_Post> */
	private static function getAllPostsBySessionId(string $sessionId): array
	{
		if (!(bool) $sessionId) {
			return [];
		}

		$args = [
			'meta_query' => [
				'relation' => 'AND',
				'session_id' => [
					'key' => MWS_FORM_CART_META_KEY_SESSION_ID,
					'value' => $sessionId,
				],
			],
			'post_type' => MWS_FORM_CART_SLUG,
			'post_status' => 'any',
			'posts_per_page' => -1,
		];

		return get_posts($args);
	}

	private static function getOnePostBySessionId(string $sessionId): ?WP_Post
	{
		if (!(bool) $sessionId) {
			return null;
		}

		$posts = self::getAllPostsBySessionId($sessionId);
		$post = reset($posts);

		return $post ?: null;
	}

	public static function createNew(MwsForm $form, WP_Post $post, bool $useCache = true): ?self
	{
		if (get_post_type($post) != MWS_FORM_CART_SLUG) {
			throw new MwsException('Passed post is not of form database cart type.');
		}

		if ($useCache) {
			//Is created already?
			$obj = MwObjectCache::get(self::class, $post->ID);
			if (!$obj) {
				$obj = new self($form, $post);
				MwObjectCache::add($obj, $obj->getId());
			}

			return $obj;
		}

		return new self($form, $post);
	}

	private static function getFormByPost(WP_Post $post): MwsForm
	{
		$data = get_post_meta($post->ID, MWS_FORM_CART_META_KEY, true) ?: [];

		if (!isset($data['formId'])) {
			throw new MwsException('Post meta does not contains form ID.');
		}

		$formId = $data['formId'];
		$form = MwsForm::getOneById($formId);
		\assert($form instanceof MwsForm || $form === null);

		if ($form === null) {
			throw new MwsException('Form with ID ' . $formId . ' not exist.');
		}

		return $form;
	}

	/** @return string|false */
	private function getSessionId()
	{
		return session_id();
	}


}
