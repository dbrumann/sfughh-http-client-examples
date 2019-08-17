<?php

declare(strict_types = 1);

namespace App\Command;

use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function GuzzleHttp\Promise\unwrap;
use function json_decode;
use function json_encode;
use function sprintf;
use const JSON_PRETTY_PRINT;

final class GuzzleAsync extends Command
{
    protected static $defaultName = 'app:guzzle6:async';

    private $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        parent::__construct(null);

        $this->httpClient = $httpClient;
    }

    protected function configure()
    {
        $this->setDescription('Use Guzzle6 to communicate with an API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // this command runs requests concurrently and reads them in *request* order in 10s

        $delays = [10, 3, 3, 5, 1];
        $promises = [];

        foreach ($delays as $offset => $delay) {
            $url = sprintf('https://reqres.in/api/users/%d?delay=%d', $offset + 1 , $delay);
            $promises[$url] = $this->httpClient->requestAsync('GET', $url);
        }

        try {
            $responses = unwrap($promises);
        } catch (\Throwable $error) {
            $output->writeln(sprintf('<error>Request failed: %s</error>', $error->getMessage()));

            return 1;
        }

        foreach ($responses as $url => $response) {
            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);

            $output->writeln(json_encode([$url => $data], JSON_PRETTY_PRINT));
        }

        return 0;
    }
}
