<?php

namespace Hola\OAuth2\Security\Authenticator;

use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Auto-generated Migration: Please modify to your needs!
 * @SuppressWarnings(PHPMD)
 */
class LoginRadiusAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $router;

    public function __construct(ClientRegistry $clientRegistry, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->router = $router;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route')=== 'connect_loginradius_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getLoginRadiusClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $client = $this->clientRegistry->getClient('loginradius_oauth');

        $userData = $this->getLoginRadiusClient()->fetchUserFromToken($credentials);
        $user = new OAuthUser($userData->getEmail(),["ROLE_USER","ROLE_OAUTH_USER"]);
        return $user;
    }



    private function getLoginRadiusClient()
    {
        return $this->clientRegistry->getClient('loginradius_oauth');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            $this->router->generate('connect_loginradius_start'),
            // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
