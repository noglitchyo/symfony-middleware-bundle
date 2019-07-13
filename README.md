# symfony-middleware-bundle

Introduce PSR-15 middleware dispatcher into Symfony.

This bundle is under heavy development. Feel free to contribute!

For v0.1, min requirements are:

[x] Define basic behavior

[] Add a compiler pass for registering tagged collections per collection name

[] Ability to configure handler on route, handler, plug on all..

[] Add unit tests

#### Step 1 : Define your middleware collections
Define your middleware collections using services definition.

`services.yaml`
```yaml 
  default.middleware_collection:
    class: ~ # Define the middleware collection class (should implement MiddlewareCollectionInterface)
    arguments:
      - ['@middleware1', '@middleware2', '...']
    tags: ['middlewares.collection']
```

#### Step 2 : Define middleware handlers configuration

`middlewares.yaml`

```yaml
middlewares:
  handlers:
    default:
      filter:
        controller: ~ # Should be the class name
      collection: # should be the collection class name / service name defined in services.yaml
```
