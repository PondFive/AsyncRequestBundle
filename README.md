AsyncRequestBundle
==================

[![Pond5 Async Request Bundle](https://github.com/PondFive/AsyncRequestBundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/PondFive/AsyncRequestBundle/actions/workflows/ci.yaml)

This bundle allows sending requests to a [Symfony Messenger](https://symfony.com/doc/current/messenger.html) transport to be handled later by a consumer.

Installation
------------

1. Add config file (no recipe yet)
```yaml
# config/packages/pond5_async_request.yaml
pond5_async_request:
    #header: X-Request-Async # user defined header name to indicate asynchronous request - X-Request-Async used by default
    transport: async-request # messenger transport name, ignored if messenger routing for Pond5\AsyncRequestBundle\Message\AsyncRequestNotification is configured manually

# can be omitted when using transport configured in another file (e.g. messenger.yaml)
framework:
    messenger:
        transports:
            async-request: '%env(MESSENGER_TRANSPORT_DSN)%'
```

2. Run composer
```bash
composer require pond5/async-request-bundle:dev-main
```
If you are not using Flex, you need to enable the bundle manually:

```php
// config/bundles.php
Pond5\AsyncRequestBundle\Pond5AsyncRequestBundle::class => ['all' => true],
```

Usage
-----

1. Add `X-Request-Async` header a `DELETE, PATCH, POST, PUT` request, e.g.
```bash
curl -i -X POST http://example.org/endpoint -H "X-Request-Async: 1"
```
Symfony should respond with `202` status code and empty body:
```
HTTP/1.1 202 Accepted
Content-Length: 0
```

2. Handle the request/consume the message
```bash
bin/console messenger:consume
```

Test
----

1. Install dev dependencies.
```
composer install
```
2. Run unit tests.
```
bin/phpunit
```
