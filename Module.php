<?php
/**
 * Soliant Consulting
 * http://www.soliantconsulting.com
 *
 * @author tanderson@soliantconsulting.com
 * @version 1.0
 */

namespace SoliantAssetsCompiler;

use Zend\ModuleManager\ModuleManager,
    ReflectionClass;

class Module
{
    private $moduleManager;

    private function getModuleManager() {
        return $this->moduleManager;
    }

    public function setModuleManager(ModuleManager $moduleManager) {
        $this->moduleManager = $moduleManager;
    }

    public function init(ModuleManager $moduleManager)
    {
        $this->setModuleManager($moduleManager);
    }

    public function getConfig()
    {
        return array();
    }

    public function onBootstrap($e) {
        foreach ($this->getModuleManager()->getModules() as $module) {
            if ($path = $this->pathTo($module)) {
               $this->copyr($path, __DIR__ . '/../../public/assets/' . $module);
            }
        }
    }


    // Copied from
    // https://github.com/monzee/ZF2-Assets-module
    protected function pathTo($module)
    {
        $clsName = $module . '\Module';
        $class = new $clsName;
        if (method_exists($class, 'getAssetsPath')) 
            return $class->getAssetsPath();
        return false;
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @return      bool     Returns TRUE on success, FALSE on failure
     */
    private function copyr($source, $dest)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Check for hidden files
        if (substr(basename($source), 0, 1) == '.' AND basename($source) !== '.htaccess') return;

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->copyr("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }
}

