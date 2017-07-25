<?php

namespace Bolt\Api\Console\Command;

use Sami\Console\Command\RenderCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Sami documentation render override class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Render extends RenderCommand
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->output = new BufferedOutput();
        $this->input = new ArrayInput([], new InputDefinition([
            new InputOption('verbose', true),
        ]));
    }

    /**
     * Echo output to stdout.
     */
    public function echoOutput()
    {
        echo $this->output->fetch();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
    }
}
