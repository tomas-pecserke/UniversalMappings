Getting Started With PecserkeUniversalMappings
==============================================

This component allows you to have one mapping definition for use with any mapping (ORM, MongoDB ODM, CouchDB ODM).
It allows writing just one model and letting user of the bundle decide, which mapping he/she will use.

## Installation

Add PecserkeUniversalMappings in your composer.json:

``` js
{
    "require": {
        "pecserke/universal-mappings": "1.0@dev"
    }
}
```

Now tell composer to download the component by running the command:

``` bash
$ php composer.phar update pecserke/universal-mappings
```

Composer will install the component into your project's `vendor/pecserke` directory.

## Using universal mappings

Create a Symfony2 bundle and extend class
`Pecserke\Component\UniversalMappings\HttpKernel\Bundle\UniversalMappingsBundle`
with your Bundle class:



Enable the bundle in the kernel:

``` php
<?php
namespace Acme\Bundle\DemoBundle;

use Pecserke\Component\UniversalMappings\HttpKernel\Bundle\UniversalMappingsBundle;

class AcmeDemoBundle extends UniversalMappingsBundle
{
}
```
