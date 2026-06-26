<?php

use Mioweb\Shop\InvoiceContactSettings;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;

class MwsContact
{

	private $_email;

	private $_phone;

	private $_person;

	private $_company;

	private $_address;

	public function __construct(string $email, ?string $phone = null, ?MwsPerson $person = null, ?MwsCompany $company = null, ?MwsAddress $address = null)
	{
		$this->_email = $email;
		$this->_phone = $phone;
		$this->_person = $person;
		$this->_company = $company;
		$this->_address = $address;
	}

	public function getPhone(): ?string
	{
		return $this->_phone;
	}

	public function getEmail(): string
	{
		return $this->_email;
	}

	public function getPerson(): ?MwsPerson
	{
		return $this->_person;
	}

	public function setPerson(?MwsPerson $person): void
	{
		$this->_person = $person;
	}

	public function getCompany(): ?MwsCompany
	{
		return $this->_company;
	}

	public function setCompany(?MwsCompany $company): void
	{
		$this->_company = $company;
	}

	public function getAddress(): ?MwsAddress
	{
		return $this->_address;
	}

	public function setAddress(?MwsAddress $address): void
	{
		$this->_address = $address;
	}

	// WTF?
	public function getCustomer(): string
	{
		return $this->_company ? $this->_company->getName() : ($this->_person ? $this->_person->format() : ($this->_email ?: ''));
	}

	public function format(bool $toHtml = false, bool $showPhone = false): string
	{
		$phoneQuote = function (?string $value, ?string $name = null) use ($toHtml) {
			$value = trim($value);
			if (!$value) {
				return '';
			}

			if ($toHtml) {
				return '<div class="mws-phone">' . esc_html(($name ? $name . ': ' : '') . $value) . '</div>';
			}

			return ($name ? $name . ': ' : '') . $value . "\n";
		};

		$person = $this->getPerson();
		$company = $this->getCompany();
		$address = $this->getAddress();

		$result = '';
		if ($company) {
			$result .= $company->format($toHtml);
		}
		if ($person) {
			$result .= $person->format($toHtml);
		}
		if ($address) {
			$result .= $address->format($toHtml);
		}

		if ($showPhone) {
			$result .= $phoneQuote($this->getPhone(), __('Telefon', 'mwshop'));
		}

		return $result;
	}

	public function formatForDocument(InvoiceContactSettings $settings = null): string
	{
		$person = $this->getPerson();
		$company = $this->getCompany();
		$address = $this->getAddress();

		$result = '<table>';
		$result .= '<tr>';
		$result .= '<td class="mws-name">';
		if ($person) {
			$result .= $person->format(true);
		}

		if ($company) {
			$result .= '<div class="mws-company-name">' . $company->getName() . '</div>';
		}

		if ($address) {
			$result .= $address->format(true);
		}

		if ((bool) $this->getPhone() && ($settings?->showPhone() ?? true)) {
			$result .= '<div class="mws-phone">' . esc_html(__('Telefon', 'mwshop') . ': ' . $this->getPhone()) . '</div>';
		}

		if ((bool) $this->getEmail() && ($settings?->showEmail() ?? false)) {
			$result .= '<div class="mws-email">' . esc_html(__('E-mail', 'mwshop') . ': ' . $this->getEmail()) . '</div>';
		}

		$result .= '</td>';

		if ($company) {
			$result .= '<td class="mws-company">';
			$result .= $company->getId() ? '<div>' . esc_html(__('IČ', 'mwshop') . ': ' . $company->getId()) . '</div>' : '';
			$result .= $company->getTaxId() ? '<div>' . esc_html(__('DIČ', 'mwshop') . ': ' . $company->getTaxId()) . '</div>' : '';
			$isSK = $address->getCountry() === MwsCountry::SK;
			$result .= $isSK && $company->getVatId() ? '<div>' . esc_html(__('IČ DPH', 'mwshop') . ': ' . $company->getVatId()) . '</div>' : '';
			$result .= '</td>';
		}

		$result .= '</tr>';
		$result .= '</table>';

		return $result;
	}

