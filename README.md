TaskBundle
==========

Overview
========
The `TaskBundle` provides infrastructure for simple implementation of delayed tasks 

Requirements
============
* PHP 7.0 or greater
* doctrine/orm 2.5.*
* stof/doctrine-extensions-bundle 1.2.*
* symfony components 3.1 or greater

Installation
============
You can install the package using the [Composer](https://getcomposer.org/) package manager. You can install it by running this command in your project root:
```sh
composer require insidieux/task-bundle
```

Don't forget to make diff and run migrations, for creating tasks queue table

Usage
=====
First of all, you need to add some new namespaces to separate task processing:
```yml
task:
  debug: true
  namespaces:
    - 'namespace1'
    - 'namespace2'
```
After building container, you'll see predefined `worker` services:
```sh
task.worker.namespace1
task.worker.namespace2
```
You can run them via `cron`/`supervisor`. Also you can scale them by passing `worker-id` to command
```sh
$ bin/console task:worker:namespace --id 1
$ bin/console task:worker:namespace --id 2
```
Create php class extending `\TaskBundle\Handler\AbstractHandler` and implement `perform` method
Push created handler to queue
```php
$this->getContainer()->get('task.services.pusher')->push(new SomeHandler, 'namespace1');
```

License
=======
This bundle is released under the [MIT license](LICENSE)

Authors
=======
- [Ageev Pavel](mailto:ageev.pavel.v@gmail.com)
- Barakin Alexandr
