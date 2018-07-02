# Logs Bundle

This project is a bundle to write logs in database and read logs with browser

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require norsys/logs-bundle "dev-master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:


```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Norsys\LogsBundle\NorsysLogsBundle(),
            // ...
        );
        // ...
    }
    // ...
}
```

Step 3: Configuration
----------------------

First of all, you need to configure the Doctrine DBAL connection to use in the handler. You have 2 ways to do that:

**By using an existing Doctrine connection:**

Note: we set the `logging` and `profiling` option to false to avoid DI circular reference.

```yaml
# app/config/config.yml

doctrine:
    dbal:
        connections:
            default:
                ...
            monolog:
                driver:    pdo_sqlite
                dbname:    monolog
                path:      %kernel.root_dir%/cache/monolog2.db
                charset:   UTF8
                logging:   false
                profiling: false

norsys_logs:
    doctrine:
        connection_name: monolog
```

**By creating a custom Doctrine connection for the bundle:**

```php
# app/config/config.yml

norsys_logs:
    doctrine:
        connection:
            driver:      pdo_sqlite
            driverClass: ~
            pdo:         ~
            dbname:      monolog
            host:        localhost
            port:        ~
            user:        root
            password:    ~
            charset:     UTF8
            path:        %kernel.root_dir%/db/monolog.db # The filesystem path to the database file for SQLite
            memory:      ~                               # True if the SQLite database should be in-memory (non-persistent)
            unix_socket: ~                               # The unix socket to use for MySQL
```

Please refer to the [Doctrine DBAL connection configuration](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#configuration) for more details.

Optionally you can override the schema table name (`monolog_entries` by default):

``` yaml
# app/config/config.yml
norsys_logs:
    doctrine:
        table_name: monolog_entries
```

Now your database is configured, you can generate the schema for your log entry table by running the following command:

```
./app/console norsys:logs:schema-create
# you should see as result:
# Created table monolog_entries for Doctrine Monolog connection
```

Then, you can configure Monolog to use the Doctrine DBAL handler:

```yaml
# app/config/config_prod.yml # or any env
monolog:
    handlers:
        main:
            type:         fingers_crossed # or buffer
            level:        error
            handler:      norsys_logs
        app:
            type:         buffer
            action_level: info
            channels:     app
            handler:      norsys_logs
        deprecation:
            type:         buffer
            action_level: warning
            channels:     deprecation
            handler:      norsys_logs
        norsys_logs:
            type:         service
            id:           norsys_logs.handler.doctrine_dbal
```

Now you have enabled and configured the handler, you migth want to display log entries, just import the routing file:

``` yaml
# app/config/routing.yml
norsys_logs:
    resource: "@NorsysLogsBundle/Resources/config/routing.xml"
    prefix:   /admin/monolog
```

Translations
------------

If you wish to use default translations provided in this bundle, make sure you have enabled the translator in your config:

``` yaml
# app/config/config.yml
framework:
    translator: ~
```

Overriding default layout
-------------------------

You can override the default layout of the bundle by using the `base_layout` option:

``` yaml
# app/config/config.yml
norsys_logs:
    base_layout: "NorsysLogsBundle::layout.html.twig"
```

or quite simply with the Symfony way by create a template on `app/Resources/NorsysLogsBundle/views/layout.html.twig`.


Using service tags to implement logger
--------------------------------------

This bundle comes up with a compiler pass helper, easing integration of the logger by using service tags when combined with LoggerAwareTrait.


First, use trait in your class:


```php
<?php
# src/AppBundle/Acme/Demo.php
namespace AppBundle\Acme;

// ...
use Norsys\LogsBundle\LoggerBehaviorTrait;

class Demo
{
    // ...
    use LoggerBehaviorTrait;
    // ...
}
```

Then update the class setup in your service container config:

```yaml
# app/config/services.yml
services:
    # ...
    app.acme.demo:
        class: AppBundle\Acme\Demo
        tags:
            - { name: logger.aware }
    # ...
```


That's it, now your logger is ready to use!:


```php
<?php
# src/AppBundle/Acme/Demo.php
namespace AppBundle\Acme;

// ...
use Norsys\LogsBundle\LoggerBehaviorTrait;

class Demo
{
    // ...
    use LoggerBehaviorTrait;
    // ...

    public function doSomething()
    {
        $this->getLogger()->debug('Method AppBundle\Acme::doSomething() was called');
        // ...
    }
}
```

## Credits
Developped with :heart: by [Norsys](https://www.norsys.fr/)

## License

This project is licensed under the [MIT license](LICENSE).