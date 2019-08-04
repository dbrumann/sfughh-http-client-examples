<?php

declare(strict_types = 1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function explode;
use function fclose;
use function feof;
use function fgets;
use function fputs;
use function fsockopen;
use function is_resource;
use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function sprintf;
use const JSON_ERROR_NONE;
use const JSON_PRETTY_PRINT;

final class NativeFsockOpen extends Command
{
    protected static $defaultName = 'app:native:fsockopen';

    protected function configure()
    {
        $this->setDescription('Use fsockopen to communicate with an API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = fsockopen('ssl://reqres.in', 443, $errorCode, $errorMessage);
        if (!is_resource($connection)) {
            $output->writeln(sprintf('<error>[%d] Could not connect to API: %s</error>', $errorCode, $errorMessage));

            return 1;
        }

        // Send request by writing to socket line by line
        fputs($connection, "GET /api/users HTTP/1.1\r\n");
        fputs($connection, "Content-Type: application/json; charset=utf-8\r\n");
        fputs($connection, "Host: reqres.in\r\n");
        fputs($connection, "Connection: close\r\n\r\n");

        $response = '';
        while(!feof($connection)) {
            $line = fgets($connection, 4096);
            if ($line === false) {
                $output->writeln('<error>Read error!</error>');
            }

            $response .= $line;
        }
        fclose($connection);

        // Extract headers and body by looking for first 2 newlines separating them
        [$headers, $body] = explode("\r\n\r\n", $response, 2);

        //var_dump($headers);

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $output->writeln(sprintf('<error>Could not parse response as JSON. %s</error>', json_last_error_msg()));

            return 1;
        }

        $output->writeln(json_encode($data, JSON_PRETTY_PRINT));

        return 0;
    }
}
