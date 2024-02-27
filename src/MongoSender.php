<?php
namespace Andrey\Symfony\Messenger\Mongo;

use Andrey\Symfony\Messenger\Mongo\Types\Interfaces\MongoSenderAdapterInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

readonly class MongoSender implements SenderInterface
{
    public function __construct(
        private MongoSenderAdapterInterface $adapter,
        private SerializerInterface $serializer = new PhpSerializer(),
    ) { }

    public function send(Envelope $envelope): Envelope
    {
        $encodedMessage = $this->serializer->encode($envelope);

        /** @var DelayStamp|null $delayStamp */
        $delayStamp = $envelope->last(DelayStamp::class);
        $delay = $delayStamp?->getDelay();

        $id = $this->adapter->send($encodedMessage, $delay);

        return $envelope->with(new TransportMessageIdStamp($id));
    }
}
