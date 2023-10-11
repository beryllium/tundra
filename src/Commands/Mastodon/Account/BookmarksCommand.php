<?php

namespace Whateverthing\Tundra\Commands\Mastodon\Account;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Whateverthing\Tundra\Api\Mastodon\Entities\Status;
use Whateverthing\Tundra\Api\Mastodon\MastodonClient;

class BookmarksCommand extends Command
{
    use PaginationHelperTrait;

    public function __construct(protected MastodonClient $client)
    {
        parent::__construct('mastodon:bookmarks');
    }

    public function configure()
    {
        $this->setDescription('Fetch your Mastodon bookmarks');
        $this->setHelp('Fetch Bookmarks');

        $this->addOption('max-id', null, InputOption::VALUE_REQUIRED, 'All results returned will be lesser than this ID');
        $this->addOption('since-id', null, InputOption::VALUE_REQUIRED, 'All results returned will be newer than this ID');
        $this->addOption('min-id', null, InputOption::VALUE_REQUIRED, 'All results returned will be greater than this ID');
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max results to return. Default: 20, Max: 40.', 20);

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();

        $result = $this->client->request(
            apiMethod: 'bookmarks',
            max_id: $options['max-id'] ?? null,
            since_id: $options['since-id'] ?? null,
            min_id: $options['min-id'] ?? null,
            limit: $options['limit'] ?? null,
        );

        $table = new Table($output);
        $table->setHeaders(['Key', 'Value']);

        foreach ($result ?? [] as $row) {
            $post = new Status(...$row);

            foreach ($post->simpleArray() as $key => $value) {
                $table->addRow([$key, $value]);
            }

            $table->addRow(new TableSeparator());
        }
        $table->setColumnMaxWidth(1, 80);
        $table->render();

        $pagination = $this->client->getPaginationLinks();

        if ($pagination) {
            $this->showPagination($pagination, $input, $output);
        }

        return self::SUCCESS;
    }
}