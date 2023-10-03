<?php

namespace Whateverthing\Tundra\Commands\Mastodon\Timeline;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Whateverthing\Tundra\Api\Mastodon\MastodonClient;

class TimelineCommand extends Command
{
    public function __construct(protected MastodonClient $client)
    {
        parent::__construct('mastodon:timeline');
    }

    public function configure()
    {
        $this->setDescription('Fetch a Mastodon timeline');
        $this->setHelp('Fetch Timeline');

        $this->addOption('only-local', null, InputOption::VALUE_NONE, 'Show only local statuses');
        $this->addOption('only-remote', null, InputOption::VALUE_NONE, 'Show only remote statuses');
        $this->addOption('only-media', 'm', InputOption::VALUE_NONE, 'Show only statuses with attached media');

        $this->addOption('max', null, InputOption::VALUE_REQUIRED, 'All results returned will be lesser than this ID');
        $this->addOption('since', null, InputOption::VALUE_REQUIRED, 'All results returned will be newer than this ID');
        $this->addOption('min', null, InputOption::VALUE_REQUIRED, 'All results returned will be greater than this ID');
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max results to return. Default: 20, Max: 40.', 20);

        $this->addOption('server', 's', InputOption::VALUE_REQUIRED, 'Server to query. Default: Configured Server');
        $this->addOption('timeline', 't', InputOption::VALUE_REQUIRED, 'Timeline to query (public, home, list). Default: public', 'public');
        $this->addOption('list-id', null, InputOption::VALUE_REQUIRED, 'List ID to query, when fetching a list timeline');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Nothing to do yet.');

        $options = $input->getOptions();

        $output->writeln(print_r($options, true));

        $result = $this->client->request(
            apiMethod: 'timelines/' . $options['timeline'],
            local: (bool) ($options['only-local'] ?? false),
            remote: (bool) ($options['only-remote'] ?? false),
            onlyMedia: (bool) ($options['only-media'] ?? false),
            maxId: $options['max-id'] ?? null,
            sinceId: $options['since-id'] ?? null,
            minId: $options['min-id'] ?? null,
            listId: $options['list-id'] ?? null,
            server: $options['server'] ?? null,
        );

        $table = new Table($output);
        $table->setHeaders(['Key', 'Value']);

        foreach ($result as $row) {
            foreach ($row as $key => $value) {
                if ($value instanceof \stdClass) {
                    $value = (array) $value;
                }
                if ($key === 'account') {
                    $value = ['id' => $value['id'], 'username' => $value['username'], 'acct' => $value['acct']];
                }
                $table->addRow([$key, substr(var_export($value, true), 0, 50)]);
            }
            $table->addRow(new TableSeparator());
        }

        $table->render();


        return self::SUCCESS;
    }
}