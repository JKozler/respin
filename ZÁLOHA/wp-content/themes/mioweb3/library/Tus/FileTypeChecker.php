<?php declare(strict_types=1);

namespace Mioweb\Tus;

use Mioweb\Tus\Exceptions\ForbiddenFileTypeException;
use TusPhp\Middleware\TusMiddleware;
use TusPhp\Request;
use TusPhp\Response;

class FileTypeChecker implements TusMiddleware
{

	const SAFE_MIME_TYPES = ['image', 'video', 'audio', 'text', 'font'];

	const SAFE_APPLICATION_MIME = [
		'application/x-bzip',
		'application/x-bzip2',
		'application/msword',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/gzip',
		'application/json',
		'application/vnd.oasis.opendocument.presentation',
		'application/vnd.oasis.opendocument.spreadsheet',
		'application/vnd.oasis.opendocument.text',
		'application/ogg',
		'application/pdf',
		'application/vnd.ms-powerpoint',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'application/xhtml+xml',
		'application/vnd.ms-excel',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/xml',
		'application/zip',
		'application/x-zip-compressed',
		'application/x-7z-compressed',
	];

	public function handle(Request $request, Response $response)
	{
		if ($request->method() === 'POST') {
			$fileType = $request->extractMeta('filetype');
			$parts = explode('/', $fileType);
			if ($parts && in_array($parts[0], self::SAFE_MIME_TYPES, true)) {
				return; // safe
			}

			if (!$fileType || !in_array($fileType, self::SAFE_APPLICATION_MIME, true)) {
				$response->createOnly(false);
				$response->send('Forbidden file type.', 400);

				throw new ForbiddenFileTypeException('Forbidden file type.');
			}
		}
	}
}
