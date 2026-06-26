<?php declare(strict_types=1);

namespace Mioweb\Shop;

class AddressValidator
{
	public const OK = 0;
	public const MISSING_WHITESPACE = 1;
	public const MISSING_HOUSE_NUMBER = 2;

	/**
	 * Validate form input for address line with house number.
	 *
	 * @param string $addressLine
	 * @return int 0 tells whether validation is ok,
	 * 1 tells the house number is not separated by whitespace,
	 * 2 tells the house number is not present.
	 * All numbers are represented by named constants.
	 */
	public static function validateAddressStreet(string $addressLine): int
	{
		$numberRegex = '/^.*\d.*$/';
		$streetRegex = '/^\D{4,}/';
		$streetNumberRegex = '/^.*\s.*\d.*$/';

		//if the address line has a number
		if (preg_match($numberRegex, $addressLine)) {
			//if address line has a street and the number is not separated by space
			if (preg_match($streetRegex, $addressLine) && !preg_match($streetNumberRegex, $addressLine)) {
				return self::MISSING_WHITESPACE;
			}

			return self::OK; //street is not present or is separated by space, validation ok
		}

		return self::MISSING_HOUSE_NUMBER;
	}

	/**
	 * Validate form input for country and ZIP code compatibility.
	 *
	 * @param string $country
	 * @param string $zipCode
	 * @return bool Bool value of true means the validation is ok.
	 */
	public static function validateCountryAndZipCode(string $country, string $zipCode): bool
	{
		$countryRegex = [
			'CZ' => '/[1-7]/',
			'SK' => '/[890]/',
		];

		$regex = $countryRegex[$country] ?? null;

		return $regex === null || preg_match($regex, $zipCode[0]);
	}
}
