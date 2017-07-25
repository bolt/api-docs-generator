<?php

namespace Bolt\Api;

use Bolt\Api\Console\Command\Parse;
use Bolt\Api\Console\Command\Render;
use Sami\Project;
use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * API Generator Builder.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Builder
{
    /** @var Config */
    protected $config;
    /** @var GitVersionCollection */
    protected $versions;
    /** @var GitHubRemoteRepository */
    protected $remoteRepository;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        $this->loadConfig($config);

        $this->versions = GitVersionCollection::create($this->config->getPath('repository'));
        foreach ($this->config->getBranches() as $branchName => $branchTitle) {
            $this->versions->add($branchName, $branchTitle);
        }
        $this->remoteRepository = new GitHubRemoteRepository('bolt/bolt', $this->config->getPath('repository'));
    }

    /**
     * Build configured Sami API documentation.
     *
     * @param Application $application
     */
    public function build(Application $application)
    {
        /** @var Parse $parse */
        $parse = $application->get('parse');
        /** @var Render $render */
        $render = $application->get('render');

        foreach ($this->config->getBuilds() as $buildName => $buildTitle) {
            $container = $this->createContainer($buildName);

            /** @var Project $publicProject */
            $project = $container['project'];

            $project->parse([$parse, 'messageCallback'], false);
            $parse->echoOutput();
            $project->render([$render, 'messageCallback'], true);
            $render->echoOutput();
        }
    }

    /**
     * Build a Sami application container.
     *
     * @param string $build
     *
     * @return Sami
     */
    protected function createContainer($build)
    {
        $container = new Sami($this->getIterator(), $this->getBuildConfig($build));
        $container['template_dirs'] = $this->config->getThemePath();

        return $container;
    }

    /**
     * Get an iterator for the repository directory.
     *
     * @return Finder|\Symfony\Component\Finder\SplFileInfo[]
     */
    protected function getIterator()
    {
        return Finder::create()
            ->files()
            ->name('*.php')
            ->in($this->config->getPath('repository') . '/src')
        ;
    }

    /**
     * GReturn the default configuration.
     *
     * @param string $build
     *
     * @return array
     */
    protected function getBuildConfig($build)
    {
        return [
            'theme'                => $this->config->getTheme(),
            'versions'             => $this->versions,
            'title'                => $this->config->getBuildTitle($build),
            'build_dir'            => $this->config->getBuildPath($build),
            'cache_dir'            => $this->config->getCachePath($build),
            'remote_repository'    => $this->remoteRepository,
            'default_opened_level' => $this->config->getDefaultOpenedLevel(),
        ];
    }

    /**
     * Load the configuration from YAML file.
     *
     * @param array|null $parameters
     */
    protected function loadConfig($parameters)
    {
        if ($parameters === null) {
            $parameters = Yaml::parse(file_get_contents(realpath(__DIR__ . '/../app/config/config.yml')));
        }

        $this->config = new Config($parameters);
    }
}
