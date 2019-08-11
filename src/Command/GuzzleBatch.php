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
        // this command runs requests sequentially and reads them in request order too in 23s

        $delays = [10, 3, 3, 5, 1];
        $responses = [];

        foreach ($delays as $offset => $delay) {
            try {
                $url = sprintf('https://reqres.in/api/users/%d?delay=%d', $offset +1 , $delay);
                $responses[$url] = $this->httpClient->send(new Request('GET', $url));
            } catch (GuzzleException $guzzleException) {
                $output->writeln(sprintf('<error>Request failed: %s</error>', $guzzleException->getMessage()));

                return 1;
            }
        }

        foreach ($responses as $url => $response) {
            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);

            $output->writeln(json_encode([$url => $data], JSON_PRETTY_PRINT));
        }

        return 0;
    }
}
