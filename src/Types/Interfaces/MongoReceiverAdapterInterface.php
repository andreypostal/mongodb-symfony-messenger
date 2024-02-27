<?php
namespace Andrey\Symfony\Messenger\Mongo\Types\Interfaces;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Cursor;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

interface MongoReceiverAdapterInterface extends MessageCountAwareInterface
{
    public function all(?int $limit = null): Cursor;
    public function find(ObjectId $id): ?array;
    public function getMessageCount(): int;
    public function getFromQueues(array $queueNames): Cursor;
    public function get(): Cursor;
    public function ack(ObjectId $id): void;
    public function reject(ObjectId $id): void;
}
