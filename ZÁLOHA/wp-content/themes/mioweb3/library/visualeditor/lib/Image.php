<?php

namespace Mioweb\VisualEditor\Lib;

use mwFrontComponents;
use function trigger_error;
use const E_USER_DEPRECATED;

class Image
{

	private ?int $_id;

	private string $_image;

	private string $_position;

	private ?string $_selectedSize;

	/** @param mixed[]|string|null $image */
	function __construct($image = [])
	{
		// backward compatibility
		if ($image === null || $image === '') {
			$image = [];
		}

		if (is_string($image)) {
			trigger_error(self::class . ' - Arg 1 - should be array, passing string is deprecated', E_USER_DEPRECATED);
			$image = ['image' => $image];
		}

		$this->_id = isset($image['imageid']) ? intval($image['imageid']) : null;
		if ($this->_id && !self::existImage($this->_id)) {
			$this->_id = null;
			$this->_image = '';
		} else {
			$this->_image = $image['image'] ?? '';
		}

		$this->_position = $image['position'] ?? '50% 50%';
		$this->_selectedSize = isset($image['selected_size']) && $image['selected_size'] ? $image['selected_size'] : null;
	}

	public function getId(): ?int
	{
		return $this->_id;
	}

	public function getPosition(): string
	{
		return $this->_position;
	}

	public function getImage(): string
	{
		return $this->_image;
	}

	public function getSelectedSize(): ?string
	{
		return $this->_selectedSize;
	}

	public function getSize(): string
	{
		if ($this->getSelectedSize() !== null) {
			return $this->getSelectedSize();
		}

		if ($this->_id) {
			if ($this->_image) {
				$sizes = get_intermediate_image_sizes();
				$sizes[] = 'full';
				foreach ($sizes as $size) {
					$sizeData = wp_get_attachment_image_src($this->_id, $size);
					if ($sizeData && $sizeData[0] === self::generateImageUrl($this->_image)) {
						return $size;
					}
				}
			} else {
				return 'full';
			}
		}

		if ($this->getImage()) {
			$path = $this->getPath();

			if ($path !== null) {
				$imageSize = wp_getimagesize($path);

				if ($imageSize) {
					$width = $imageSize[0];

					return self::getImageSizeByWidth($width);
				}
			}
		}

		return 'full';
	}

	public function getWidth(): ?int
	{
		if ($this->_id) {
			if ($this->getSelectedSize() !== null) {
				$sizeData = wp_get_attachment_image_src($this->_id, $this->getSelectedSize());
				if ($sizeData) {
					return $sizeData[1];
				}
			}

			if ($this->_image) {
				$sizes = get_intermediate_image_sizes();
				$sizes[] = 'full';
				foreach ($sizes as $size) {
					$sizeData = wp_get_attachment_image_src($this->_id, $size);
					if ($sizeData && $sizeData[0] === self::generateImageUrl($this->_image)) {
						return $sizeData[1];
					}
				}
			} else {
				$sizeData = wp_get_attachment_image_src($this->_id, 'full');
				if ($sizeData) {
					return $sizeData[1];
				}
			}
		}

		if ($this->getImage()) {
			$path = $this->getPath();

			if ($path !== null) {
				$imageSize = wp_getimagesize($path);

				if ($imageSize) {
					return $imageSize[0];
				}
			}
		}

		return null;
	}

	public function getUrl($size = null, $empty = false): string
	{
		if ($size !== null && $this->_id) {
			return wp_get_attachment_image_url($this->_id, $size);
		}

		if ($this->_image) {
			return self::generateImageUrl($this->_image);
		}

		if ($this->_id) {
			return wp_get_attachment_image_url($this->_id, 'full');
		}

		if ($empty) {
			return self::getEmptyImageUrl();
		}

		return '';
	}

	public function getPath($size = null): ?string
	{
		$path = null;
		if ($this->_id) {
			$imgPath = get_attached_file($this->_id);

			if ($size === null || $size === 'full') {
				$path = $imgPath;
			}

			$meta = wp_get_attachment_metadata($this->_id);

			if (isset($meta['sizes'][$size])) {
				$path = str_replace(wp_basename($imgPath), $meta['sizes'][$size]['file'], $imgPath);
			}
		}

		if ($this->_image) {
			if (str_starts_with($this->_image, 'http')) {
				$img = str_replace(site_url(), '', $this->_image);
				// if image is on bad domain
				if ($this->_image === $img) {
					return null;
				}
			} else {
				$img = $this->_image;
			}

			$path = str_replace('//', '/', ABSPATH . $img);
		}

		if ($path !== null) {
			$path = wp_normalize_path($path);
			if (file_exists($path)) {
				return $path;
			}
		}

		return null;
	}

