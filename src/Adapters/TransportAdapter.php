<?php
namespace Andrey\Symfony\Messenger\Mongo\Adapters;

use Andrey\Symfony\Messenger\Mongo\Types\Configuration;
use Andrey\Symfony\Messenger\Mongo\Types\Interfaces\MongoTransportAdapterInterface;
use MongoDB\Client;
use MongoDB\Model\CollectionInfo;

readonly class TransportAdapter extends BaseAdapter implements MongoTransportAdapterInterface
{
    public function __construct(Client $client, Configuration $config)
    {
        parent::__construct($client, $config);
        $this->setup();
    }

    public function setup(): void
    {
        if (!$this->config->autoSetup) {
            return;
        }
        $this->config->autoSetup = false;

        $collections = $this->getDatabase()->listCollections([
            'nameOnly' => true,
        ]);

        $collectionExists = array_reduce(
            array: iterator_to_array($collections),
            callback: function(bool $exists, CollectionInfo $info): bool {
                return $exists || $info->getName() === $this->config->collection;
            },
            initial: false,
        );

        if (!$collectionExists) {
            $options = $this->config->tailable ? [
                'capped' => $this->config->tailable,
                'size' => $this->config->cappedSize,
            ] : [];

            $this->getDatabase()->createCollection(
                $this->config->collection,
                $options,
            );
        }

        $this->getCollection()->createIndexes([
            [ 'key' => [ 'queue_name' => 'text' ], ],
            [ 'key' => [ 'created_at' => 1, ], ],
            [ 'key' => [ 'available_at' => 1, ], ],
            [ 'key' => [ 'delivered_at' => 1, ], ],
        ]);
    }
}
