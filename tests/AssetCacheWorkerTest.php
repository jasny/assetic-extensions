<?php

namespace Jasny\Assetic;

use Jasny\Assetic\AssetCacheWorker;
use Assetic\Cache\CacheInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollectionInterface;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers Jasny\Assetic\AssetCacheWorker
 */
class AssetCacheWorkerTest extends TestCase
{
    /**
     * @var CacheInterface|MockObject 
     */
    protected $cache;
    
    /**
     * @var AssetFactory|MockObject
     */
    protected $factory;
    
    /**
     * @var AssetCacheWorker
     */
    protected $worker;
    
    public function setUp()
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->factory = $this->createMock(AssetFactory::class);
        
        $this->worker = new AssetCacheWorker($this->cache);
    }
    
    public function testProcess()
    {
        $asset = $this->createMock(AssetInterface::class);
        
        $assetCache = $this->worker->process($asset, $this->factory);
        
        $this->assertInstanceOf(AssetCache::class, $assetCache);
        $this->assertAttributeSame($asset, 'asset', $assetCache);
        $this->assertAttributeSame($this->cache, 'cache', $assetCache);
    }
    
    public function testProcessWithAssetCollection()
    {
        $asset = $this->createMock(AssetCollectionInterface::class);
        
        $assetCache = $this->worker->process($asset, $this->factory);
        $this->assertSame($asset, $assetCache);
    }    
    
    public function testProcessWithAssetCache()
    {
        $asset = $this->createMock(AssetCache::class);
        
        $assetCache = $this->worker->process($asset, $this->factory);
        $this->assertSame($asset, $assetCache);
    }    
}
