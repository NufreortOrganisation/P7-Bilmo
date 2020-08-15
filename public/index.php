<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$request = Request::createFromGlobals();

if ($request->server->get('APP_DEBUG')) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $request->server->get('TRUSTED_PROXIES') ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $request->server->get('TRUSTED_HOSTS') ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

if ($request->server->get('APP_ENV') && $request->server->get('APP_DEBUG')) {
  $kernel = new Kernel($request->server->get('APP_ENV'), (bool) $request->server->get('APP_DEBUG'));
  $request = Request::createFromGlobals();
  $response = $kernel->handle($request);
  $response->send();
  $kernel->terminate($request, $response);
}
