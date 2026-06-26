<?php

function mwMessages(): mwMessages
{
	return mwMessages::instance();
}

class mwMessages
{

	protected static $_instance = null;

	public $errors = [];

	public $success = 1;

	public $success_message;

	function __construct()
	{
		$this->success_message = __('Nastavení bylo uloženo.', 'cms_ve');
	}

	function error($msg, $target = '', $code = 0)
	{
		$this->errors[$code] = [
			'msg' => $msg,
			'code' => $code,
			'target' => $target,
		];
		$this->success = 0;
	}

	function success($msg = '')
	{
		$this->success = 1;
		if ($msg) {
			$this->success_message = $msg;
		}
	}

	function writeHtml($close = false)
	{
		$content = '';
		if ($this->success) {
			$content .= mwAdminComponents::messageBox($this->success_message, [
				'close' => $close,
			]);
		} else {
			foreach ($this->errors as $er) {
				$content .= mwAdminComponents::messageBox($er['msg'], [
					'type' => 'error',
					'close' => $close,
				]);
			}
		}

		return $content;
	}

	function returnArray()
	{
		return [
			'success' => $this->success,
			'errors' => $this->errors,
			'html' => $this->writeHtml(),
		];
	}

	public static function instance(): self
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}
