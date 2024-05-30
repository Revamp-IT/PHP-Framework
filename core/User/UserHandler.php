<?php

namespace Revamp\Core\User;

use Revamp\Core\Cookie\CookieInterface;
use Revamp\Core\JsonError\JsonErrorInterface;
use Revamp\Core\ConfigManager\ConfigManagerInterface;
use Revamp\Core\DataMapHandler\DataMapHandlerInterface;
use Revamp\Core\Token\TokenInterface;

final class UserHandler implements UserHandlerInterface
{
    public final function __construct(
        private DataMapHandlerInterface $dmh,
        private ConfigManagerInterface $config,
        private TokenInterface $token,
        private CookieInterface $cookie,
        private JsonErrorInterface $error
    ) {}

    public final function register(string $login, string $password): void
    {
        $password = $password . $this->config->get("SECRET");

        $res = $this->dmh->setMap(User::class)->insert([
            'login' => $login,
            'password' => password_hash(password: $password, algo: PASSWORD_DEFAULT),
        ]);

        if (!$res) $this->error->throw(U3);

        $id = $this->dmh->setMap(User::class)->getBy(['login' => $login], false)['id'];

        $accessToken = $this->token->generateAccessToken(['id' => $id]);
        $refreshToken = $this->token->generateRefreshToken($accessToken);

        $this->cookie->set('Access-Token', $accessToken);
        $this->cookie->set('Refresh-Token', $refreshToken);
    }

    public final function authorize(string $login, string $password): void
    {
        $password = $password . $this->config->get("SECRET");

        $res = $this->dmh->setMap(User::class)->getBy(['login' => $login], false);

        if (!$res) {
            $this->error->throw(U1);
        }

        if (!password_verify($password, $res['password'])) $this->error->throw(U2);

        $accessToken = $this->token->generateAccessToken(['id' => $res['id']]);
        $refreshToken = $this->token->generateRefreshToken($accessToken);

        $this->cookie->set('Access-Token', $accessToken);
        $this->cookie->set('Refresh-Token', $refreshToken);
    }
}