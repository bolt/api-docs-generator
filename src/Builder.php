<?php

namespace Bolt\Api;

use Bolt\Api\Console\Command\Parse;
use Bolt\Api\Console\Command\Render;
use Bolt\Collection\Bag;
use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Handler\YamlFile;
use Sami\Project as SamiProject;
use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

/**
 * API Generator Builder.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class Builder
{
    /** @var FilesystemInterface */
    private $filesystem;
    /** @var string */
    private $root;

    /** @var Config */
    private $config;
    /** @var Project[] */
    private $projects;

    /**
     * Constructor.
     *
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        $this->root = dirname(__DIR__);
        $this->filesystem = new Filesystem(new Local($this->root));

        $this->loadConfig($config);
    }

    /**
     * Build configured Sami API documentation.
     *
     * @param Application $application
     * @param array|null  $config
     * @param array|null  $projects
     */
    public function build(Application $application, array $projects = null)
    {
        $this->loadProjects($projects);

        foreach ($this->projects as $projectName => $project) {
            $this->buildProject($application, $project);
        }
    }

    /**
     * @param Application $application
     * @param Project     $project
     */
    public function buildProject(Application $application, Project $project)
    {
        /** @var GitVersionCollection $versions */
        $versions = GitVersionCollection::create($project->getPath());
        foreach ($project->getBranches() as $branchName => $branchTitle) {
            $versions->add($branchName, $branchTitle);
        }
        $remoteRepository = new GitHubRemoteRepository($project->getName(), $project->getPath());

        /** @var Parse $parse */
        $parse = $application->get('parse');
        /** @var Render $render */
        $render = $application->get('render');

        foreach ($project->getBuilds() as $buildName => $buildTitle) {
            $buildConfig = $this->getBuildConfig($project, $versions, $remoteRepository, $buildName);
            $container = $this->createContainer($project, $buildConfig);

            /** @var SamiProject $samiProject */
            $samiProject = $container['project'];

            echo "Parsing $buildName $buildTitle\n";
            $samiProject->parse([$parse, 'messageCallback'], false);
            $parse->echoOutput();

            echo "Rendering $buildName $buildTitle\n";
            $samiProject->render([$render, 'messageCallback'], true);
            $render->echoOutput();
        }
    }

    /**
     * Build a Sami application container.
     *
     * @param Project $project
     * @param array   $buildConfig
     *
     * @return Sami
     */
    private function createContainer(Project $project, array $buildConfig)
    {
        $container = new Sami($this->getIterator($project), $buildConfig);
        $container['template_dirs'] = $this->config->getThemePath();

        return $container;
    }

    /**
     * Get an iterator for the repository source code directories.
     *
     * @return Finder|\Symfony\Component\Finder\SplFileInfo[]
     */
    private function getIterator(Project $project)
    {
        return Finder::create()
            ->files()
            ->name('*.php')
            ->in($project->getIncludes())
        ;
    }

    /**
     * Return the build configuration for a project.
     *
     * @param Project                $project
     * @param GitVersionCollection   $versions
     * @param GitHubRemoteRepository $remoteRepository
     * @param string                 $build
     *
     * @return array
     */
    private function getBuildConfig(Project $project, GitVersionCollection $versions, GitHubRemoteRepository $remoteRepository, $build)
    {
        return [
            'theme'                => $this->config->getTheme(),
            'versions'             => $versions,
            'title'                => $project->getBuildTitle($build),
            'build_dir'            => $this->config->getBuildPath($project->getName(), $build),
            'cache_dir'            => $this->config->getCachePath($project->getName(), $build),
            'remote_repository'    => $remoteRepository,
            'default_opened_level' => $this->config->getDefaultOpenedLevel(),
        ];
    }

    /**
     * Load the configuration from the YAML file.
     *
     * @param array|null $parameters
     */
    private function loadConfig($parameters)
    {
        if ($parameters === null) {
            /** @var YamlFile $configFile */
            $configFile = $this->filesystem->getFile('app/config/config.yml');
            if (!$configFile->exists()) {
                throw new \RuntimeException('The config directory is missing a config.yml file.');
            }
            $parameters = (array) $configFile->parse();
        }

        $this->config = new Config($this->root, Bag::from($parameters));
    }

    /**
     * Load the projects from the YAML file.
     *
     * @param array|null $parameters
     */
    private function loadProjects($parameters)
    {
        if ($parameters === null) {
            /** @var YamlFile $projectsFile */
            $projectsFile = $this->filesystem->getFile('app/config/projects.yml');
            if (!$projectsFile->exists()) {
                throw new \RuntimeException('The config directory is missing a projects.yml file.');
            }
            $parameters = (array) $projectsFile->parse();
        }
        $parameters = Bag::fromRecursive($parameters);

        foreach ($parameters as $projectName => $config) {
            /** @var Bag $config */
            if (!$config->has('path')) {
                throw new \RuntimeException(sprintf('Project "%s" has no path set.', $projectName));
            }

            $this->projects[$projectName] = new Project($projectName, $this->root, $config);
        }
    }
}
