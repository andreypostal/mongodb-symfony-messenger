<?php
namespace Andrey\Symfony\Messenger\Mongo\Adapters;

use Andrey\Symfony\Messenger\Mongo\Types\Configuration;
use Andrey\Symfony\Messenger\Mongo\Types\Exceptions\MongoMessengerException;
use Andrey\Symfony\Messenger\Mongo\Types\Interfaces\MongoSenderAdapterInterface;
use Andrey\Symfony\Messenger\Mongo\Types\QueueItem;
use DateTimeImmutable;
use MongoDB\Client;

readonly class SenderAdapter extends BaseAdapter implements MongoSenderAdapterInterface
{
    public function __construct(Client $client, Configuration $config)
    {
        parent::__construct($client, $config);
    }

    /**
     * @throws MongoMessengerException
     */
    public function send(array $message, ?int $delay): string
    {
        $now = new DateTimeImmutable('UTC');
        $availableAt = $now->modify(sprintf('+%d seconds', ($delay ?? 0) / 1_000))->format('U');

        $queueItem = new QueueItem(
            null,
            $message['body'],
            $message['headers'] ?? [],
            $this->config->queueName,
            time(),
            $availableAt,
            null
        );

        $result = $this->getCollection()->insertOne($queueItem->toArray());
        if (!$result->getInsertedCount()) {
            throw new MongoMessengerException('Failed to insert the item to the queue.');
        }
        return (string) $result->getInsertedId();
    }
}
