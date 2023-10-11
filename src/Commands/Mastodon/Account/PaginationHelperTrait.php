<?php

namespace Whateverthing\Tundra\Commands\Mastodon\Account;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait PaginationHelperTrait
{
    private function handlePagination(array $pagination, InputInterface $input, OutputInterface $output): void
    {
        foreach ($pagination as $page => $link) {
            // 1. Dissect link
            $queryParams = [];
            $query = parse_url($link, PHP_URL_QUERY);
            parse_str($query, $queryParams);

            // 2. Echo the command to load $page
            $cmdString = '';
            foreach ($queryParams as $param => $value) {
                $cmdString .= '--' . str_replace('_', '-', $param) . '=' . $value . ' ';
            }

            $output->writeln(
                'Command for ' . $page . ' page: ' . $_SERVER['PHP_SELF']
                . ' ' . $this->getName() . ' ' . $cmdString
            );
        }
    }
}