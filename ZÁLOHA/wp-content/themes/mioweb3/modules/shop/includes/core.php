<?php
/**
 * Exception classes
 *
 * Date: 08.02.16
 * Time: 16:21
 *
 * @since 1.0.0
 */


/** Basic MioShop exception. */
class MwsException extends Exception
{

}

/** Exception with a message presentable to user. It is localised. */
class MwsUserException extends MwsException
{
}

/**
 * @TODO remove this magic ride to hell
 * Rendering helper class. Stores current instances that should be rendered using template files.
 */
class MwsCurrent
{

	/** @var MwsProduct */
	private $_product;

	/** @var MwsCartItem */
	private $_cartItem;

	private $_showAvailabilityInAdded = false;

	public function getProduct(): ?MwsProduct
	{
		return $this->_product;
	}

	public function setProduct(?MwsProduct $product): void
	{
		$this->_product = $product;
	}

	public function getCartItem(): ?MwsCartItem
	{
		return $this->_cartItem;
	}

	public function setCartItem(?MwsCartItem $cartItem): void
	{
		$this->_cartItem = $cartItem;
	}

	// @TODO remove this
	public function setShowAvailabilityInAdded(bool $showAvailabilityInAdded): void
	{
		$this->_showAvailabilityInAdded = $showAvailabilityInAdded;
	}

	// @TODO remove this
	public function showAvailabilityInAdded(): bool
	{
		return $this->_showAvailabilityInAdded;
	}

}
