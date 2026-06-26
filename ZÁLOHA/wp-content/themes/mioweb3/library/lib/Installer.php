<?php declare(strict_types=1);

namespace Mioweb\Lib;

class Installer
{

	public static function installUpdates(): void
	{
		$versions = get_option('cms_versions');

		if (isset($versions['cms']) && $versions['cms'] != CMS_VERSION) {
			if (version_compare($versions['cms'], '1.0', '<')) {
				self::delete_nette_cache();
			}

			$versions['cms'] = CMS_VERSION;
			update_option('cms_versions', $versions);
		}
	}

	private static function delete_nette_cache(): void
	{
		$cacheDir = get_temp_dir() . 'cache';

		if (!file_exists($cacheDir) || !is_dir($cacheDir)) {
			return;
		}

		self::delete_directory($cacheDir);
	}

	private static function delete_directory(string $dir): void
	{
		$files = scandir($dir);

		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$filePath = $dir . '/' . $file;
				if (is_dir($filePath)) {
					self::delete_directory($filePath);
				} else {
					@unlink($filePath);
				}
			}
		}
	}

}
