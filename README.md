# Labrador

[![Build Status](https://travis-ci.org/cspray/labrador.svg?branch=master)](https://travis-ci.org/cspray/labrador.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cspray/labrador/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
[![License](https://poser.pugx.org/cspray/labrador/license.png)](https://packagist.org/packages/cspray/labrador)

> This library is still in development and the provided APIs are likely to change in the short-term future.

A microframework wiring together high-quality libraries to route HTTP requests to specific controller objects.

## Dependencies

Labrador has a variety of dependencies that must be provided.

- PHP 5.5+
- [nikic/FastRoute](https://github.com/nikic/FastRoute) Router for mapping an HTTP Request to a handler
- [rdlowrey/Auryn](https://github.com/rdlowrey/Auryn) DI container to automatically provision dependencies
- [symfony/HttpFoundation](https://github.com/symfony/HttpFoundation) Provides abstraction over HTTP Request/Response and HttpKernelInterface
- [cspray/Configlet](https://github.com/cspray/Configlet) Object Oriented library for storing PHP configurations
- [zendframework/Component_ZendEscaper](https://github.com/zendframework/Component_ZendEscaper) Escaping library for simple, out-of-the-box template rendering

## Installation

We recommend you use Composer to install Labrador.

`require cspray/labrador dev-master`

```php
<?php

$me->assumesCompetentDeveloper();

if (!$you->usingComposer()) {
    $you->downloadLabrador();
    $you->downloadDependencies();
    $you->setupAutoloader();
}
```

## Getting Started

Getting started with Labrador is really simple whether you clone the repo directly or install via Composer. First we're gonna talk about getting the required directory structure setup after a Composer installation. If you are cloning the repo directly you can skip to the [server setup]() section as the out-of-the-box directory structure is a part of the repo.

### Directory Setup

#### Bare Bones

This is the **absolute bare minimum you'll need** to get Labrador properly executing your requests. This will not setup the LabradorGuide and requires you to properly wire up the Labrador\Application. Take a look at the repository's `/init.php` for an example of a working implementation.

```plain
/public             # your webroot, all publicly accessible files should be stored here (i.e. css, js, images)
    |_index.php     # should only require ../init.php
/init.php           # is where you wire up your application
```

#### Automated with Labrador Guide

Labrador provides a shell script to automatically setup the appropriate directory structure and wiring. Execute the following command in your terminal:

```plain
./vendor/bin/labrador-skeleton
```

This command will copy over the `/public/*`, `/config/*` and `/init.php` files from Labrador's root directory structure into the directory that the command was executed. Included with this is setting up the included LabradorGuide to be available. This is a built-in documentation that you can access through your browser and learn all kinds of intricate details on how Labrador works. This is also a safe command; if the file already exists the copy or creation will be aborted and your existing code stays intact.

### Server Setup

Labrador has been developed on a VM running Apache. In theory there's nothing that would require you to use Apache and any web server capable of running PHP 5.5 should suffice. But for now the only configurations that we provide examples for is Apache because we haven't confirmed other server's configurations work.

## Apache Configuration

Below is a slightly modified server configuration for the Apache server that we use in development.

```plain
<VirtualHost *:80>
    ServerName awesome.dev
    ServerAlias www.awesome.dev
    ServerSignature Off

    DocumentRoot "/public"
    FallbackResource /index.php
    DirectoryIndex index.php

    <Directory "/public">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride None
    </Directory>
</VirtualHost>
```

Load this into the appropriate `httpd.conf` file for your server and restart it.

## Application Setup

At this point you're ready to start developing your application. So, let's take a look at setting up your app's configuration, routes, and middleware. From this point we're going to assume that you have done a manual setup and need to setup `/init.php` from a clean slate. If you copied over Labrador's default files your init.php will look slightly different as various pieces are split into `/config` files.

```php
<?php

use Labrador\Application;
use Labrador\ConfigDirective;
use Labrador\Bootstrap\FrontControllerBootstrap;
use Labrador\Events\ApplicationHandleEvent;
use Labrador\Events\ApplicationFinishedEvent;
use Labrador\Events\RouteFoundEvent;
use Labrador\Events\ExceptionThrownEvent;
use Auryn\Injector;
use Configlet\Config;

require_once __DIR__ . '/vendor/autoload.php';

// Typically this function would be returned from /config/application.php
$appConfig = function(Config $config) {

    // Unless utilizing the Guide all of these configuration directives are optional

    /**
     * The environment that the current application is running in.
     *
     * This is a completely arbitrary string and only holds meaning to your application.
     */
    $config[ConfigDirective::ENVIRONMENT] = 'development';

    /**
     * The root directory for the application
     */
    $config[ConfigDirective::ROOT_DIR] = __DIR__;

    /**
     * A callback accepting a Auryn\Provider as the first argument and a Configlet\Config
     * as the second argument.
     *
     * It should perform actions that are needed at time of request startup including
     * wire up dependencies, set configuration values, and other tasks your application
     * might need to carry out before Labrador actually takes over handling the Request.
     */
    $config[ConfigDirective::BOOTSTRAP_CALLBACK] = function(Injector $injector, Config $config) {
        // do your application bootup stuff here if needed
    };

};

/** @var \Auryn\Injector $injector */
/** @var \Labrador\Application $app */
$injector = (new FrontControllerBootstrap($appConfig))->run();
$app = $injector->make(Application::class);

// perform some action when Application::handle is invoked
$app->onHandle(function(ApplicationHandleEvent $event) {
    // You can set a response to $event->setResponse() to short-circuit processing and return early
    // Your Application::onFinished middleware will still be executed if you short-circuit
});

// perform some action when we've successfully converted a found handler into a callable
$app->onRouteFound(function(RouteFoundEvent $event) {
    // You can wrap the callable that we resolved. A common use case might be allowing controllers to return strings
    $existing = $event->getController();
    $cb = function(Request $request) use($existing) {
        $response = $existing($request);
        if (!$response instanceof Response) {
            $response = new Response($response);
        }

        return $response;
    };
    $event->setController($cb);
});

// perform some action when we're through processing a Request
// unless a prior Application::onFinished middleware throws an exception
// this is guaranteed to be run on every request.
$app->onFinished(function(ApplicationFinishedEvent $event) {
    // For a cool example of Application::onFinished middleware check out
    // Labrador\Development\HtmlToolbar::appFinishedEvent
});

// perform some action when Application catches an exception
// if you pass Application::THROW_EXCEPTIONS this event will not be triggered
$app->onException(function(ExceptionThrownEvent $event) {

});



```


