# Jasny Assetic extensions

Improved caching for [Assetic](https://github.com/kriswallsmith/assetic).

## Installation

Jasny's Twig Extensions can be easily installed using [composer](http://getcomposer.org/)

    composer require jasny/twig-extensions

## Internal caching when using a factory

The `AssetCacheWorker` wraps each asset in a asset in an `AssetCache` object. This enables the use of asset caching
when using a factory.

```php
<?php

use Assetic\Factory\AssetFactory;
use Jasny\Assetic\AssetCacheWorker;

$factory = new AssetFactory('/path/to/asset/directory/');
$factory->setAssetManager($am);
$factory->setFilterManager($fm);

$factory->addWorker(new AssetCacheWorker(
    new FilesystemCache('/path/to/cache')
));

```

## Caching when using Twig

With the example code from the Assetic readme, each template is parsed on each request. This considerably slows down
your application. The `TwigCachingFormulaLoader` using Twig cache to store the assetic formulae is finds in each
template. The formula loader uses the [`cache` and `auto_reload` options](https://twig.sensiolabs.org/doc/2.x/api.html#environment-options)
of the Twig environment.

The `PersistentAssetWriter` is an asset writer with an `overwrite` option. When overwrite is disabled, existing assets
are not overwritten. This can speed up your production environment. It's recommended to add a version number in the
output files, either manually or by using the `AssetVersionWorker`.

## Versioning assets

The `AssetVersionWorker` add a version number to each generated assets. This works well on a production environment,
preventing the need of removing, checking or overwriting the asset files.

```
<?php

use Assetic\Factory\AssetFactory;
use Jasny\Assetic\AssetVersionWorker;

$factory = new AssetFactory('/path/to/asset/directory/');
$factory->setAssetManager($am);
$factory->setFilterManager($fm);

$factory->addWorker(new AssetVersionWorker($version));
```

