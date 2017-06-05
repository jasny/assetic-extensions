<?php

namespace Jasny\Assetic;

use Jasny\Assetic\AssetVersionWorker;
use Assetic\Factory\AssetFactory;
use Assetic\Asset\AssetInterface;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers Jasny\Assetic\AssetVersionWorker
 */
class AssetVersionWorkerTest extends TestCase
{
    /**
     * @var AssetFactory|MockObject
     */
    protected $factory;
    
    /**
     * @var AssetVersionWorker
     */
    protected $worker;
    
    public function setUp()
    {
        $this->factory = $this->createMock(AssetFactory::class);
        $this->worker = new AssetVersionWorker('1.3.7');
    }
    
    public function targetProvider()
    {
        return [
            ['all.css', 'all-1.3.7.css'],
            ['all.foo.css', 'all.foo-1.3.7.css'],
            ['all.css.gz', 'all-1.3.7.css.gz']
        ];
    }
    
    /**
     * @dataProvider targetProvider
     * 
     * @param string $unversionedPath
     * @param string $versionedPath
     */
    public function testProcess($unversionedPath, $versionedPath)
    {
        $asset = $this->createMock(AssetInterface::class);
        
        $asset->expects($this->atLeastOnce())->method('getTargetPath')->willReturn($unversionedPath);
        $asset->expects($this->once())->method('setTargetPath')->with($versionedPath);
        
        $ret = $this->worker->process($asset, $this->factory);
        $this->assertNull($ret);
    }
    
    public function testProcessWithoutVersion()
    {
        $this->worker = new AssetVersionWorker(null);
        
        $asset = $this->createMock(AssetInterface::class);
        
        $asset->expects($this->any())->method('getTargetPath')->willReturn('all.css');
        $asset->expects($this->never())->method('setTargetPath');
        
        $ret = $this->worker->process($asset, $this->factory);
        $this->assertNull($ret);
    }
}
