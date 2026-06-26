<?php

interface MwsCustomer
{

	public function getEmail(): string;

	public function getDetailUrl(): ?string;

	public function getEditUrl(): ?string;

}
