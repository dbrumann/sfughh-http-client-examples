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

final class GuzzleSimple extends Command
{
    protected static $defaultName = 'app:guzzle6:simple';

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
        $request = new Request('GET', 'https://reqres.in/api/users');

        try {
            $response = $this->httpClient->send($request);
        } catch (GuzzleException $guzzleException) {
            $output->writeln(sprintf('<error>Request failed: %s</error>', $guzzleException->getMessage()));

            return 1;
        }

        $responseBody = $response->getBody()->getContents();
        $data = json_decode($responseBody, true);

        $output->writeln(json_encode($data, JSON_PRETTY_PRINT));

        return 0;
    }
}
