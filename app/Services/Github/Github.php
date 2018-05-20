<?php

namespace App\Services\Github;

use App\Services\Github\Exceptions\RepositoryNotFound;
use Cache\Adapter\Illuminate\IlluminateCachePool;
use Github\Client;
use Github\Exception\RuntimeException;
use Illuminate\Contracts\Cache\Store;

class Github
{
    /**
     * The Github API client instance.
     *
     * @var \Github\Client
     */
    public $client;

    /**
     * The email address to notify.
     *
     * @var string
     */
    public $notifiedAddress;

    /**
     * The cache store implementation.
     *
     * @var \Illuminate\Contracts\Cache\Store
     */
    private $cache;

    /**
     * Github constructor.
     *
     * @param array $config
     * @param \Illuminate\Contracts\Cache\Store $cache
     */
    public function __construct(array $config, Store $cache)
    {
        $this->notifiedAddress = $config['notified_address'];

        $this->cache = $cache;

        $this->setupClient($config);
    }

    /**
     * @param string $username
     * @param string $repository
     * @return array
     * @throws \App\Services\Github\Exceptions\RepositoryNotFound
     */
    public function getRepository(string $username, string $repository): array
    {
        try {
            return $this->client->repository()->show($username, $repository);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'Not Found') {
                throw new RepositoryNotFound($username, $repository, $exception->getCode(), $exception->getPrevious());
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @param string $username
     * @param string $repository
     * @return array
     */
    public function getLatestRelease(string $username, string $repository): array
    {
        return $this->client->repository()->releases()->latest($username, $repository);
    }

    /**
     * Setup the Github API client.
     *
     * @param array $config
     */
    private function setupClient(array $config): void
    {
        $client = new Client();

        $pool = new IlluminateCachePool($this->cache);
        $client->addCache($pool);

        if ($config['client_id'] && $config['client_secret']) {
            $client->authenticate($config['client_id'], $config['client_secret'], $client::AUTH_URL_CLIENT_ID);
        }

        $this->client = $client;
    }
}
