<?php

namespace Jasny\Assetic;

use Assetic\Factory\Worker\WorkerInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;

/**
 * Factory worker to add caching to each asset
 */
class AssetVersionWorker implements WorkerInterface
{
    /**
     * @var string
     */
    protected $version;
    
    /**
     * Class constructor
     * 
     * @param VersionInterface $version
     */
    public function __construct($version)
    {
        $this->version = $version;
    }
    
    /**
     * Process an asset
     * 
     * @param AssetInterface $asset
     * @param AssetFactory   $factory
     */
    public function process(AssetInterface $asset, AssetFactory $factory)
    {
        if (isset($this->version)) {
            $originalPath = $asset->getTargetPath();
            $path = preg_replace('/(\.\w+(?:\.gz)?)$/', '-' . $this->version . '$1', $originalPath);
            
            $asset->setTargetPath($path);
        }
    }
}
