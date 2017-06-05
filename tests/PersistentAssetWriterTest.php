<?php

namespace Jasny\Assetic;

use Jasny\Assetic\PersistentAssetWriter;
use Assetic\Asset\AssetInterface;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers Jasny\Assetic\PersistentAssetWriter
 */
class PersistentAssetWriterTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    protected $root;
    
    /**
     * @var AssetInterface|MockObject
     */
    protected $asset;
    
    public function setUp()
    {
        $this->root = vfsStream::setup();
        
        $this->asset = $this->createConfiguredMock(AssetInterface::class, [
            'getTargetPath' => 'all.css',
            'getVars' => [],
            'getValues' => [],
            'dump' => 'body { color: #333; }'
        ]);
    }
    
    public function testWriteAsset()
    {
        $writer = new PersistentAssetWriter(vfsStream::url('root'));
        
        $writer->writeAsset($this->asset);
        
        $this->assertTrue($this->root->hasChild('all.css'), 'all.css exists');
        $this->assertEquals('body { color: #333; }', $this->root->getChild('all.css')->getContent());
    }
    
    public function testWriteAssetDontOverwrite()
    {
        vfsStream::create(['all.css' => 'body { color: #000; }']);
        
        $writer = new PersistentAssetWriter(vfsStream::url('root'), [], false);
        
        $writer->writeAsset($this->asset);
        
        $this->assertTrue($this->root->hasChild('all.css'), 'all.css exists');
        $this->assertEquals('body { color: #000; }', $this->root->getChild('all.css')->getContent());
    }
    
    public function testWriteAssetDoOverwrite()
    {
        vfsStream::create(['all.css' => 'body { color: #000; }']);
        
        $writer = new PersistentAssetWriter(vfsStream::url('root'), [], true);
        
        $writer->writeAsset($this->asset);
        
        $this->assertTrue($this->root->hasChild('all.css'), 'all.css exists');
        $this->assertEquals('body { color: #333; }', $this->root->getChild('all.css')->getContent());
    }
    
    public function testWriteAssetSetValues()
    {
        $writer = new PersistentAssetWriter(vfsStream::url('root'), ['var' => ['foo', 'bar']]);
        
        $asset = $this->createMock(AssetInterface::class);
        $asset->expects($this->atLeastOnce())->method('getTargetPath')->willReturn('all.css');
        $asset->expects($this->atLeastOnce())->method('getVars')->willReturn(['var']);
        $asset->expects($this->atLeastOnce())->method('getValues')->willReturn(['foo' => 'bar', 'zoo' => 'ape']);
        $asset->expects($this->atLeastOnce())->method('dump')->willReturn('body { color: #333; }');
        
        $asset->expects($this->exactly(2))->method('setValues')
            ->withConsecutive([['var' => 'foo']], [['var' => 'bar']]);

        $writer->writeAsset($asset);
    }
}
