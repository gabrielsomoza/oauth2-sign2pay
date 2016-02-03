<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Somoza\OAuth2\Client\Provider\Sign2Pay;

$container = $app->getContainer();

$settings = $container->get('settings');
$options = $settings['oauth2'];

// == application logger
$container['logger'] = function (ContainerInterface $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new StreamHandler($settings['path'], Logger::DEBUG));
    return $logger;
};

// == Sign2Pay provider
// includes HandlerStack with logging
$stack = HandlerStack::create();
$stack->push(Middleware::log($container->get('logger'), new MessageFormatter(MessageFormatter::DEBUG)));

$client = new HttpClient(['handler' => $stack]);
$collaborators['httpClient'] = $client;

$provider = new Sign2Pay($options, $collaborators);
$container['oauth'] = $provider;
