<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Somoza\OAuth2\Client\Provider\Exception\Sign2PayException;
use Somoza\OAuth2\Client\Provider\Sign2Pay;

/**
 * Sign2Pay error handler middleware
 */
$app->add(function(Request $request, Response $response, callable $next) {
    $params = $request->getQueryParams();
    if (!empty($params['error'])) {
        $description = !empty($params['error_description']) ? $params['error_description'] : $params['error'];
        throw new \Exception('An unexpected error occurred on Sign2Pay: ' . $description);
    }

    $response = $next($request, $response);

    return $response;
});

/**
 * Sign2Pay oauth redirect middleware
 * Will redirect all routes to Sign2Pay if access_token is not stored in session
 */
$app->add(function(Request $request, Response $response, callable $next) use ($app) {
    /** @var Sign2Pay $oauth */
    $oauth = $this->oauth;

    $existingToken = !empty($_SESSION['access_token']) ? $_SESSION['access_token'] : null;

    // if we already have a token, verify it
    if ($existingToken) {
        $valid = false;
        try {
            $valid = $oauth->checkAccessTokenValid($existingToken);
        } catch (Sign2PayException $e) {
            if ($e->getCode() === 403) {
                $valid = false;
            }
            $this->logger->warn('Could not verify existing access token.');
        }
        if (!$valid) {
            // the token probably expired, so authorize again
            $existingToken = false;
            unset($_SESSION['access_token']);
        }
    }

    // if we still have a valid token, or if the request points to the the oauth2 callback URL, then continue
    if ($existingToken|| $request->getUri()->getPath() == '/callback') {
        $response = $next($request, $response);
        return $response;
    }

    // .. otherwise, redirect the user to Sign2Pay so we can get a new token

    $authorizationUrl = $oauth->getAuthorizationUrl();

    // Get the state generated and store it to the session.
    $_SESSION['oauth2state'] = $oauth->getState();

    $this->logger->info('REDIRECT: ' . $authorizationUrl);

    return $response->withHeader('Location', $authorizationUrl);
});
