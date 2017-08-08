<?php

namespace Bolt\Api;

use Bolt\Collection\Bag;

/**
 * Project configuration class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class Project
{
    /** @var string */
    private $name;
    /** @var Bag */
    private $parameters;

    /**
     * Constructor.
     *
     * @param string $projectName
     * @param string $rootDir
     * @param Bag    $parameters
     */
    public function __construct($projectName, $rootDir, Bag $parameters)
    {
        $parameters = $parameters->defaults($this->getDefaults());

        $path = $parameters->get('path');
        $realPath = realpath(str_replace('%root%', $rootDir, $path));
        if ($realPath === false || !file_exists($realPath)) {
            throw new \RuntimeException(sprintf('Project "%s" path %s does not exist.', $projectName, $path));
        }

        $this->parameters = $parameters->replace(['path' => $realPath]);
        $this->name = $projectName;
    }

    /**
     * Return the project name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the projects file system path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->parameters->get('path');
    }

    /**
     * Return the (sub) directories to be scanned.
     *
     * @return array
     */
    public function getIncludes()
    {
        $path = $this->parameters->get('path');
        $includes = $this->parameters->get('includes');
        if ($includes === null) {
            return (array) $path;
        }
        if (is_string($includes)) {
            return (array) "$path/$includes";
        }
        $dirs = [];
        foreach ($includes as $include) {
            $dirs[] = $path . '/' . $include;
        }

        return $dirs;
    }

    /**
     * Return a single branch
     *
     * @param string $branch
     *
     * @return string[]
     */
    public function getBranch($branch)
    {
        if ($this->parameters->hasPath("branches/$branch")) {
            return $this->parameters->getPath("branches/$branch");
        }

        throw new \RuntimeException(sprintf('Branch %s key not set.', $branch));
    }

    /**
     * Return the branches to generate documentation for.
     *
     * @return string[]
     */
    public function getBranches()
    {
        return $this->parameters->get('branches');
    }

    /**
     * Return the page title for a build.
     *
     * @param string $build Build name, either "public" or "internal".
     *
     * @return string
     */
    public function getBuildTitle($build)
    {
        if ($this->parameters->hasPath("builds/$build")) {
            return $this->parameters->getPath("builds/$build");
        }

        throw new \RuntimeException(sprintf('Build %s key not set.', $build));
    }

    /**
     * @return string[]
     */
    public function getBuilds()
    {
        return $this->parameters->get('builds');
    }

    /**
     * @return array
     */
    private function getDefaults()
    {
        return [
            'path'     => null,
            'branches' => [],
            'builds'   => [
                'public'   => 'Public API',
                'internal' => 'Internal API',
            ],
        ];
    }
}
