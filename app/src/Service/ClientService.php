<?php

namespace App\Service;

use Exception;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientService implements ClientServiceInterface
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function request (string $url, array $options = []): array
    {
        try {
            $response = $this->httpClient->request('GET', $url, $options);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('API request failed with status code ' . $response->getStatusCode());
            }

            return $response->toArray();
        } catch (TransportExceptionInterface|Exception $e) {
            return [
                'error' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

}
