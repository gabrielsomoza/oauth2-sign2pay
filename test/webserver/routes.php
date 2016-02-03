<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Somoza\OAuth2\Client\Provider\Sign2Pay;

/**
 * Index
 */
$app->get('/', function (Request $request, Response $response) {

    // this won't work until we have an access token in the session. See the middleware that checks for that.
    $response->getBody()->write('You have a valid token!');

    return $response;
});

/**
 * OAuth2 Callback
 */
$app->get('/callback', function (Request $request, Response $response, $args) {

    $params = $request->getQueryParams();
    $state = !empty($params['state']) ? $params['state'] : null;
    $code = !empty($params['code']) ? $params['code'] : null;
    if (!$code || !$state || ($state !== $_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
        $response->getBody()->write('Invalid state');
        return $response->withStatus(500);
    }

    /** @var Sign2Pay $provider */
    $provider = $this->oauth;

    // Try to get an access token using the authorization code grant.
    $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $params['code'],
        'response_type' => 'code',
        'state' => $_SESSION['oauth2state'],
    ]);

    // We have an access token, which we may use in authenticated
    // requests against the service provider's API.
    if ($accessToken) {
        $_SESSION['access_token'] = $accessToken->getToken()['token'];
    }

    return $response->withHeader('Location', '/');
});
