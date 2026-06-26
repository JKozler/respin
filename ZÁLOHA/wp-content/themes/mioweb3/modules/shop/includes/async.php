<?php

/**
 * Asynchronous execution of events. Events are fired in thread of user request and postponed to asynchronous execution.
 * User: kuba
 * Date: 30.03.16
 * Time: 18:27
 */

/**
 * Fires an event asynchronously as custom AJAX request. Request is fired immediately when {@link dispatch()} method is
 * called.
 */
class MwsAsyncNow extends WP_Async_Request
{

	protected $action = 'mws_async_now';

	protected function handle()
	{
		mwshoplog(__METHOD__, MWLL_DEBUG, 'async');
		MwsAsyncExecutor::perform($_POST);
	}
}

/** Fires an event asynchronously as custom AJAX request. Request is fired on WP shutdown. */
class MwsAsyncLater extends WP_Async_Task
{

	protected $action = 'mws_async_later';

	private $tmpData = null;

	protected function prepare_data($data)
	{
		//    mwdbg(__METHOD__);
		return $this->tmpData === null ? $this->tmpData : $data;
	}

	protected function run_action()
	{
		mwshoplog(__METHOD__, MWLL_DEBUG, 'async');
		MwsAsyncExecutor::perform($_POST);
	}

	public function data($data)
	{
		$this->tmpData = $data;
		$this->launch();
		//    return $this;
	}

	public function launch_on_shutdown()
	{
		//Check if really fire the action
		$oper = $this->_body_data['operation'] ?? '';
		if ($oper === 'syncAll') {
			$syncNeeded = MWS()->gateways()->syncNeeded();
			if (!$syncNeeded) {
				return;
			}
		}
		parent::launch_on_shutdown();
	}

}

/** Centralizes asynchronous/postponed execution. */
class MwsAsyncExecutor
{

	public static function perform($data)
	{
		$sleep = isset($data['sleep']) ? absint($data['sleep']) : 0;
		if ($sleep >= 1) {
			sleep($sleep);
		}
		mwshoplog(json_encode($data, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG, 'async');
		$op = $data['operation'] ?? '';
		if ($op === 'syncAll') {
			MWS()->gateways()->synchronizeAll();
		}
	}
}
