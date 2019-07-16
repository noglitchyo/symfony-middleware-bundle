# symfony-middleware-bundle

Introduce PSR-15 middleware dispatcher into Symfony.

This bundle is under heavy development and is not meant to be used in production. 
Feel free to contribute!

#### Roadmap

- Full test coverage
- Improve the filter implementation so it can be extended
- Documentation

### Getting started

#### Requirements

- PHP 7.3

#### Step 1 - Define a middleware collection
Define a middleware collection using the service definition system.

`services.yaml`
```yaml 
  default.middleware_collection:
    class: ~ # Define the middleware collection class (MUST implement MiddlewareCollectionInterface)
    arguments:
      - ['@middleware1', '@middleware2', '...']
    tags: ['middlewares.collection']
```

#### Step 2 : Define a middleware handler configuration

A middleware handler configuration defines when a middleware collection can be executed.
Basic filtering features are implemented. It is actually possible to filter on the following:
- Route Path 
- Route Name
- Invoked controller

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