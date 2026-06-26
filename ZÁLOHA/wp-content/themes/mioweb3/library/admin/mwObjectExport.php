<?php declare(strict_types=1);

namespace Mioweb\Admin;

class mwObjectExport
{

	private string $content;

	private string $fileExtension;

	private ?string $attachmentFileName;

	public function __construct(string $content, string $fileExtension, string $attachmentFileName = null)
	{
		$this->content = $content;
		$this->fileExtension = $fileExtension;
		$this->attachmentFileName = $attachmentFileName;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function getFileExtension(): string
	{
		return $this->fileExtension;
	}

	public function getAttachmentFileName(): ?string
	{
		return $this->attachmentFileName;
	}

	public function toArray(): array
	{
		return [
			'content' => $this->getContent(),
			'fileExtension' => $this->getFileExtension(),
			'attachmentFileName' => $this->getAttachmentFileName(),
		];
	}

}
