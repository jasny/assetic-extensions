<?php

namespace Jasny\Assetic;

use Assetic\AssetWriter;
use Assetic\Util\VarUtils;
use Assetic\Asset\AssetInterface;

/**
 * Writes assets to the filesystem.
 * Added the option, not to overwrite existing files.
 */
class PersistentAssetWriter extends AssetWriter
{
    /**
     * Overwrite existing asset files
     * @var boolean
     */
    protected $overwrite = false;

    /**
     * @var string
     */
    protected $dir;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * Class constructor
     * 
     * @param string  $dir
     * @param array   $values
     * @param boolean $overwrite
     */
    public function __construct($dir, array $values = [], $overwrite = false)
    {
        parent::__construct($dir, $values);
        
        $this->dir = $dir;
        $this->values = $values;
        $this->overwrite = $overwrite;
    }
    
    /**
     * Write the asset to file
     * 
     * @param AssetInterface $asset
     */
    public function writeAsset(AssetInterface $asset)
    {
        foreach (VarUtils::getCombinations($asset->getVars(), $this->values) as $combination) {
            $asset->setValues($combination);
            $path = $this->dir . '/' .
                VarUtils::resolve($asset->getTargetPath(), $asset->getVars(), $asset->getValues());
            
            if (!$this->overwrite && file_exists($path)) {
                continue;
            }

            static::write($path, $asset->dump());
        }
    }
}
