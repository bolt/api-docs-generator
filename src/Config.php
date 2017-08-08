<?php

namespace Bolt\Api;

/**
 * Configuration class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Config
{
    /** @var string */
    protected $theme;
    /** @var integer */
    protected $defaultOpenedLevel;
    /** @var string[] */
    protected $paths;
    /** @var string[] */
    protected $branches;
    /** @var string[] */
    protected $builds;

    /** @var string */
    private $root;

    public function __construct(array $parameters = [])
    {
        $parameters += $this->getDefaults();

        $this->root = dirname(__DIR__);
        $this->theme = $parameters['theme'];
        $this->defaultOpenedLevel = $parameters['default_opened_level'];

        foreach ($parameters['paths'] as $name => $title) {
            $this->paths[$name] = $title;
        }
        foreach ($parameters['branches'] as $name => $title) {
            $this->branches[$name] = $title;
        }
        foreach ($parameters['builds'] as $name => $title) {
            $this->builds[$name] = $title;
        }
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     *
     * @return Config
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultOpenedLevel()
    {
        return $this->defaultOpenedLevel;
    }

    /**
     * @param int $defaultOpenedLevel
     *
     * @return Config
     */
    public function setDefaultOpenedLevel($defaultOpenedLevel)
    {
        $this->defaultOpenedLevel = $defaultOpenedLevel;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPath($path)
    {
        if (!isset($this->paths[$path])) {
            throw new \RuntimeException(sprintf('Path %s key not set.', $path));
        }

        $realPath = realpath(str_replace('%root%', $this->root, $this->paths[$path]));
        if ($realPath === false || !file_exists($realPath)) {
            throw new \RuntimeException(sprintf('Configured path %s does not exist.', $path));
        }

        return $realPath;
    }

    /**
     * @return string[]
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param string[] $paths
     *
     * @return Config
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuildPath($build)
    {
        $path = $this->root . '/var/build';
        $realPath = realpath($this->root . '/var/build');
        if ($realPath === false || !file_exists($realPath)) {
            throw new \RuntimeException(sprintf('Configured build path %s does not exist.', $path));
        }

        return "$path/$build/%version%";
    }

    /**
     * @return string
     */
    public function getCachePath($build)
    {
        $path = $this->root . '/var/cache';
        $realPath = realpath($this->root . '/var/cache');
        if ($realPath === false || !file_exists($realPath)) {
            throw new \RuntimeException(sprintf('Configured cache path %s does not exist.', $path));
        }

        return "$path/$build/%version%";
    }

    /**
     * @return array
     */
    public function getThemePath()
    {
        $path = $this->root . '/themes';
        $realPath = realpath($this->root . '/themes');
        if ($realPath === false || !file_exists($realPath)) {
            throw new \RuntimeException(sprintf('Configured theme path %s does not exist.', $path));
        }

        return [$path];
    }

    /**
     * @param string $branch
     *
     * @return string[]
     */
    public function getBranch($branch)
    {
        if (!isset($this->branches[$branch])) {
            throw new \RuntimeException(sprintf('Branch %s key not set.', $branch));
        }

        return $this->branches[$branch];
    }

    /**
     * @return string[]
     */
    public function getBranches()
    {
        return $this->branches;
    }

    /**
     * @param string[] $branches
     *
     * @return Config
     */
    public function setBranches($branches)
    {
        $this->branches = $branches;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getBuildTitle($build)
    {
        if (!isset($this->builds[$build])) {
            throw new \RuntimeException(sprintf('Build %s key not set.', $build));
        }

        return $this->builds[$build];
    }

    /**
     * @return string[]
     */
    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * @param string[] $builds
     *
     * @return Config
     */
    public function setBuilds($builds)
    {
        $this->builds = $builds;

        return $this;
    }

    /**
     * @return array
     */
    private function getDefaults()
    {
        return [
            'theme' => 'bolt',
            'paths' => [
                'repository' => '%root%/../repository',
                'build'      => '%root%/build',
                'cache'      => '%root%/cache',
            ],
            'branches' => [
            ],
            'builds' => [
                'public'   => 'Bolt Public API',
                'internal' => 'Bolt Internal API',
            ],
            'default_opened_level' => 2,
        ];
    }
}
