<?php
namespace Andrey\Symfony\Messenger\Mongo;

use Andrey\Symfony\Messenger\Mongo\Adapters\ReceiverAdapter;
use Andrey\Symfony\Messenger\Mongo\Adapters\SenderAdapter;
use Andrey\Symfony\Messenger\Mongo\Adapters\TransportAdapter;
use Andrey\Symfony\Messenger\Mongo\Types\Configuration;
use MongoDB\Client;
use SensitiveParameter;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class TransportFactory implements TransportFactoryInterface
{
    const SCHEME = 'mongo://';

    public function createTransport(
        #[SensitiveParameter] string $dsn,
        array $options,
        SerializerInterface $serializer,
    ): TransportInterface {
        $driverOptions = $options['driverOptions'] ?? [];
        $uriOptions = $options['uriOptions'] ?? [];
        unset($options['driverOptions'], $options['uriOptions']);

        $config = Configuration::unserialize($dsn, $options);

        $client = new Client($config->uri, $uriOptions, $driverOptions);
        $transport = new TransportAdapter($client, $config);

        $receiver = new MongoReceiver(
            adapter: new ReceiverAdapter($client, $config),
            serializer: $serializer,
        );

        $sender = new MongoSender(
            adapter: new SenderAdapter($client, $config),
            serializer: $serializer,
        );

        return new MongoTransport(
            $transport,
            $receiver,
            $sender,
        );
    }

    public function supports(#[SensitiveParameter] string $dsn, array $options): bool
    {
        return str_starts_with($dsn, self::SCHEME);
    }
}
