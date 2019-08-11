<?php

declare(strict_types = 1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function json_encode;
use function sprintf;
use const JSON_PRETTY_PRINT;

final class HttpClientBatch extends Command
{
    protected static $defaultName = 'app:symfony:batch';

    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        parent::__construct(null);

        $this->httpClient = $httpClient;
    }

    protected function configure()
    {
        $this->setDescription('Use Symfony HttpClient to communicate with an API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // this command runs requests concurrently and read them in *request* order in 10s too

        $delays = [10, 3, 3, 5, 1];
        $responses = [];

        foreach ($delays as $offset => $delay) {
            $url = sprintf('https://reqres.in/api/users/%d?delay=%d', $offset + 1, $delay);
            $responses[] = $this->httpClient->request('GET', $url);
        }

        /** @var ResponseInterface $response */
        foreach ($responses as $url => $response) {
            try {
                $data = $response->toArray();

                $output->writeln(json_encode([$url => $data], JSON_PRETTY_PRINT));
            } catch (TransportExceptionInterface $transportException) {
                $output->writeln(sprintf('<error>Request failed: %s</error>', $transportException->getMessage()));

                return 1;
            }
        }

        return 0;
    }
}
