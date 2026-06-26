<?php declare(strict_types=1);

namespace Mioweb\Shop;

use Mioweb\Shop\Exceptions\InvalidPacketSizeException;

class PacketSize
{

	/** @var float in cm */
	private float $length;

	/** @var float in cm */
	private float $width;

	/** @var float in cm */
	private float $height;

	/**
	 * @param float $length in cm
	 * @param float $width in cm
	 * @param float $height in cm
	 * @throws InvalidPacketSizeException
	 */
	public function __construct(float $length, float $width, float $height)
	{
		if ($length <= 0.0 || $width <= 0.0 || $height <= 0.0) {
			throw new InvalidPacketSizeException();
		}

		$this->length = $length;
		$this->width = $width;
		$this->height = $height;
	}

	/** @return float[] in cm */
	public function toArray(): array
	{
		return [
			'length' => $this->length,
			'width' => $this->width,
			'height' => $this->height,
		];
	}

	/** @return float[] in mm */
	public function toPacketaArray(): array
	{
		return [
			'length' => $this->length * 10,
			'width' => $this->width * 10,
			'height' => $this->height * 10,
		];
	}

	public function getLength(): float
	{
		return $this->length;
	}

	public function getWidth(): float
	{
		return $this->width;
	}

	public function getHeight(): float
	{
		return $this->height;
	}

	/** @param float[] $array */
	public static function fromArray(array $array): self
	{
		return new self($array['length'], $array['width'], $array['height']);
	}

}