	public function sendMail(string $subject, string $body, array $attachments = []): bool
	{
		$emailsSet = MWS()->getEmailSetting();

		if (isset($emailsSet['appearance_type']) && $emailsSet['appearance_type'] === 'graphic') {
			$logo = '';

			if ((isset($emailsSet['logo']['image']) && $emailsSet['logo']['image'])) {
				// BASE64
//				$logoPath = get_home_path() . $emailsSet['logo']['image'];
//				if (file_exists($logoPath)) {
//					$imgData = base64_encode(file_get_contents($logoPath));
//					$logoSrc = 'data: ' . mime_content_type($logoPath) . ';base64,' . $imgData;
//				}

				// Include external image
				$logoSrc = get_home_url() . $emailsSet['logo']['image'];
				$logo = '<img src="' . $logoSrc . '" alt="" width="' . $emailsSet['logo_width'] . '" style="height:auto;display:block;" />';
			}

			$body = '
				<!DOCTYPE html>
				<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
				<head>
				  <meta charset="UTF-8">
				  <meta name="viewport" content="width=device-width,initial-scale=1">
				  <meta name="x-apple-disable-message-reformatting">
				  <!--[if mso]>
				  <noscript>
				    <xml>
				      <o:OfficeDocumentSettings>
				        <o:PixelsPerInch>96</o:PixelsPerInch>
				      </o:OfficeDocumentSettings>
				    </xml>
				  </noscript>
				  <![endif]-->
				  <style>
				    table, td, div, h1, p {font-family: Arial, sans-serif;}
				  </style>
				</head>
				<body style="margin:0;padding:0;">
				  <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#eeeeee;">
				    <tr>
				      <td style="padding:10px;">
				      </td>
				    </tr>
				    <tr>
				      <td align="center" style="padding:0;">
				        <table role="presentation" style="width:602px;border-collapse:collapse;border:0;border-spacing:0;text-align:left;">
				          <tr>
				            <td align="center" style="padding:30px;background:' . ($emailsSet['header_color'] ?: '#eeeeee') . ';">
				              ' . $logo . '
				            </td>
				          </tr>
				          <tr>
				            <td style="padding:40px 40px 45px 40px; background:#ffffff">
				              ' . $body . '
				            </td>
				          </tr>
				          <tr>
				            <td style="padding:30px;">
				              <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
				                <tr>
				                  <td style="padding:0;width:50%;" align="center">
				                    <p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#959595;">' . get_home_url() . '</p>
				                  </td>
				                </tr>
				              </table>
				            </td>
				          </tr>
				        </table>
				      </td>
				    </tr>
				  </table>
				</body>
				</html>
			';
		} else {
			$body = '
				<!doctype html>
				<html>
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
					</head>
					<body>
						' . $body . '
					</body>
				</html>
			';
		}

		$senderEmail = MWS()->getSenderEmail() ?: get_option('admin_email');
		$senderName = MWS()->getSenderName() ?: get_bloginfo('name') ?: $senderEmail;

		try {
			miowebMailer()->send($this->getEmail(), $senderEmail, $senderName, $subject, $body, $senderEmail, $attachments);
		} catch (SendException $e) {
			return false;
		}

		return true;
	}

	public function toArray(): array
	{
		return [
			'email' => $this->getEmail(),
			'phone' => $this->getPhone(),
			'person' => ($person = $this->getPerson()) ? $person->toArray() : null,
			'company' => ($company = $this->getCompany()) ? $company->toArray() : null,
			'address' => ($address = $this->getAddress()) ? $address->toArray() : null,
		];
	}

	public static function createFromArray(array $values): self
	{
		return new self(
			$values['email'],
			$values['phone'],
			$values['person'] ? MwsPerson::createFromArray($values['person']) : null,
			$values['company'] ? MwsCompany::createFromArray($values['company']) : null,
			$values['address'] ? MwsAddress::createFromArray($values['address']) : null
		);
	}

}
