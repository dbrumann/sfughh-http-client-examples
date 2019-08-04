<?php

declare(strict_types = 1);

namespace App\Command;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function json_encode;
use const JSON_PRETTY_PRINT;

final class GuzzleBatch extends Command
{
    protected static $defaultName = 'app:guzzle6:batch';

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
        $requests = [
            new Request('GET', 'https://reqres.in/api/users/1?delay=1'),
            new Request('GET', 'https://reqres.in/api/users/2?delay=3'),
            new Request('GET', 'https://reqres.in/api/users/3?delay=3'),
            new Request('GET', 'https://reqres.in/api/users/4?delay=5'),
            new Request('GET', 'https://reqres.in/api/users/5?delay=10'),
        ];
        $responses = [];

        foreach ($requests as $request) {
            try {
                $responses[] = $this->httpClient->send($request);
            } catch (GuzzleException $guzzleException) {
                $output->writeln(sprintf('<error>Request failed: %s</error>', $guzzleException->getMessage()));

                return 1;
            }
        }

        foreach ($responses as $response) {
            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);

            $output->writeln(json_encode($data, JSON_PRETTY_PRINT));
        }

        return 0;
    }
}
