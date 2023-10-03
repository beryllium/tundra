<?php

namespace Whateverthing\Tundra\Commands\Mastodon;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AuthCommand extends Command
{
    public function __construct()
    {
        parent::__construct('mastodon:auth');
    }

    public function configure()
    {
        $this->setDescription('Authenticate with a Mastodon instance');
        $this->setHelp('Does the annoyingly complicated auth dance and shows the values to add to .env');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Nothing to do yet.');

        return self::SUCCESS;
    }
}