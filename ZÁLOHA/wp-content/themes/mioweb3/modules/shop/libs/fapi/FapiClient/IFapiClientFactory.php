<?php

namespace MwShop\FapiClient;


interface IFapiClientFactory
{

	/**
	 * @param string $username
	 * @param string $password
	 * @return IFapiClient
	 */
	function createFapiClient($username, $password);
}
