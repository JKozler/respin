<?php

class MwsNumberGenerator
{

	private $_id;

	private $_prefix;

	private $_padding;

	private $_initNumber;

	private $_existsCallback;

	public function __construct(string $id, string $prefix, int $padding, int $initNumber, callable $existCallback)
	{
		$this->_id = $id;
		$this->_prefix = $prefix;
		$this->_padding = $padding;
		$this->_initNumber = $initNumber;
		$this->_existsCallback = $existCallback;
	}

	public function next(bool $reserve = false, ?DateTimeImmutable $time = null): string
	{
		$prefix = $this->_prefix;
		if ($prefix) {
			$now = $time ?? new DateTimeImmutable();
			$prefix = preg_replace('/RRRR|YYYY/', $now->format('Y'), $prefix);
			$prefix = preg_replace('/RR|YY/', $now->format('y'), $prefix);
			$prefix = preg_replace('/MM/', $now->format('m'), $prefix);
		}

		$key = join('_', [
			$this->_id,
			$prefix,
			$this->_padding,
			$this->_initNumber,
		]);

		$nextNumber = (int) get_option(MWS_OPTION . '_number_generator_next_' . $key) ?: $this->_initNumber;

		// if are previous deleted
		if ($nextNumber > $this->_initNumber) {
			do {
				$nextNumber--;
				$result = $prefix . str_pad($nextNumber, $this->_padding, 0, STR_PAD_LEFT);
			} while (!call_user_func($this->_existsCallback, $result) && $nextNumber > $this->_initNumber);
		}

		// find next free number
		do {
			$result = $prefix . str_pad($nextNumber, $this->_padding, 0, STR_PAD_LEFT);
			$nextNumber++;
		} while (call_user_func($this->_existsCallback, $result));

		if ($reserve) {
			update_option(MWS_OPTION . '_number_generator_next_' . $key, $nextNumber);
		}

		return $result;
	}

}
