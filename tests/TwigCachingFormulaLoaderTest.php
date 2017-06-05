<?php

namespace Jasny\Assetic;

use Jasny\Assetic\TwigCachingFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Assetic\Extension\Twig\AsseticNode;
use Assetic\Asset\AssetInterface;
use Twig_Environment;
use Twig_TokenStream;
use Twig_CacheInterface;
use Twig_Cache_Filesystem;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_Matcher_InvokedCount as InvokedCount;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Psr\Log\LoggerInterface;

/**
 * @covers Jasny\Assetic\TwigCachingFormulaLoader
 */
class TwigCachingFormulaLoaderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    protected $root;
    
    /**
     * @var Twig_Environment|MockObject
     */
    protected $twig;
    
    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;
    
    /**
     * @var TwigResource|MockObject
     */
    protected $resource;
    
    /**
     * @var array
     */
    protected $expectedFormulae = [
        '1234' => [
            'main.scss',
            'scss',
            [
                'output' => 'all.css',
                'name' => '1234',
                'debug' => false,
                'combine' => true,
                'vars' => []
            ]
        ]
    ];
    
    public function setUp()
    {
        $this->root = vfsStream::setup();
        
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->logger->expects($this->never())->method('error');
        
        $this->resource = $this->createMock(TwigResource::class);
        $this->resource->method('getContent')->willReturn('dummy content');
        $this->resource->method('__toString')->willReturn('base.html');
    }
    
    /**
     * @param InvokedCount                     $tokenize
     * @param Twig_CacheInterface|string|false $cache
     * @param boolean                          $autoReload
     */
    protected function initTwig(InvokedCount $tokenize, $cache, $autoReload)
    {
        $asset = $this->createConfiguredMock(AssetInterface::class, ['getTargetPath' => 'all.css']);
        
        $tokens = new Twig_TokenStream([]);
        
        $node = $this->createMock(AsseticNode::class);
        $node->method('getAttribute')->willReturnMap([
            ['name', '1234'],
            ['inputs', 'main.scss'],
            ['filters', 'scss'],
            ['asset', $asset],
            ['debug', false],
            ['combine', true],
            ['vars', []]
        ]);
        $node->method('getIterator')->willReturn(new \ArrayIterator([]));
        
        $this->twig = $this->createMock(Twig_Environment::class);
        
        $this->twig->expects($tokenize)->method('tokenize')->willReturn($tokens);
        $this->twig->expects($this->any())->method('parse')->with($this->identicalTo($tokens))->willReturn($node);
        
        $this->twig->expects($this->once())->method('getCache')->willReturn($cache);
        $this->twig->expects($this->once())->method('isAutoReload')->willReturn($autoReload);
    }
    
    
    public function testLoadWithoutCache()
    {
        $this->initTwig($this->once(), false, false);
        
        $loader = new TwigCachingFormulaLoader($this->twig, $this->logger);
        $this->assertNull($loader->getCache());
        
        $formulae = $loader->load($this->resource);
        $this->assertSame($this->expectedFormulae, $formulae);        
    }
    
    public function testLoadNotCached()
    {
        $key = vfsStream::url('root/adc3.php');
        
        $cache = $this->createMock(Twig_Cache_Filesystem::class);
        $cache->expects($this->any())->method('generateKey')
            ->with('base.html', get_class($this->resource) . ':base.html')->willReturn($key);
        $cache->expects($this->once())->method('getTimestamp')->with($key)->willReturn(0);
        $cache->expects($this->once())->method('write')
            ->with($key, '<?php return ' . var_export($this->expectedFormulae, true) . ';');

        $this->initTwig($this->once(), $cache, false);

        $this->resource->expects($this->never())->method('isFresh');
        
        $loader = new TwigCachingFormulaLoader($this->twig, $this->logger);
        $this->assertSame($cache, $loader->getCache());
        
        $formulae = $loader->load($this->resource);
        $this->assertSame($this->expectedFormulae, $formulae);        
    }
    
    public function testLoadFromCache()
    {
        vfsStream::create(['adc3.php' => '<?php return ' . var_export(['foo' => 'bar'], true) . ';']);
        
        $key = vfsStream::url('root/adc3.php');
        
        $cache = $this->createMock(Twig_Cache_Filesystem::class);
        $cache->expects($this->any())->method('generateKey')
            ->with('base.html', get_class($this->resource) . ':base.html')->willReturn($key);
        $cache->expects($this->once())->method('getTimestamp')->with($key)->willReturn(1496665557);
        
        $cache->expects($this->never())->method('write');
        
        $this->initTwig($this->never(), $cache, false);
        
        $this->resource->expects($this->never())->method('isFresh');
        
        $loader = new TwigCachingFormulaLoader($this->twig, $this->logger);
        $this->assertSame($cache, $loader->getCache());
        
        $formulae = $loader->load($this->resource);
        $this->assertSame(['foo' => 'bar'], $formulae);        
    }
    
    public function testLoadFromUnfreshCache()
    {
        vfsStream::create(['adc3.php' => '<?php return ' . var_export(['foo' => 'bar'], true) . ';']);
        
        $key = vfsStream::url('root/adc3.php');
        
        $cache = $this->createMock(Twig_Cache_Filesystem::class);
        $cache->expects($this->any())->method('generateKey')
            ->with('base.html', get_class($this->resource) . ':base.html')->willReturn($key);
        $cache->expects($this->once())->method('getTimestamp')->with($key)->willReturn(1496665557);
        $cache->expects($this->once())->method('write')
            ->with($key, '<?php return ' . var_export($this->expectedFormulae, true) . ';');

        $this->initTwig($this->once(), $cache, true);

        $this->resource->expects($this->once())->method('isFresh')->with(1496665557)->willReturn(false);
        
        $loader = new TwigCachingFormulaLoader($this->twig, $this->logger);
        $this->assertSame($cache, $loader->getCache());
        
        $formulae = $loader->load($this->resource);
        $this->assertSame($this->expectedFormulae, $formulae);        
    }
    
    public function testLoadFromFreshCache()
    {
        vfsStream::create(['adc3.php' => '<?php return ' . var_export(['foo' => 'bar'], true) . ';']);
        
        $key = vfsStream::url('root/adc3.php');
        
        $cache = $this->createMock(Twig_Cache_Filesystem::class);
        $cache->expects($this->any())->method('generateKey')
            ->with('base.html', get_class($this->resource) . ':base.html')->willReturn($key);
        $cache->expects($this->once())->method('getTimestamp')->with($key)->willReturn(1496665557);
        
        $cache->expects($this->never())->method('write');
        
        $this->initTwig($this->never(), $cache, true);
        
        $this->resource->expects($this->once())->method('isFresh')->with(1496665557)->willReturn(true);
        
        $loader = new TwigCachingFormulaLoader($this->twig, $this->logger);
        $this->assertSame($cache, $loader->getCache());
        
        $formulae = $loader->load($this->resource);
        $this->assertSame(['foo' => 'bar'], $formulae);        
    }
}
