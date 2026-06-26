<?php

use Mioweb\Shop\Order\Order;

define('MWS_PAYMENT_META_KEY', MWS_OPTION . 'payment');
define('MWS_PAYMENT_META_KEY_STATUS', MWS_OPTION . 'status');
define('MWS_PAYMENT_META_KEY_PAYMENT_GATEWAY_ID', MWS_OPTION . 'payment_gateway_id');
define('MWS_PAYMENT_META_KEY_PAYMENT_GATEWAY_PAYMENT_ID', MWS_OPTION . 'payment_gateway_payment_id');
define('MWS_PAYMENT_META_KEY_PAYMENT_METHOD_TYPE', MWS_OPTION . 'payment_method_type');
define('MWS_PAYMENT_META_KEY_PAYMENT_URL', MWS_OPTION . 'payment_url');

class MwsPayment
{

	const MWS_PAYMENT_META_KEY_STATUS = MWS_PAYMENT_META_KEY_STATUS;

	/** @var WP_Post Post object. */
	private $_post = null;

	private $_orderId = null;

	private $_status = MwsPaymentStatus::Created;

	private $_paymentMethodType = null;

	private $_paymentGatewayId = null;

	private $_paymentGatewayPaymentId = null;

	private ?string $_paymentUrl = null;

	private $_data = [];

	public function __construct(
		?WP_Post $post = null,
		?Order $order = null,
		?string $paymentMethodType = null,
		?MwsPaymentGateway $paymentGateway = null,
		?string $paymentGatewayPaymentId = null,
		?string $paymentUrl = null
	)
	{
		if ($post) { // existing payment
			$this->_post = $post;
			$this->load();
		} else { // new payment
			if (!$order) {
				throw new MwsException('Order need value.');
			}
			$this->_orderId = $order->getId();
			$this->_paymentGatewayId = $paymentGateway->getId();
			$this->_paymentGatewayPaymentId = $paymentGatewayPaymentId;
			$this->_paymentMethodType = $paymentMethodType;
			$this->_paymentUrl = $paymentUrl;
		}
	}

	public function getOrderId(): ?string
	{
		return $this->_orderId;
	}

	public function getPaymentMethodType(): ?string
	{
		return $this->_paymentMethodType;
	}

	public function getPaymentUrl(): ?string
	{
		return $this->_paymentUrl;
	}

	public function getPaymentGatewayId(): ?string
	{
		return $this->_paymentGatewayId;
	}

	public function getPaymentGatewayPaymentId(): ?string
	{
		return $this->_paymentGatewayPaymentId;
	}

	public function getStatus(): string
	{
		return $this->_status;
	}

	public function setStatus(string $status): void
	{
		$this->_status = $status;
	}

	public function getData(): array
	{
		return $this->_data;
	}

	public function setData(array $data): void
	{
		$this->_data = $data;
	}

	public function isPaid(): bool
	{
		return $this->getStatus() === MwsPaymentStatus::Paid;
	}

	public function getCreatedAt(): int
	{
		return strtotime($this->_post->post_date);
	}

	private function load(): void
	{
		$this->_orderId = $this->_post->post_parent;
		$this->_status = get_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_STATUS, true) ?: MwsPaymentStatus::Created;
		$this->_paymentGatewayId = get_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_PAYMENT_GATEWAY_ID, true) ?: null;
		$this->_paymentGatewayPaymentId = get_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_PAYMENT_GATEWAY_PAYMENT_ID, true) ?: null;
		$this->_paymentMethodType = get_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_PAYMENT_METHOD_TYPE, true) ?: null;
		$this->_paymentUrl = get_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_PAYMENT_URL, true) ?: null;
		$meta = get_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY, true);
		$this->_data = $meta['data'] ?? [];
	}

	public function save(): bool
	{
		$meta = [
			'data' => $this->getData(),
		];
		if (!$this->_post) { // create new post
			$args = [
				'post_title' => '',
				'post_status' => 'publish',
				'post_parent' => $this->getOrderId(),
				'post_type' => MWS_PAYMENT_SLUG,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_name' => '',
				'meta_input' => [
					MWS_PAYMENT_META_KEY => $meta,
					MWS_PAYMENT_META_KEY_STATUS => $this->getStatus(),
					MWS_PAYMENT_META_KEY_PAYMENT_GATEWAY_ID => $this->getPaymentGatewayId(),
					MWS_PAYMENT_META_KEY_PAYMENT_GATEWAY_PAYMENT_ID => $this->getPaymentGatewayPaymentId(),
					MWS_PAYMENT_META_KEY_PAYMENT_METHOD_TYPE => $this->getPaymentMethodType(),
					MWS_PAYMENT_META_KEY_PAYMENT_URL => $this->getPaymentUrl(),
				],
			];
			$postId = wp_insert_post($args);
			if ($postId) {
				$this->_post = get_post($postId);
				$this->load();
			} else {
				mwshoplog('New payment could not be saved into database.', MWLL_ERROR, 'payment');

				return false;
			}
		} else {
			update_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY, $meta);
			update_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_STATUS, $this->getStatus());
			update_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_PAYMENT_GATEWAY_ID, $this->getPaymentGatewayId());
			update_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_PAYMENT_GATEWAY_PAYMENT_ID, $this->getPaymentGatewayPaymentId());
			update_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_PAYMENT_METHOD_TYPE, $this->getPaymentMethodType());
			update_post_meta($this->_post->ID, MWS_PAYMENT_META_KEY_PAYMENT_URL, $this->getPaymentUrl());
		}

		return true;
	}

	public static function createNew(WP_Post $post): ?self
	{
		if ($post->post_type !== MWS_PAYMENT_SLUG) {
			throw new MwsException('Passed post is not of payment type.');
		}
		$obj = MwObjectCache::get(self::class, $post->ID);
		if (!$obj) {
			$obj = new self($post);
			MwObjectCache::add($obj, $post->ID);
		}

		return $obj;
	}

	/** @return MwsPayment[] */
	public static function getAllByOrder(Order $order): array
	{
		$args = [
			'post_parent' => $order->getId(),
			'post_type' => MWS_PAYMENT_SLUG,
			'post_status' => 'any',
			'posts_per_page' => -1,
		];

		return array_map(function (WP_Post $post) {
			return self::createNew($post);
		}, get_posts($args));
	}

}
