# LoginRadius Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/v/release/developer-hola/oauth2-loginradius.svg?style=flat-square)](https://github.com/developer-hola/oauth2-loginradius/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/developer-hola/oauth2-loginradius/master.svg?style=flat-square)](https://travis-ci.org/developer-hola/oauth2-loginradius)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/developer-hola/oauth2-loginradius.svg?style=flat-square)](https://scrutinizer-ci.com/g/developer-hola/oauth2-loginradius/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/developer-hola/oauth2-loginradius.svg?style=flat-square)](https://scrutinizer-ci.com/g/developer-hola/oauth2-loginradius)
[![Total Downloads](https://img.shields.io/packagist/dt/developer-hola/oauth2-loginradius.svg?style=flat-square)](https://packagist.org/packages/developer-hola/oauth2-loginradius)

This package provides LoginRadius OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer requirehola/oauth2-loginradius
```

## Usage

Activate the bundle in the bundles.php file

```
Hola\OAuth2\HolaOAuth2LoginRadiusBundle::class => ['all' => true]
```

Create a knpu_oauth2_client.yaml file inside config/packages directory like this:
```yaml
# config/packages/knpu_oauth2_client.yaml
knpu_oauth2_client:
    clients:
        # will create service: "knpu.oauth2.client.foo_bar_oauth"
        # an instance of: KnpU\OAuth2ClientBundle\Client\OAuth2Client
        loginradius_oauth:
            type: generic
            provider_class: Hola\OAuth2\Client\Provider\LoginRadiusProvider

            # optional: a class that extends OAuth2Client
            #client_class: Hola\OAuth2\Client\LoginRadiusClient

            # optional: if your provider has custom constructor options
            # provider_options: {}

            # now, all the normal options!
            client_id: '%env(LOGINRADIUS_API_KEY)%'
            client_secret: '%env(LOGINRADIUS_API_SECRET)%'
            redirect_route: connect_loginradius_check
            redirect_params: {}

```

Define your firewall in the config/packages/security.yaml file:
```yaml
security:
    ...
    firewalls:
        main:
            provider: users  #your custom provider
            anonymous: ~
            logout:
                path:   /logout
                target: /

                handlers: [hola.oauth2.loginradius.logout.handler]  
            guard:
                authenticators:
                    - hola.oauth2.loginradius.authenticator
                entry_point: hola.oauth2.loginradius.authenticator

         
```
Add this interfaces to your User entity:

```php
class User implements UserInterface, \Serializable , OauthUserInterface
```
The OauthUserInterface allows the system to save the AccessToken of the user to check the this token is valid in each request.

Create a controller with to routes: **connect_loginradius_start** and **connect_loginradius_check** :

```php
class LoginRadiusController extends Controller
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/loginradius", name="connect_loginradius_start")
     */
    public function connectAction(ClientRegistry $clientRegistry, Request $request)
    {
        return $clientRegistry
            ->getClient('loginradius_oauth') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
	    	'profile','&action=login&regSource=cabecera&new=1' // the scopes you want to access
            ])
        ;
	}


    /**
     * @Route("/connect/loginradius/check", name="connect_loginradius_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {

        $client = $clientRegistry->getClient('loginradius_oauth');
        try {
            $user = $client->fetchUser();


            $accessToken = $client->getAccessToken();
            //Login the user saving the accesstoken and redirect to the original url

            //$this->userService->userLogin($user,$accessToken, $request);

            return new RedirectResponse(
                '/myoriginalurl',
                // might be the site, where users choose their oauth provider
                Response::HTTP_TEMPORARY_REDIRECT
            );

            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            var_dump($e->getMessage()); die;
        }



    }
}
```


## LoginRadius AccessToken check

If you use Oauth2 in various systems, you don't want if you logout of one system your session in the others continues.

To solve this problem we implement an AuthenticationVoter that takes your session accessToken and validates. If the token is invalid you need to login again.

To activate this voter put in the config/packages/security.yaml

```yaml
security:
    access_denied_url: connect_loginradius_start
    access_decision_manager:
         strategy: unanimous
         allow_if_all_abstain: false
```

## LoginRadius Session Logout

When you logout on your system we need to invalidate the LoginRadius accesstoken, for this reason you'll need to define our LogoutHandler under your firewall in the security.yaml file. If you don't define it, you will not logout from LoginRadius.
```yaml
security:
    firewalls:
        main:
            provider: users
            logout:
                path:   /logout
                target: /

                handlers: [hola.oauth2.loginradius.logout.handler]
            guard:
                authenticators:
                    - hola.oauth2.loginradius.authenticator
                entry_point: hola.oauth2.loginradius.authenticator

```

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/oauth2-github/blob/master/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-github/blob/master/LICENSE) for more information.
