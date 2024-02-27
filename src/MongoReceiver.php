<?php
namespace Andrey\Symfony\Messenger\Mongo;

use Andrey\Symfony\Messenger\Mongo\Types\Interfaces\MongoReceiverAdapterInterface;
use Andrey\Symfony\Messenger\Mongo\Types\MongoStamp;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\QueueReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

readonly class MongoReceiver implements ListableReceiverInterface, QueueReceiverInterface, MessageCountAwareInterface
{
    public function __construct(
        private MongoReceiverAdapterInterface $adapter,
        private SerializerInterface $serializer = new PhpSerializer(),
    ) { }

    public function get(): iterable
    {
        $cursor = $this->adapter->get();
        while ($cursor->valid() && !$cursor->isDead()) {
            yield $this->envelope(
                document: $cursor->current(),
            );

            $cursor->next();
        }
    }

    public function ack(Envelope $envelope): void
    {
        $this->adapter->ack(
            id: $this->getEnvelopeId($envelope)
        );
    }

    public function reject(Envelope $envelope): void
    {
        $this->adapter->reject(
            id: $this->getEnvelopeId($envelope)
        );
    }

    public function getMessageCount(): int
    {
        return $this->adapter->getMessageCount();
    }

    public function getFromQueues(array $queueNames): iterable
    {
        $cursor = $this->adapter->getFromQueues($queueNames);
        while ($cursor->valid() && !$cursor->isDead()) {
            yield $this->envelope(
                document: $cursor->current(),
            );

            $cursor->next();
        }
    }

    public function all(?int $limit = null): iterable
    {
        return $this->adapter->all($limit);
    }

    public function find(mixed $id): ?Envelope
    {
        return $this->envelope(
            document: $this->adapter->find(new ObjectId($id)),
        );
    }

    private function stamp(Envelope $envelope, string $id): Envelope
    {
        return $envelope->with(
            new MongoStamp($id),
            new TransportMessageIdStamp($id),
        );
    }

    private function envelope(array $document): Envelope
    {
        $id = ($document['_id'] instanceof ObjectId) ? $document['_id'] : new ObjectId($document['_id']);

        try {
            return $this->stamp(
                envelope: $this->serializer->decode([
                    $document['headers'],
                    $document['body'],
                ]),
                id: (string) $id,
            );
        } catch (MessageDecodingFailedException $e) {
            $this->adapter->reject($id);
            throw $e;
        }
    }

    private function getEnvelopeId(Envelope $envelope): ObjectId
    {
        /** @var MongoStamp|null $stamp */
        $stamp = $envelope->last(MongoStamp::class);
        if ($stamp === null) {
            throw new LogicException('MongoDb stamp not found on the envelope.');
        }
        return $stamp->getId();
    }
}
