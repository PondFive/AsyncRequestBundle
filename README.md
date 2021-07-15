AsyncRequestBundle
==================

[![Pond5 Async Request Bundle](https://github.com/PondFive/AsyncRequestBundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/PondFive/AsyncRequestBundle/actions/workflows/ci.yaml)
[![codecov](https://codecov.io/gh/PondFive/AsyncRequestBundle/branch/main/graph/badge.svg?token=HAAA5YZBT8)](https://codecov.io/gh/PondFive/AsyncRequestBundle)
[![Latest Stable Version](http://poser.pugx.org/pond5/async-request-bundle/v)](https://packagist.org/packages/pond5/async-request-bundle)
[![Total Downloads](http://poser.pugx.org/pond5/async-request-bundle/downloads)](https://packagist.org/packages/pond5/async-request-bundle)

This bundle allows sending requests to a [Symfony Messenger](https://symfony.com/doc/current/messenger.html) transport to be handled later by a consumer.

Installation
------------

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```shell
composer require pond5/async-request-bundle
```

### Applications that don't use Symfony Flex

1. Add config file
```yaml
# config/packages/pond5_async_request.yaml
pond5_async_request:
    #header: X-Request-Async # user defined header name to indicate asynchronous request - X-Request-Async used by default
    #methods: [DELETE, PATCH, POST, PUT] # HTTP methods that should support async requests
    transport: async-request # messenger transport name, ignored if messenger routing for Pond5\AsyncRequestBundle\Message\AsyncRequestNotification is configured manually

# can be omitted when using transport configured in another file (e.g. messenger.yaml)
framework:
    messenger:
        transports:
            async-request: '%env(MESSENGER_TRANSPORT_DSN)%'
```

2. Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:
```shell
composer require pond5/async-request-bundle
```

3. Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:
```php
// config/bundles.php

return [
    // ...
    Pond5\AsyncRequestBundle\Pond5AsyncRequestBundle::class => ['all' => true],
];
```

Usage
-----

1. Add `X-Request-Async` header a `DELETE, PATCH, POST, PUT` request, e.g.
```shell
curl -i -X POST http://example.org/endpoint -H "X-Request-Async: 1"
```
Symfony should respond with `202` status code and empty body:
```
HTTP/1.1 202 Accepted
Content-Length: 0
```

2. Handle the request/consume the message
```shell
bin/console messenger:consume
```

Test
----

1. Install dev dependencies.
```shell
composer install
```
2. Run unit tests.
```shell
bin/phpunit
```
