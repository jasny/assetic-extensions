<?php

namespace Jasny\Assetic;

use Assetic\Extension\Twig\TwigFormulaLoader;
use Psr\Log\LoggerInterface;
use Assetic\Factory\Resource\ResourceInterface;

/**
 * Twig formula loader with caching
 */
class TwigCachingFormulaLoader extends TwigFormulaLoader
{
    /**
     * @var boolean
     */
    protected $autoReload;
    
    /**
     * @var \Twig_CacheInterface 
     */
    protected $cache;
    
    /**
     * Class constructor
     * 
     * @param Twig_Environment $twig
     * @param LoggerInterface  $logger
     */
    public function __construct(Twig_Environment $twig, LoggerInterface $logger = null)
    {
        parent::__construct($twig, $logger);
        
        $this->autoReload = $twig->isAutoReload();
        
        $cache = $twig->getCache();
        if ($cache !== false) {
            $this->cache = is_string($cache) ? new \Twig_Cache_Filesystem($cache) : $cache;
        }
    }

    /**
     * Get cache key for resource
     * 
     * @param ResourceInterface $resource
     * @return string
     */
    protected function getCacheKey(ResourceInterface $resource)
    {
        return $this->cache->generateKey((string)$resource, get_class($resource) . ':' . (string)$resource);
    }
    
    /**
     * Get formulea for a resource from cache.
     * 
     * @param ResourceInterface $resource
     * @return array|null
     */
    protected function loadFromCache(ResourceInterface $resource)
    {
        if (!$this->cache instanceof \Twig_Cache_Filesystem) {
            return;
        }
        
        $key = $this->getCacheKey($resource);
        $timestamp = $this->cache->getTimestamp($key);
            
        if (!$this->autoReload || $resource->isFresh($timestamp)) {
            return include $key; // Unfortunately we can't use Twig cache, because it doesn't return the value
        }
        
        return null;
    }
    
    /**
     * Store formulea in cache
     * 
     * @param ResourceInterface $resource
     * @param array $formulea
     */
    protected function writeToCache(ResourceInterface $resource, array $formulea)
    {
        if (!$this->cache instanceof \Twig_Cache_Filesystem) {
            return;
        }
        
        $key = $this->getCacheKey($resource);
        $this->cache->write($key, '<?php return ' . var_export($formulea, true) . ';');
    }
    
    /**
     * Get formulea for a resource.
     * 
     * @param ResourceInterface $resource
     * @return array
     */
    public function load(ResourceInterface $resource)
    {
        $formulea = $this->loadFromCache($resource);
        
        if ($formulea === null) {
            $formulea = parent::load($resource);
            $this->writeToCache($resource, $formulea);
        }
        
        return $formulea;
    }    
}
