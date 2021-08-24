<?php

namespace App\Weather;

use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Retrieves weather by location
 */
class Weather
{
    const CACHE_EXPIRATION_TIME = 3600;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * @var string
     */
    protected string $apiUrl;

    /**
     * @var string
     */
    protected string $cacheKey;

    /**
     * Weather constructor.
     * @param CacheInterface $cache
     * @param HttpClientInterface $httpClient
     * @param string $apiUrl
     */
    public function __construct(
        CacheInterface $cache,
        HttpClientInterface $httpClient,
        string $apiUrl)
    {
        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param string $cacheKey
     */
    public function setCacheKey(string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @param array $geo // [lat, lon]
     * @param bool $refresh
     * @return string[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getForecastByGeoLocation(array $geo, bool $refresh = false): array
    {
        if (is_null($this->cacheKey)) {
            return $this->fetchForecastByGeoLocation($geo); // fetch from API directly
        }

        if ($refresh) {
            $this->cache->delete($this->cacheKey);
        }

        // Fetch and cache weather
        return $this->cache->get($this->cacheKey, function (CacheItem $item) use ($geo) {
            $item->expiresAfter(self::CACHE_EXPIRATION_TIME);
            return $this->fetchForecastByGeoLocation($geo);
        });
    }

    /**
     * @param array $geo
     * @return string[]
     */
    private function fetchForecastByGeoLocation(array $geo): array
    {
        $response = $this->httpClient->request('GET', $this->getApiUrl($geo));
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $response = $response->toArray();
        if ($response['cod'] !== 200 || isset($response['message'])) {
            return [];
        }

        return $response;
    }

    /**
     * @param array $geo
     * @return string
     */
    private function getApiUrl(array $geo): string
    {
        return str_replace(['{LAT}', '{LON}'], [$geo[0], $geo[1]], $this->apiUrl);
    }

}