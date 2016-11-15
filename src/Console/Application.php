<?php

namespace Bolt\Api\Console;

use Bolt\Api\Console\Command\Parse;
use Bolt\Api\Console\Command\Render;
use Sami\Console\Application as SamiApplication;

/**
 * Sami application override class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Application extends SamiApplication
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->add(new Parse());
        $this->add(new Render());
    }
}
