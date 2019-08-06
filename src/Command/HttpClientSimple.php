<?php

declare(strict_types = 1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientSimple extends Command
{
    protected static $defaultName = 'app:symfony:simple';

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
        try {
            $response = $this->httpClient->request('GET', 'https://reqres.in/api/users');
        } catch (TransportExceptionInterface $transportException) {
            $output->writeln(sprintf('<error>Request failed: %s</error>', $transportException->getMessage()));

            return 1;
        }

        $data = $response->toArray();

        $output->writeln(json_encode($data, JSON_PRETTY_PRINT));

        return 0;
    }
}
