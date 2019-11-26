<?php

namespace Hola\OAuth2\Security\User;

interface OauthUserInterface
{
    public function getAccessToken();

    public function setAccessToken($accessToken);
}
