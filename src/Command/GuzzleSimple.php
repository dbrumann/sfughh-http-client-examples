<?php

declare(strict_types = 1);

namespace App\Command;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function json_encode;
use const JSON_PRETTY_PRINT;

final class GuzzleSimple extends Command
{
    protected static $defaultName = 'app:guzzle5:simple';

    private $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        parent::__construct(null);

        $this->httpClient = $httpClient;
    }

    protected function configure()
    {
        $this->setDescription('Use Guzzle 5 to communicate with an API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $response = $this->httpClient->get('https://reqres.in/api/users');
        } catch (RequestException $requestException) {
            //$requestException->getRequest();
            //$requestException->getResponse();
            $output->writeln(sprintf('<error>Request failed: %s</error>', $requestException->getMessage()));

            return 1;
        }

        // $response->getHeaders();

        $output->writeln(json_encode($response->json(), JSON_PRETTY_PRINT));
    }
}
