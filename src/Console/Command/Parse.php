<?php

namespace Bolt\Api\Console\Command;

use Sami\Console\Command\ParseCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Sami source code parser override class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Parse extends ParseCommand
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->input = new ArgvInput(null, new InputDefinition([
            new InputOption('verbose', 'v', InputOption::VALUE_NONE),
            new InputOption('cron', null, InputOption::VALUE_NONE),
        ]));
        $this->output = new ConsoleOutput($this->input->getOption('verbose'));
    }

    /**
     * {@inheritdoc}
     */
    public function renderProgressBar($percent, $length)
    {
        if ($this->input->getOption('cron')) {
            return;
        }

        parent::renderProgressBar($percent, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function displayRenderProgress($section, $message, $progression)
    {
        if ($this->input->getOption('cron')) {
            return;
        }

        parent::displayRenderProgress($section, $message, $progression);
    }
}
