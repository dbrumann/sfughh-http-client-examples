<?php

declare(strict_types = 1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function fclose;
use function feof;
use function fgets;
use function fopen;
use function is_resource;
use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;
use const JSON_ERROR_NONE;
use const JSON_PRETTY_PRINT;

final class NativeFopen extends Command
{
    protected static $defaultName = 'app:native:fopen';

    protected function configure()
    {
        $this->setDescription('Use fopen to communicate with an API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = fopen('https://reqres.in/api/users', 'r');
        if (!is_resource($connection)) {
            $output->writeln('<error>Could not connect to "https://reqres.in".</error>');

            return 1;
        }

        $response = '';
        while(!feof($connection)) {
            $line = fgets($connection, 4096);
            if ($line === false) {
                $output->writeln('<error>Read error!</error>');
            }

            $response .= $line;
        }
        fclose($connection);

        // Manually decode JSON from Request
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $output->writeln(sprintf('<error>Could not parse response as JSON: %s</error>', json_last_error_msg()));

            return 1;
        }

        $output->writeln(json_encode($data, JSON_PRETTY_PRINT));

        return 0;
    }
}
