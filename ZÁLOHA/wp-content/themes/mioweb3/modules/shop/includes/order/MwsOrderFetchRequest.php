<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

use MwsOrderSourceType;
use Nette\Database\Table\Selection;

class MwsOrderFetchRequest
{

	public const SOURCE_ESHOP = 0;

	public const DATE_TYPE_ISSUED_AT = 'issued_at';

	public const DATE_TYPE_PAID_AT = 'paid_at';

	/** @var string[]|null */
	private ?array $orderStatuses;

	private ?bool $isPaid;

	private ?int $source;

	private string $search;

	private ?string $dateType;

	private ?string $orderNumber;

	private ?string $currency;

	/** @var int[]|null */
	private ?array $paymentMethodIds;

	/** @var int[]|null */
	private ?array $shippingMethodIds;

	private bool $includeTestOrders;

	/** @var int|string|null */
	private $gateId;

	private ?bool $hasArchive;

	private ?string $orderDirection;

	private ?\DateTimeInterface $from;

	private ?\DateTimeInterface $to;

	private ?int $limit;

	private ?int $offset;

	/** @param string|int|null $gateId */
	public function __construct(
		?int $limit = 20,
		?int $offset = null,
		?array $orderStatuses = null,
		string $search = '',
		?bool $isPaid = null,
		?int $source = null,
		?string $dateType = null,
		?\DateTimeInterface $from = null,
		?\DateTimeInterface $to = null,
		?string $orderNumber = null,
		?string $currency = null,
		?array $paymentMethodIds = null,
		?array $shippingMethodIds = null,
		?bool $includeTestOrders = true,
		$gateId = null,
		?bool $hasArchive = null,
		?string $orderDirection = null
	)
	{
		$this->limit = $limit === -1 ? null : $limit;
		$this->offset = $offset;
		$this->from = $from;
		$this->to = $to;
		$this->orderStatuses = $orderStatuses;
		$this->isPaid = $isPaid;
		$this->source = $source;
		$this->search = $search;
		$this->dateType = $dateType;
		$this->orderNumber = $orderNumber;
		$this->currency = $currency;
		$this->paymentMethodIds = $paymentMethodIds;
		$this->shippingMethodIds = $shippingMethodIds;
		$this->includeTestOrders = $includeTestOrders;
		$this->gateId = $gateId;
		$this->hasArchive = $hasArchive;

		if ($orderDirection !== null && !in_array($orderDirection, ['ASC', 'DESC'], true)) {
			throw new \InvalidArgumentException('Value `orderDirection` must be "ASC" or "DESC"');
		}

		$this->orderDirection = $orderDirection;
	}

	public function getFrom(): ?\DateTimeInterface
	{
		return $this->from;
	}

	public function getTo(): ?\DateTimeInterface
	{
		return $this->to;
	}

	public function getLimit(): ?int
	{
		return $this->limit;
	}

	public function setLimit(?int $limit): void
	{
		$this->limit = $limit;
	}

	public function getOffset(): ?int
	{
		return $this->offset;
	}

	public function setOffset(int $offset): void
	{
		$this->offset = $offset;
	}

	/** @return mixed[] */
	public function buildQuery(Selection $selection): Selection
	{
		if ($this->limit !== null || $this->offset !== null) {
			$selection->limit($this->limit, $this->offset);
		}

		if ($this->from !== null || $this->to !== null) {
			if ($this->from !== null) {
				$selection->where('created_at >= ?', $this->from);
			}


			if ($this->to !== null) {
				$selection->where('created_at <= ?', $this->to);
			}
		}

		if ($this->orderDirection !== null) {
			$selection->order('id ' . $this->orderDirection);
		}

		// Order statuses
		if ($this->orderStatuses !== null) {
			$selection->where('status IN (?)', $this->orderStatuses);
		}

		// Is Paid
		if ($this->isPaid !== null) {
			$selection->where('is_paid = ?', $this->isPaid);
		}

		// Source
		if ($this->source !== null) {
			if ($this->source === self::SOURCE_ESHOP) {
				$selection->where('source_type = ?', MwsOrderSourceType::Eshop);
			} else {
				$selection
					->where('source_type = ?', MwsOrderSourceType::Form)
					->where('source_form_id = ?', $this->source);
			}
		}

		// Search
		if ($this->search) {
			$strings = array_map(fn (string $string): string => '%' . $string . '%', explode(' ', $this->search));

			if ($strings) {
				$searchColumns = ['variable_symbol', 'invoice_contact', 'note', 'customer_note'];
				$args = [];

				foreach ($searchColumns as $searchColumn) {
					$likeArg = '';

					foreach ($strings as $string) {
						$likeArg .= $searchColumn . ' LIKE ? OR ';
					}

					// Remove last OR
					$likeArg = substr($likeArg, 0, -4);

					$args[$likeArg] = count($strings) >= 2 ? $strings : $strings[0];
				}

				$selection->whereOr($args);
			}
		}

		// Dates
		if ($this->dateType !== null && ($this->from !== null || $this->to !== null)) {
			if ($this->dateType === self::DATE_TYPE_ISSUED_AT) {
				if ($this->from !== null) {
					$selection->where('DATE(created_at) >= ?', $this->from->format('Y-m-d'));
				}

				if ($this->to !== null) {
					$selection->where('DATE(created_at) <= ?', $this->to->format('Y-m-d'));
				}
			} elseif ($this->dateType === self::DATE_TYPE_PAID_AT) {
				// Filter all non-paid orders
				if (!$this->isPaid) {
					$selection->where('is_paid = 1');
				}

				if ($this->from !== null || $this->to !== null) {
					$selection->where('paid_at IS NOT NULL');

					if ($this->from !== null) {
						$selection->where('DATE(paid_at) >= ?', $this->from->format('Y-m-d'));
					}

					if ($this->to !== null) {
						$selection->where('DATE(paid_at) <= ?', $this->to->format('Y-m-d'));
					}
				}
			} else {
				throw new \Exception('Invalid date type.');
			}
		}

		// Number
		if ($this->orderNumber !== null) {
			$selection->where('variable_symbol = ?', $this->orderNumber);
		}

		// Currency
		if ($this->currency !== null) {
			$selection->where('currency = ?', strtolower($this->currency));
		}

		// Payment methods
		if ($this->paymentMethodIds !== null) {
			$selection->where('JSON_EXTRACT(payment, "$.id") IN (?)', $this->paymentMethodIds);
		}

		// Shipping methods
		if ($this->shippingMethodIds !== null) {
			$selection->where('JSON_EXTRACT(shipping, "$.shippingId") IN (?)', $this->shippingMethodIds);
		}

		// Test orders
		if (!$this->includeTestOrders) {
			$selection->where('is_test = 0');
		}
		// archive
		if ($this->hasArchive !== null) {
			if ($this->hasArchive) {
				$selection->where('archived_at IS NOT NULL');
			} else {
				$selection->where('archived_at IS NULL');
			}
		}

		if ((bool) $this->gateId) {
			if (is_int($this->gateId)) {
				$selection->where('gate_id = ?', $this->gateId);
			} else {
				$selection->where('gate.identifier = ?', $this->gateId);
			}
		}

		return $selection;
	}

}
