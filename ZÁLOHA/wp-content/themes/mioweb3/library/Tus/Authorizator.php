<?php declare(strict_types=1);

namespace Mioweb\Tus;

use Mioweb\Tus\Exceptions\AuthorizationException;
use TusPhp\Middleware\TusMiddleware;
use TusPhp\Request;
use TusPhp\Response;

class Authorizator implements TusMiddleware
{

	const SESSION_TOKEN = 'tus_token';

	/** @var string */
	private $token;

	public function __construct()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		$this->token = $this->generateToken();
	}

	public function handle(Request $request, Response $response)
	{
		$token = $_SESSION[self::SESSION_TOKEN];
		if (!$token) {
			$response->createOnly(false);
			$response->send('Not authorized.', 401);

			throw new AuthorizationException('No token in session.');
		}

		if ($request->header('tus-auth-token') !== $token) {
			$response->createOnly(false);
			$response->send('Not authorized.', 401);

			throw new AuthorizationException('Token not match.');
		}
	}

	public function access(): string
	{
		$_SESSION[Authorizator::SESSION_TOKEN] = $this->token;

		return $this->token;
	}

	public function revokeAccess()
	{
		unset($_SESSION[self::SESSION_TOKEN]);
	}

	private function generateToken(): string
	{
		return 'tus' . base64_encode(random_bytes(64));
	}

}