	public static function generateImageUrl(string $image): string
	{
		return str_starts_with($image, 'http') ? $image : site_url() . $image;
	}

	public function getTitle(): string
	{
		if ($this->getId()) {
			return get_the_title($this->getId());
		}

		return '';
	}

	public function getExcerpt(): string
	{
		if ($this->getId()) {
			return get_the_excerpt($this->getId());
		}

		return '';
	}

	// @TODO replace with printImg()
	public function getImg($size = 'medium', $attrs = []): string
	{
		if ($this->_id) {
			return wp_get_attachment_image($this->_id, $size, false, $attrs);
		}

		return '';
	}

	public function getAttrImageSizes(float $colDivisor = 1, float $mobileColDivisor = 1, ?int $maxWidth = null, bool $edit_mode = false, bool $fullRow = false, float $ratio = 1): string
	{
		global $vePage;

		if ($fullRow) {
			$pageWidth = 100;
			$pageWidthUnit = '%';
		} else {
			$pageWidth = $vePage->display->getPageWidth();
			$pageWidthUnit = $vePage->display->getPageWidthUnit();
		}

		if ($edit_mode) {
			if ($pageWidthUnit === '%') {
				return '(' . $pageWidth . 'vw)';
			}

			return '(min-width: ' . $pageWidth . 'px) ' . $pageWidth . 'px, ' . ceil(90 / $mobileColDivisor) . 'vw';
		}

		// max width
		if ($maxWidth !== null && ($pageWidthUnit === '%' || $maxWidth < ceil($pageWidth / $colDivisor))) {
			return '(min-width: ' . ($maxWidth * $colDivisor) . 'px) ' . ceil($maxWidth * $ratio) . 'px, (min-width:767px) ' . ceil(100 / $colDivisor * $ratio) . 'vw, (min-width:' . $maxWidth . 'px) ' . ceil($maxWidth * $ratio) . 'px, ' . ceil(90 / $mobileColDivisor * $ratio) . 'vw';
		}

		// percentage width
		if ($pageWidthUnit === '%') {
			return '(min-width:767px) ' . ceil($pageWidth / $colDivisor * $ratio) . 'vw, ' . ceil(90 / $mobileColDivisor * $ratio) . 'vw';
		}

		return '(min-width: ' . $pageWidth . 'px) ' . ceil($pageWidth / $colDivisor * $ratio) . 'px, (min-width:767px) ' . ceil(100 / $colDivisor * $ratio) . 'vw, ' . ceil(90 / $mobileColDivisor * $ratio) . 'vw';
	}

	public static function getLayoutImageSize(float $colDivisor = 1, ?int $maxWidth = null, bool $edit_mode = false, $fullRow = false, int $ratio = 1): string
	{
		global $vePage;

		if ($edit_mode) {
			return 'full';
		}

		if (wp_is_mobile()) {
			return 'mio_columns_c1';
		}

		if ($fullRow) {
			$pageWidth = 100;
			$pageWidthUnit = '%';
		} else {
			$pageWidth = $vePage->display->getPageWidth();
			$pageWidthUnit = $vePage->display->getPageWidthUnit();
		}

		// max width
		if ($maxWidth !== null && ($pageWidthUnit === '%' || $maxWidth < ceil($pageWidth / $colDivisor))) {
			$imageWidth = $maxWidth;
		} elseif ($pageWidthUnit === '%') {
			// percentage width
			return 'full';
		} else {
			// fixed width
			$imageWidth = $pageWidth / $colDivisor;
		}

		return self::getImageSizeByWidth((int) $imageWidth * $ratio);
	}

