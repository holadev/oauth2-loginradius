<?php
namespace Hola\OAuth2\Client\Provider;

use Hola\OAuth2\Client\Provider\Exception\LoginRadiusProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use UnexpectedValueException;

/**
 * Auto-generated Migration: Please modify to your needs!
 * @SuppressWarnings(PHPMD)
 */
class LoginRadiusProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public $domain = 'https://cloud-api.loginradius.com';

    public $apiDomain = 'https://api.loginradius.com';

    public function getBaseAuthorizationUrl()
    {
        return $this->domain.'/sso/oauth/redirect';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->domain.'/sso/oauth/access_token';
    }

    public function getBaseAccessTokenValidateUrl()
    {
        return $this->apiDomain.'/identity/v2/auth/access_token/Validate';
    }

    public function getBaseRevokeTokenUrl()
    {
        return $this->apiDomain.'/identity/v2/auth/access_token/invalidate';
    }

    protected function getRevokeAccessTokenMethod()
    {
        return self::METHOD_GET;
    }

    public function validateAccessToken($accessToken)
    {
        $request = $this->getAuthenticatedRequest(
            self::METHOD_GET,
            $this->getBaseAccessTokenValidateUrl(),
            $accessToken
        );

        $response = $this->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }
        return;
    }

    public function logout($accessToken)
    {
        return $this->getRevokeAccessToken(['access_token' => $accessToken]);
    }
    public function getRevokeAccessToken(array $options = [])
    {
        $params = [
            'apiKey'     => $this->clientId,
            'access_token'  => $options['access_token'],
            'preventRefresh'  => "true"
        ];
        $request  = $this->getRevokeAccessTokenRequest($params);

        $response = $this->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }
        return;
    }

    protected function getRevokeAccessTokenRequest(array $params)
    {
        $method  = $this->getRevokeAccessTokenMethod();
        $url     = $this->getBaseRevokeTokenUrl($params);

        return $this->getRequest($method, $url.'?'.$this->buildQueryString($params), []);
    }
    protected function getRevokeAccessTokenUrl(array $params)
    {
        $url = $this->getBaseRevokeTokenUrl($params);

        if ($this->getAccessTokenMethod() === self::METHOD_POST) {
            $query = $this->getAccessTokenQuery($params);
            return $this->appendQuery($url, $query);
        }

        return $url;
    }

    public function getAuthorizationUrl(array $options = [])
    {

        $url = parent:: getAuthorizationUrl($options);
        return $url;
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->apiDomain .'/identity/v2/auth/account';
    }

    public function getAuthenticatedRequest($method, $url, $token, array $options = [])
    {

        return $this->createRequest($method, $url.'?apiKey='.$this->clientId, $token, $options);
    }

    protected function getAuthorizationHeaders($token = null)
    {
        return ['Authorization'=> 'Bearer '.$token];
    }
    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['profile'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw LoginRadiusProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw LoginRadiusProviderException::oauthException($response, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new LoginRadiusResourceOwner($response);
        return $user->setDomain($this->domain);
    }
}
