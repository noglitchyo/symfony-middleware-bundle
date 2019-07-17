# symfony-middleware-bundle

Introduces PSR-15 middleware support into Symfony framework.

![PHP from Packagist](https://img.shields.io/packagist/php-v/noglitchyo/symfony-middleware-bundle.svg)
[![Build Status](https://travis-ci.org/noglitchyo/symfony-middleware-bundle.svg?branch=master)](https://travis-ci.org/noglitchyo/symfony-middleware-bundle)
[![codecov](https://codecov.io/gh/noglitchyo/symfony-middleware-bundle/branch/master/graph/badge.svg)](https://codecov.io/gh/noglitchyo/symfony-middleware-bundle)
![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/noglitchyo/symfony-middleware-bundle.svg)
![Packagist](https://img.shields.io/packagist/l/noglitchyo/symfony-middleware-bundle.svg)

#### Roadmap

- Full test coverage
- Documentation

This bundle is under heavy development and is not meant to be used in production. 
Feel free to contribute!

### Description

Symfony Middleware Bundle provides support for PSR-15 middleware execution in Symfony application. 
It introduces a middleware dispatcher in a non-intrusive way, so it can be used with a minimal and simple configuration.

This bundle attempts to offer two implementations, so it can be used in different ways:

- through an event listener plugged on the events dispatched by the HttpKernel.
It will execute the middleware collection before passing the request to the controller if no response were created by the middlewares.

- as a decorator of the HttpKernel with the MiddlewareStackKernel.

### Getting started

#### Requirements

- Symfony 4
- PHP 7.3

#### 1) Install the package

`composer require noglitchyo/symfony-middleware-bundle`

#### 2) Create a middleware collection

First, it is required to define a middleware collection. The middleware collection will, as states its name, contains all
the middleware to execute. 

For this purpose, this bundle rely on the middleware collection implementation from 
[noglitchyo/middleware-collection-request-handler](https://github.com/noglitchyo/middleware-collection-request-handler)
which gives the ability to execute a collection of middlewares as if it was a single PSR-15 middleware or request handler with different execution strategies.

##### Example

`services.yaml`
```yaml 

  # Let's define some middlewares that we want to use.
  App\Middleware\SecurityMiddleware: ~
  App\Middleware\CorsMiddleware: ~
  App\Middleware\RouterMiddleware: ~
  App\Middleware\EncoderMiddleware: ~

  middleware_collections.default:
    class: NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\MiddlewareCollectionInterface 
        # Define the middleware collection class which MUST implement MiddlewareCollectionInterface.
        # Some default implemementations are provided:
        # NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\ArrayStackMiddlewareCollection
        # NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\SplQueueMiddlewareCollection
        # NoGlitchYo\MiddlewareCollectionRequestHandler\Collection\SplStackMiddlewareCollection
    arguments:
      - ['@App\Middleware\EncoderMiddleware', '@App\Middleware\RouterMiddleware:', '@App\Middleware\EncoderMiddleware']
    tags: ['middlewares.collection']
```

#### 3) Define a middleware handler

Handler gives the instruction to execute the attached middleware collection. 
Once, it is defined, the collection of middlewares will be executed for every incoming requests.

It is possible to define some conditions on when the handler should be run:
- Route Path matching : if the route path of the request match the given route path 
- Route Name matching : if the route name of the request match the given route name

##### Example

`middlewares.yaml`

```yaml
middlewares:
  handlers:
    default: # Name of your handler configuration
      collection: default.middleware_collection # should be the collection class name / service name defined in services.yaml
      filter: # Filter is optional, if not provided the collection will be executed for every requests
        routePath: '/path/test/1'
        routeName: 'myCustomRouteName'
        controller: 'App\Controller\SomeController' # Should be the class name
```

#### Tests

Would like to the run the test suite? Go ahead:

`composer test`

### References

https://www.php-fig.org/psr/psr-15/

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
