<?php
namespace Andrey\Symfony\Messenger\Mongo;

use Andrey\Symfony\Messenger\Mongo\Types\Interfaces\MongoTransportAdapterInterface;
use Andrey\Symfony\Messenger\Mongo\Types\Interfaces\MongoTransportInterface;
use Symfony\Component\Messenger\Envelope;

readonly class MongoTransport implements MongoTransportInterface
{
    public function __construct(
        private MongoTransportAdapterInterface $adapter,
        private MongoReceiver $receiver,
        private MongoSender $sender,
    ) { }

    public function get(): iterable
    {
        return $this->receiver->get();
    }

    public function ack(Envelope $envelope): void
    {
        $this->receiver->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        $this->receiver->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->sender->send($envelope);
    }

    public function setup(): void
    {
        $this->adapter->setup();
    }

    public function getMessageCount(): int
    {
        return $this->receiver->getMessageCount();
    }

    public function all(?int $limit = null): iterable
    {
        return $this->receiver->all($limit);
    }

    public function find(mixed $id): ?Envelope
    {
        return $this->receiver->find($id);
    }
}
