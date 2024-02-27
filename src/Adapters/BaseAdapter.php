<?php
namespace Andrey\Symfony\Messenger\Mongo\Adapters;

use Andrey\Symfony\Messenger\Mongo\Types\Configuration;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

readonly abstract class BaseAdapter
{
    /**
     * @param Client $client
     * @param Configuration $config
     */
    public function __construct(
        protected Client $client,
        protected Configuration $config,
    ) { }

    protected function getDatabase(): Database
    {
        return $this->client->selectDatabase($this->config->database);
    }

    protected function getCollection(): Collection
    {
        return $this->getDatabase()->selectCollection($this->config->collection);
    }
}