	public function printImg(array $args = [], string $class = '', $edit_mode = false, $respectImageSize = false): string
	{
		if ($this->_id) {
			$size = $layoutSize = $args['size'] ?? 'full';

			if ($this->getExt() !== 'svg' && $this->getExt() !== 'gif') {
				$maxWidth = $args['max_width'] ?? null ?: null;
				$colDivisor = $args['col_divisor'] ?? 1;
				$mobileColDivisor = $args['mobile_col_divisor'] ?? 1;
				$fullRow = $args['full_row'] ?? false;
				$imageRatio = $args['image_ratio'] ?? '';

				if (!isset($args['size'])) {
					$size = $this->getSize();
				}

				if ($respectImageSize && $imageRatio === '') {
					$imageWidth = !isset($args['size']) ? $this->getWidth() : $this->getImageWidthBySize($size);

					if ($maxWidth === null || ($imageWidth !== null && $maxWidth > $imageWidth)) {
						$maxWidth = $imageWidth;
					}
				}
				$ratio = $this->calculateRatio($imageRatio);

				$args['attrs']['sizes'] = $edit_mode && $respectImageSize
					? ''
					: $this->getAttrImageSizes($colDivisor, $mobileColDivisor, $maxWidth, $edit_mode, $fullRow, $ratio);

				$layoutSize = self::getLayoutImageSize($colDivisor, $maxWidth, $edit_mode, $fullRow);

				if ($respectImageSize && $imageRatio === '') {
					$sizeWidth = $this->getImageWidthBySize($size);
					$layoutWidth = $this->getImageWidthBySize($layoutSize);

					if ($sizeWidth !== null && $layoutWidth !== null && $sizeWidth < $layoutWidth) {
						$layoutSize = $size;
					}
				}
			}

			if (isset($args['alt'])) {
				$args['attrs']['alt'] = $args['alt'];
			}
			$args['attrs']['class'] = $class;

			global $vePage;

			$args['attrs']['loading'] = $vePage->display->isLazyLoadingDisabled() ? false : $args['lazy_loading'] ?? 'lazy';

			return wp_get_attachment_image($this->_id, $layoutSize, false, $args['attrs'] ?? []);
		} else {
			global $vePage;

			return mwFrontComponents::image([
				'src' => $this->getUrl(),
				'alt' => $args['alt'] ?? '',
				'lazy_loading' => $vePage->display->isLazyLoadingDisabled() ? false : $args['lazy_loading'] ?? 'lazy',
				'empty_image_url' => $args['empty_image_url'] ?? '',
				'empty_image' => $args['empty_image'] ?? true,
			], $class);
		}
	}

	public function getExt(): string
	{
		return wp_check_filetype($this->getUrl())['ext'] ?? '';
	}

	function isEmpty(): bool
	{
		return !$this->_id && !$this->_image;
	}

	public static function getImageSizeByWidth(int $width): string
	{
		$sizes = wp_get_registered_image_subsizes();
		$currentSize = 10000;
		$sizeName = '';
		foreach ($sizes as $name => $size) {
			if ($size['width'] >= $width && $currentSize > $size['width'] && !$size['crop'] && $name !== 'medium') {
				$currentSize = $size['width'];
				$sizeName = $name;
			}
		}

		return $sizeName;
	}

	public static function getColDivisor(string $colType): int
	{
		$colDivisors = [
			'col-one' => 1,
			'col-two' => 2,
			'col-three' => 3,
			'col-four' => 4,
			'col-five' => 5,
			'col-six' => 6,
			'col-twofree' => 3 / 2,
			'col-twofour' => 2,
			'col-threefour' => 4 / 3,
			'col-threefive' => 5 / 3,
			'col-twofive' => 5 / 2,
			'col-fourfive' => 5 / 4,
		];

		return $colDivisors[$colType] ?? 1;
	}

	public function getImageWidthBySize(string $size): ?int
	{
		$original = wp_get_attachment_image_src($this->_id, $size);

		return $original[1] ?? 2000;
	}

	public function calculateRatio(string $imageRatio): float
	{
		if ($imageRatio === '23' || $imageRatio === '34' || $imageRatio === '11') {
			$original = wp_get_attachment_image_src($this->_id, 'full');

			if ($original && $original[1] > $original[2]) {
				if ($imageRatio === '23') {
					$unit = $original[2] / 3 * 2;
				} elseif ($imageRatio === '34') {
					$unit = $original[2] / 4 * 3;
				} else {
					$unit = $original[2];
				}

				return $original[1] / $unit;
			}
		}

		return 1;
	}

	public static function getEmptyImageUrl(): string
	{
		return MW_IMAGE_LIBRARY . 'misc/empty_image.jpg';
	}

	public static function createById(int $id): Image
	{
		return new self([
			'imageid' => $id,
		]);
	}

	public static function createByUrl(string $url): Image
	{
		return new self([
			'image' => $url,
		]);
	}

	public static function existImage(int $id): bool
	{
		return get_post($id) !== null;
	}

}
