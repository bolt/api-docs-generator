<?php

namespace Bolt\Api;

use Bolt\Collection\Bag;

/**
 * Configuration class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class Config
{
    /** @var Bag */
    private $parameters;

    /**
     * Constructor.
     *
     * @param string $rootDir
     * @param Bag    $parameters
     */
    public function __construct($rootDir, Bag $parameters)
    {
        $this->parameters = $parameters->defaults($this->getDefaults());
        $this->buildPaths($rootDir);
    }

    /**
     * Return the default depth to open trees.
     *
     * @return int
     */
    public function getDefaultOpenedLevel()
    {
        return (int) $this->parameters->get('default_opened_level');
    }

    /**
     * Return a specific path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getPath($path)
    {
        if ($this->parameters->hasPath("paths/$path")) {
            return $this->parameters->getPath("paths/$path");
        }

        throw new \RuntimeException(sprintf('Path "%s" key not set.', $path));
    }

    /**
     * @return string[]
     */
    public function getPaths()
    {
        return $this->parameters->get('paths');
    }

    /**
     * Return the build directory for a project's build (internal or public).
     *
     * @param string $projectName
     * @param string $type
     *
     * @return string
     */
    public function getBuildPath($projectName, $type)
    {
        $path = $this->getPath('build');

        return "$path/$projectName/$type/%version%";
    }

    /**
     * Return the cache directory for a project's build (internal or public).
     *
     * @param string $projectName
     * @param string $type
     *
     * @return string
     */
    public function getCachePath($projectName, $type)
    {
        $path = $this->getPath('cache');

        return "$path/$projectName/$type/%version%";
    }

    /**
     * Return the theme directory.
     *
     * @return array
     */
    public function getThemePath()
    {
        $path = $this->getPath('theme');

        return [$path];
    }

    /**
     * Return the name of the in-use theme matching the manifest.yml setting.
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->parameters->get('theme');
    }

    /**
     * @return array
     */
    private function getDefaults()
    {
        return [
            'default_opened_level' => 2,
            'paths'                => [
                'build'      => '%root%/build',
                'cache'      => '%root%/cache',
            ],
            'theme' => 'bolt',
        ];
    }

    /**
     * @param string $rootDir
     */
    private function buildPaths($rootDir)
    {
        $paths = $this->parameters->get('paths');
        foreach ($paths as $name => $path) {
            $paths[$name] = str_replace('%root%', $rootDir, $path);
        }

        $this->parameters = $this->parameters->replace(['paths' => $paths]);
    }
}
