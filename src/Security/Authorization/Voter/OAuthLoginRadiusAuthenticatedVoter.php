<?php

namespace Hola\OAuth2\Security\Authorization\Voter;

use Hola\OAuth2\Client\Provider\Exception\LoginRadiusProviderException;
use Hola\OAuth2\Security\User\OauthUserInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 * @SuppressWarnings(PHPMD)
 */
class OAuthLoginRadiusAuthenticatedVoter extends AuthenticatedVoter
{
    protected $authTrustResolver;
    protected $userProvider;

    public function __construct(
        AuthenticationTrustResolverInterface $authTrustResolver,
        ClientRegistry $clientRegistry
    ) {
        $this->authTrustResolver = $authTrustResolver;
        $this->userProvider = $clientRegistry->getClient('loginradius_oauth')->getOAuth2Provider();
    }

    /**
     * @param TokenInterface $token
     * @param $subject
     * @param array $attributes
     * @return mixed
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if ($this->checkHasToVote($token, $subject)) {
            $user = $token->getUser();
            try {
                $this->checkUserToken($user, $token);
                $this->userProvider->validateAccessToken($user->getAccessToken());
            } catch (LoginRadiusProviderException $e) {
                $result = VoterInterface::ACCESS_DENIED;
            } catch (\Exception $e) {
                $result = VoterInterface::ACCESS_DENIED;
            }
        }

        return $result;
    }

    /**
     * @param TokenInterface $token
     * @param $subject
     * @return bool
     */
    public function checkHasToVote(TokenInterface $token, $subject)
    {
        $existsSubject = isset($subject);
        $supportsSubject = $this->supports($subject);
        $isUser = $token && $token->getUser() && $token->getUser() instanceof OauthUserInterface;

        return (bool) ($existsSubject && $supportsSubject && $isUser);
    }

    public function supports($subject)
    {
        if ($subject instanceof Request) {
            return $subject->attributes->get('_route') !== 'connect_loginradius_check';
        }
    }

    /**
     * @param OauthUserInterface $user
     * @param TokenInterface $token
     * @return bool
     * @throws \Exception
     */
    public function checkUserToken(OauthUserInterface $user, TokenInterface $token)
    {
        if (!$user || !$token) {
            throw new \Exception('Invalid token');
        }

        return true;
    }
}
