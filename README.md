AsyncRequestBundle
==================

This bundle allows sending requests to a Symfony Messenger transport to be handled later by a consumer.

Installation
------------

1. Run composer
```bash
composer require pond5/async-request-bundle:dev-master --no-scripts
```
If you are not using Flex, you need to enable the bundle manually:
```php
// config/bundles.php
Pond5\AsyncRequestBundle\AsyncRequestBundle::class => ['all' => true],
```
2. Add config file
```yaml
async_request:
#    header: X-Request-Async # user defined header name to indicate asynchronous request - X-Request-Async used by default
    transport: async-request # messenger transport name, ignored if messenger routing for Pond5\AsyncRequestBundle\Message\AsyncRequestNotification is configured manually

# can be omitted when using transport configured in another file (e.g. messenger.yaml)
framework:
    messenger:
        transports:
            async-request: '%env(MESSENGER_TRANSPORT_DSN)%'
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

2. Handle the request/consumer the message
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
