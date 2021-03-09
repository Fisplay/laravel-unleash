<?php

namespace JWebb\Unleash\Entities\Api;

use FrancescoMalatesta\LaravelCircuitBreaker\Manager\CircuitBreakerManager;
use FrancescoMalatesta\LaravelCircuitBreaker\Service\ServiceOptionsResolver;
use FrancescoMalatesta\LaravelCircuitBreaker\Store\CacheCircuitBreakerStore;
use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractApi
{

    /**
     * Client object
     *
     * @var Client
     */
    protected $client;

    /**
     * Class of the entity.
     *
     * @var string
     */
    protected $class;

    /**
     * The API endpoint for the entity
     *
     * @var string
     */
    protected $endpoint;

    /**
     * The API entity name
     *
     * @var string
     */
    protected $entityName;

    /**
     * The API query parameters
     *
     * @var array
     */
    protected $params = [];

    /**
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get all of the Entities from the API resource.
     *
     * @return array
     * @throws \Exception
     */
    public function all(): array
    {
        try {
            $response = $this->request($this->getApiEndpoint(), $this->prepareParams());
            $response = json_decode((string)$response->getBody());

            if (property_exists($response, "$this->entityName")) {
                return array_map(function ($object) {
                    return $this->instantiateEntity($object);
                }, $response->{$this->entityName});
            }
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        return [];
    }

    /**
     * Get a specified Entity from the API resource.
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        $this->params = ['namePrefix' => $name];

        try {
            $response = $this->request($this->getApiEndpoint(), $this->prepareParams());
            $response = json_decode((string)$response->getBody(), true);

            return $this->handleResponse($response);
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }

    /**
     * Handle API response.
     *
     * When a filter has been applied, we must handle
     * the response differently.
     *
     * @param $response
     * @return array
     */
    public function handleResponse($response)
    {
        if (empty($this->filter)) {
            return $this->instantiateEntity($response);
        }

        return array_map(function ($object) {
            return $this->instantiateEntity($object);
        }, $response->data);
    }

    /**
     * Prepare the params for the request
     *
     * @return array
     */
    public function prepareParams(): array
    {
        return $this->params;
    }

    /**
     * Instantiate a new entityClass
     *
     * @param $params
     * @return mixed
     */
    public function instantiateEntity($params)
    {
        return new $this->class($params);
    }

    /**
     * Get the API endpoint
     *
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return config('unleash.url') . "/" . $this->endpoint . "/" . $this->entityName;
    }

    private function request(string $url, array $params = []): ResponseInterface
    {
        $circuitBreaker = null;

        if (config('unleash.circuit_breaker.enabled')) {
            $circuitBreaker = $this->createCircuitBreaker();

            if (!$circuitBreaker->isAvailable($url)) {
                throw new \Exception('Feature flag circuit breaker open');
            }
        }

        try {
            $response = $this->client->get($url, $params);

            if ($circuitBreaker) {
                $circuitBreaker->reportSuccess($url);
            }

            return $response;
        } catch (\Exception $e) {
            if ($circuitBreaker) {
                $circuitBreaker->reportFailure($url);
            }

            throw $e;
        }
    }

    private function createCircuitBreaker(): CircuitBreakerManager
    {
        return new CircuitBreakerManager(
            new CacheCircuitBreakerStore(app()->make('cache.store')),
            app()->make('events'),
            new ServiceOptionsResolver(
                new Repository([
                    'circuit_breaker' => [
                        'defaults' => [
                            'attempts_threshold' => config('unleash.circuit_breaker.attempts_threshold'),
                            'attempts_ttl' => config('unleash.circuit_breaker.attempts_ttl'),
                            'failure_ttl' => config('unleash.circuit_breaker.failure_ttl'),
                        ],
                        'services' => [],
                    ]
                ])
            )
        );
    }
}
