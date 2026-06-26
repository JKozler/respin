<?php declare(strict_types=1);

namespace Mioweb\Tus;

use TusPhp\Cache\FileStore;

class Tus
{

	const API_PATH = '';

	private static $instance = null;

	/** @var TusServer */
	private $server;

	/** @var Authorizator */
	private $authorizator;

	/** @var string */
	private $uploadsDir;

	/** @var string */
	private $cacheDir;

	private function __construct(string $tmpDir)
	{
		require_once __DIR__ . '/../../vendor/autoload.php';
		require_once __DIR__ . '/TusServer.php';
		require_once __DIR__ . '/Request.php';
		require_once __DIR__ . '/Authorizator.php';
		require_once __DIR__ . '/FileTypeChecker.php';

		$this->uploadsDir = $tmpDir . '/uploads';
		$this->cacheDir = $tmpDir . '/cache';

		$this->server = new TusServer(); // Either redis, file or apcu. Leave empty for file based cache.)
		$this->server->setApiPath(self::API_PATH);

		if (!file_exists($this->cacheDir)) {
			@mkdir($this->cacheDir, 0777, true);
		}

		$cache = $this->server->getCache();
		if ($cache instanceof FileStore) {
			$cache->setCacheDir($this->cacheDir);
		}

		if (!file_exists($this->uploadsDir)) {
			@mkdir($this->uploadsDir, 0777, true);
		}
		$this->server->setUploadDir($this->uploadsDir);

		$this->authorizator = new Authorizator();
		$this->server->middleware()->add($this->authorizator);

		$this->server->middleware()->add(FileTypeChecker::class);
	}

	public function deleteFile(string $filename)
	{
		$cache = $this->server->getCache();
		$cacheKeys = $cache->keys();

		foreach ($cacheKeys as $key) {
			$fileMeta = $cache->get($key, true);

			if ($fileMeta['name'] !== $filename) {
				continue;
			}

			if (!$cache->delete($key)) {
				continue;
			}

			if (is_writable($fileMeta['file_path'])) {
				unlink($fileMeta['file_path']);
			}
		}
	}

	public function initInput(
		string $id,
		string $name = null,
		array $allowedFileTypes = [],
		string $submitSelector = null,
		bool $isRequired = false,
		bool $processDirectly = true,
		bool $autoProceed = true,
		int $maxNumberOfFiles = 1
	): string
	{
		if (version_compare(phpversion(), '7.2', '<')) {
			return '<div class="cms_info_box">' . __('Pro použití této funkce je nutné na vašem hostingu aktualizovat PHP na verzi 7.2 a novější.', 'cms_ve') . '</div>';
		}

		$token = $this->authorizator->access();

		$name ??= $id;
		$allowedFileTypes = $allowedFileTypes ? '["' . implode('", "', $allowedFileTypes) . '"]' : 'null';
		$params = [
			'"' . $id . '"',
			'"' . $token . '"',
			$allowedFileTypes,
			$submitSelector ? '"' . $submitSelector . '"' : 'null',
			$isRequired ? 'true' : 'false',
			$processDirectly ? 'true' : 'false',
			$autoProceed ? 'true' : 'false',
			$maxNumberOfFiles,
		];

		return '
<input type="hidden" id="' . $id . '" name="' . $name . '" />
<script>jQuery(document).ready(function($) { mw_init_uppy(' . implode(', ', $params) . '); });</script>
';
	}

	public function getServer(): TusServer
	{
		return $this->server;
	}

	public function getAuthorizator(): Authorizator
	{
		return $this->authorizator;
	}

	public function getUploadsDir(): string
	{
		return $this->uploadsDir;
	}

	public function getCacheDir(): string
	{
		return $this->cacheDir;
	}

	public static function getInstance(string $tmpDir): Tus
	{
		if (!self::$instance) {
			self::$instance = new self($tmpDir);
		}

		return self::$instance;
	}

}
