<?php
namespace Andrey\Symfony\Messenger\Mongo\Adapters;

use Andrey\Symfony\Messenger\Mongo\Types\Configuration;
use Andrey\Symfony\Messenger\Mongo\Types\Interfaces\MongoReceiverAdapterInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Driver\Cursor;
use MongoDB\Operation\Find;

readonly class ReceiverAdapter extends BaseAdapter implements MongoReceiverAdapterInterface
{
    private const MAX_AWAIT_TIME = 100;

    public function __construct(Client $client, Configuration $config)
    {
        parent::__construct($client, $config);
    }

    public function all(?int $limit = null): Cursor
    {
        return $this->getCollection()->find(
            [
                'deliveredAt' => null,
                'queueName' => $this->config->queueName,
            ],
            [
                'limit' => $limit,
            ],
        );
    }

    public function find(ObjectId $id): ?array
    {
        return $this->getCollection()->findOne([
            '_id' => $id,
            'deliveredAt' => null,
            'queueName' => $this->config->queueName,
        ]);
    }

    public function getMessageCount(): int
    {
        return $this->getCollection()->countDocuments([
            'queueName' => $this->config->queueName,
            'deliveredAt' => null,
        ]);
    }

    public function getFromQueues(array $queueNames): Cursor
    {
        return $this->getCollection()->find(
            filter: [
                'queueName' => [
                    '$in' => $queueNames,
                ],
                'availableAt' => [
                    '$gte' => time(),
                ],
                'deliveredAt' => null,
            ],
            options: [
                'cursorType' => $this->config->tailable ? Find::TAILABLE_AWAIT : Find::NON_TAILABLE,
                'sort' => [
                    'available_at' => 1,
                ],
                'maxAwaitTimeMS' => self::MAX_AWAIT_TIME,
            ],
        );
    }

    public function get(): Cursor
    {
        return $this->getCollection()->find(
            filter: [
                'queueName' => $this->config->queueName,
                'deliveredAt' => null,
                'availableAt' => [
                    '$gte' => time(),
                ],
            ],
            options: [
                'cursorType' => $this->config->tailable ? Find::TAILABLE_AWAIT : Find::NON_TAILABLE,
                'sort' => [
                    'available_at' => 1,
                ],
                'maxAwaitTimeMS' => self::MAX_AWAIT_TIME,
            ],
        );
    }

    public function ack(ObjectId $id): void
    {
        $this->getCollection()->updateOne(
            filter: [
                '_id' => $id,
            ],
            update: [
                '$set' => [
                    'deliveredAt' => time(),
                ],
            ],
        );
    }

    public function reject(ObjectId $id): void
    {
        $this->getCollection()->updateOne(
            filter: [
                '_id' => $id,
            ],
            update: [
                '$set' => [
                    'deliveredAt' => -1,
                ],
            ],
        );
    }
}
