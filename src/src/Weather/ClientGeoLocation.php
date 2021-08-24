<?php

namespace App\Weather;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Retrieves client's location by IP address
 */
class ClientGeoLocation
{
    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * @var ClientIpAddress
     */
    protected ClientIpAddress $clientIpAddress;

    /**
     * @var string
     */
    private string $apiUrl;

    /**
     * ClientLocation constructor.
     *
     * @param HttpClientInterface $httpClient
     * @param string $apiUrl
     */
    public function __construct(
        HttpClientInterface $httpClient,
        string $apiUrl)
    {
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Get location by IP
     *
     * @param string|null $ip
     * @return array
     */
    public function getGeoLocation(string $ip = null): array
    {
        return $this->fetchGeoLocation($ip);
    }

    /**
     * @param string $ip
     * @return array
     */
    private function fetchGeoLocation(string $ip): array
    {
        $response = $this->httpClient->request('GET', $this->getApiUrl($ip));

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $response = $response->toArray();
        if (isset($response['error'])) {
            return [];
        }

        return [$response['latitude'], $response['longitude']];
    }

    /**
     * @param string $ip
     * @return string
     */
    private function getApiUrl(string $ip): string
    {
        return str_replace('{IP}', $ip, $this->apiUrl);
    }
}