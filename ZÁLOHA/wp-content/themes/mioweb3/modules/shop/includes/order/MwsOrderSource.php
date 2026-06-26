<?php declare(strict_types=1);

class MwsOrderSource implements JsonSerializable
{

	/** @var string */
	private $type;

	/** @var int|null */
	private $pageId;

	/** @var string|null */
	private $url;

	/** @var int */
	private $formId;

	public function __construct(string $type, ?int $pageId = null, ?string $url = null, ?int $formId = null)
	{
		$this->type = $type;
		$this->pageId = $pageId;
		$this->url = $url;
		$this->formId = $formId;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): void
	{
		if (!MwsOrderSourceType::isValidValue($type)) {
			throw new MwsException(sprintf(
				'Order source type "%s" is not valid. Choose one of: [%s]',
				$type,
				implode(',', MwsOrderSourceType::getAll())
			));
		}

		$this->type = $type;
	}

	public function getPageId(): ?int
	{
		return $this->pageId;
	}

	public function setPageId(?int $pageId): void
	{
		$this->pageId = $pageId;
	}

	public function getUrl(): ?string
	{
		return $this->url;
	}

	public function setUrl(?string $url): void
	{
		$this->url = $url;
	}

	public function getFormId(): ?int
	{
		return $this->formId;
	}

	public function setFormId(?int $formId): void
	{
		$this->formId = $formId;
	}

	/** @return mixed[] */
	public function toArray(): array
	{
		return [
			'type' => $this->getType(),
			'pageId' => $this->getPageId(),
			'url' => $this->getUrl(),
			'formId' => $this->getFormId(),
		];
	}

	public function jsonSerialize(): mixed
	{
		return json_encode($this->toArray());
	}

	/** @param mixed[] $array */
	public static function fromArray(array $array): self
	{
		return new self($array['type'], $array['pageId'] ?? null, $array['url'] ?? null, $array['formId'] ?? null);
	}

	public static function getSelect($args = [], $val = '', $class = ''): string
	{
		$options = [];
		$options[] = [
			'value' => '',
			'name' => __('Vše', 'cms'),
		];

		if (MWS()->isCreated()) {
			$options[] = [
				'value' => '0',
				'name' => __('Eshop', 'cms'),
			];
		}

		$forms = MwsForm::getAll([], false);
		foreach ($forms as $form) {
			$options[] = [
				'value' => $form->getId(),
				'name' => __('Formulář', 'cms') . ': ' . $form->getName(),
			];
		}

		return mwAdminComponents::select([
			'name' => $args['name'] ?? '',
			'options' => $options,
		], $val, $class);
	}

}
