<?php

namespace Jasny\Assetic;

use Assetic\Factory\Worker\WorkerInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Asset\AssetCache;
use Assetic\Cache\CacheInterface;
use Assetic\Asset\AssetCollectionInterface;

/**
 * Factory worker to add caching to each asset
 */
class AssetCacheWorker implements WorkerInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;
    
    /**
     * Class constructor
     * 
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    
    /**
     * Process an asset
     * 
     * @param AssetInterface $asset
     * @param AssetFactory   $factory
     * @return AssetCache
     */
    public function process(AssetInterface $asset, AssetFactory $factory)
    {
        if ($asset instanceof AssetCollectionInterface || $asset instanceof AssetCache) {
            return $asset;
        }
        
        return new AssetCache($asset, $this->cache);
    }
}
