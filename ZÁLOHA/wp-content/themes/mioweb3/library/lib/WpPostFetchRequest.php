<?php declare(strict_types=1);

namespace Mioweb\Lib;

class WpPostFetchRequest
{

	protected ?\DateTimeInterface $from;

	protected ?\DateTimeInterface $to;

	private int $num;

	private int $page;

	public function __construct(int $num = 20, int $page = 1, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null)
	{
		$this->num = $num;
		$this->page = $page;
		$this->from = $from;
		$this->to = $to;
	}

	/** @return mixed[] */
	public function buildQuery(): array
	{
		$query_args = [
			'posts_per_page' => $this->num,
			'paged' => $this->page,
		];

		if ($this->from !== null || $this->to !== null) {
			$dateQuery = [
				'column' => 'post_date',
				'inclusive' => true,
			];

			if ($this->from !== null) {
				$dateQuery['after'] = $this->from->format('Y-m-d');
			}


			if ($this->to !== null) {
				$dateQuery['before'] = $this->to->format('Y-m-d');
			}

			$query_args['date_query'] = $dateQuery;
		}

		return $query_args;
	}

	public function getFrom(): ?\DateTimeInterface
	{
		return $this->from;
	}

	public function getTo(): ?\DateTimeInterface
	{
		return $this->to;
	}

	public function getNum(): int
	{
		return $this->num;
	}

	public function getPage(): int
	{
		return $this->page;
	}

}
