<?php
namespace Mioweb\VisualEditor\Lib;

final class Colors
{

	public static function getColorContrast($color, $for_transparent = true)
	{
		if ($color == 'transparent' || $color === '') {
			return $for_transparent ? 500 : 1;
		}
		if (strpos($color, 'rgb') !== false) {
			$regex = '#\((([^()]+|(?R))*)\)#';
			if (preg_match_all($regex, str_replace(' ', '', $color), $matches)) {
				$rgba = explode(',', implode(' ', $matches[1]));
			}
			$r = $rgba[0];
			$g = $rgba[1];
			$b = $rgba[2];
		} else {
			if (strlen($color) === 4) {
				$color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
			}
			$r = hexdec(substr($color, 1, 2));
			$g = hexdec(substr($color, 3, 2));
			$b = hexdec(substr($color, 5, 2));
		}

		$contrast = sqrt(
			($r * $r * 0.299) +
			($g * $g * 0.587) +
			($b * $b * 0.114)
		);

		return $contrast;
	}

	public static function isLightColor($color, $for_transparent = true)
	{
		return self::getColorContrast($color, $for_transparent) > 200;
	}

	public static function hex2rgba($hex, $transparency)
	{
		$hex = str_replace('#', '', $hex);
		if ($hex) {
			if (strlen($hex) == 3) {
				$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
				$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
				$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
			} else {
				$r = hexdec(substr($hex, 0, 2));
				$g = hexdec(substr($hex, 2, 2));
				$b = hexdec(substr($hex, 4, 2));
			}

			return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $transparency . ')';
		} else {
			return '';
		}
	}

	public static function rgba2hex($string)
	{
		$rgba = [];
		$hex = '';
		$regex = '#\((([^()]+|(?R))*)\)#';
		$rgba = preg_match_all($regex, $string, $matches) ? explode(',', implode(' ', $matches[1])) : explode(',', $string);
		echo ' r=' . $rgba['0'];
		echo ' g=' . $rgba['1'];
		echo ' b=' . $rgba['2'];
		$rr = dechex($rgba['0']);
		$gg = dechex($rgba['1']);
		$bb = dechex($rgba['2']);
		$aa = '';

		if (array_key_exists('3', $rgba)) {
			$aa = dechex($rgba['3'] * 255);
		}

		return strtoupper("#$rr$gg$bb");
	}

	public static function shiftColor($color, $coef = 0.8, $torgba = false)
	{
		if (!preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $parts)) {
			return '';
		}

		$out = '';
		for ($i = 1; $i <= 3; $i++) {
			$parts[$i] = hexdec($parts[$i]);
			$parts[$i] = round($parts[$i] * $coef);
			if ($parts[$i] > 255) {
				$parts[$i] = 255;
			}
			$out .= str_pad(dechex($parts[$i]), 2, '0', STR_PAD_LEFT);
		}

		if ($torgba) {
			return self::hextorgba('#' . $out, 1);
		}

		return '#' . $out;
	}
}
