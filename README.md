# Jasny Assetic extensions

[![Build Status](https://travis-ci.org/jasny/assetic-extensions.svg?branch=master)](https://travis-ci.org/jasny/assetic-extensions)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/assetic-extensions/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/assetic-extensions/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/assetic-extensions/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/assetic-extensions/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a1a1745c-1272-46a3-9567-7bbb52acda5a/mini.png)](https://insight.sensiolabs.com/projects/a1a1745c-1272-46a3-9567-7bbb52acda5a)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/assetic-extensions.svg)](https://packagist.org/packages/jasny/assetic-extensions)
[![Packagist License](https://img.shields.io/packagist/l/jasny/assetic-extensions.svg)](https://packagist.org/packages/jasny/assetic-extensions)

Improved caching for [Assetic](https://github.com/kriswallsmith/assetic).

## Installation

Jasny's Twig Extensions can be easily installed using [composer](http://getcomposer.org/)

    composer require jasny/assetic-extensions

## Internal caching when using a factory

The `AssetCacheWorker` wraps each asset in a asset in an `AssetCache` object. This enables the use of asset caching
when using a factory.

```php
use Assetic\Factory\AssetFactory;
use Jasny\Assetic\AssetCacheWorker;

$factory = new AssetFactory('/path/to/asset/directory/');
$factory->setAssetManager($am);
$factory->setFilterManager($fm);

$factory->addWorker(new AssetCacheWorker(
    new FilesystemCache('/path/to/cache')
));

```

## Versioning assets

The `AssetVersionWorker` add a version number to each generated assets. This works well on a production environment,
preventing the need of removing, checking or overwriting the asset files.

If the output file is set to `all.css` and version is set to `1.3.7`, the output file will be named `all-1.3.7.css`.

```php
use Assetic\Factory\AssetFactory;
use Jasny\Assetic\AssetVersionWorker;

$factory = new AssetFactory('/path/to/asset/directory/');
$factory->setAssetManager($am);
$factory->setFilterManager($fm);

$factory->addWorker(new AssetVersionWorker($version));
```

## Caching when using Twig

With the example code from the Assetic readme, each template is parsed on each request. This considerably slows down
your application. The `TwigCachingFormulaLoader` using Twig cache to store the assetic formulae is finds in each
template. The formula loader uses the [`cache` and `auto_reload` options](https://twig.sensiolabs.org/doc/2.x/api.html#environment-options)
of the Twig environment.

The `PersistentAssetWriter` is an asset writer with an `overwrite` option. When overwrite is disabled, existing assets
are not overwritten. This can speed up your production environment. It's recommended to add a version number in the
output files, either manually or by using the `AssetVersionWorker`.

```php
use Jasny\Assetic\PersistentAssetWriter;
use Jasny\Assetic\TwigCachingFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Assetic\Factory\LazyAssetManager;

$twigLoader = new Twig_Loader_Filesystem('/path/to/views');
$twig = new Twig_Environment($twigLoader, ['cache' => '/path/to/cache', 'auto_reload' => true]);

$am = new LazyAssetManager($factory);

// enable loading assets from twig templates, caching the formulae
$am->setLoader('twig', new TwigCachingFormulaLoader($twig));

// loop through all your templates
foreach ($templates as $template) {
    $resource = new TwigResource($twigLoader, $template);
    $am->addResource($resource, 'twig');
}

$writer = new PersistentAssetWriter('/path/to/web');
$writer->writeManagerAssets($am);
```

