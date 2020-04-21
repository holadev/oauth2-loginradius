<?php

namespace Hola\OAuth2\Security\Handler;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 * @SuppressWarnings(PHPMD)
 */
class OAuthLoginRadiusLogoutHandler implements LogoutHandlerInterface
{
    protected $userProvider;

    /**
     * OAuthLoginRadiusLogoutHandler constructor.
     * @param ClientRegistry $clientRegistry
     */
    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->userProvider = $clientRegistry->getClient('loginradius_oauth')->getOAuth2Provider();
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     * @return mixed
     */
    public function logout(
        Request $request,
        Response $response,
        TokenInterface $token
    ) {
        $isMultiSession = $this->checkTokenInSession($token);
        $accessToken = $token->getUser()->getAccessToken();

        if ($isMultiSession) {
            $accessToken = $token->getAttribute("oauthToken")->getToken();
        }

        return $this->userProvider->logout($accessToken);
    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    public function checkTokenInSession($token)
    {
        return (bool) !empty($token->getAttribute("oauthToken"));
    }
}
