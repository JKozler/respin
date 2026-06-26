<?php declare(strict_types=1);

namespace Mioweb\Tus;

class TusServer extends \TusPhp\Tus\Server
{

	public function __construct($cacheAdapter = 'file')
	{
		parent::__construct($cacheAdapter);

		$this->request = new Request();
	}

	/** @inheritDoc */
	public function handleExpiration(): array
	{
		$deleted = parent::handleExpiration();

		$uploadsDir = tus()->getUploadsDir();
		$files = glob($uploadsDir . '/*');
		foreach ($files as $file) {
			$createdAt = filemtime($file);
			if ($createdAt === false) {
				continue;
			}

			$day = 86400; // in seconds

			if (time() - $createdAt >= $day && is_writable($file)) {
				unlink($file);
				$deleted[] = ['file_path' => $file];
			}
		}

		return $deleted;
	}

}
