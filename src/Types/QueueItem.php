<?php
namespace Andrey\Symfony\Messenger\Mongo\Types;

use MongoDB\BSON\ObjectId;

final readonly class QueueItem
{
    public function __construct(
        public ?ObjectId $_id,
        public string $body,
        public array $headers,
        public string $queueName,
        public int $createdAt,
        public int $availableAt,
        public ?int $deliveredAt,
    ) { }

    public function withId(ObjectId $id): self
    {
        return new self(
            $id,
            $this->body,
            $this->headers,
            $this->queueName,
            $this->createdAt,
            $this->availableAt,
            $this->deliveredAt,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->_id,
            'body' => $this->body,
            'headers' => $this->headers,
            'queueName' => $this->queueName,
            'createdAt' => $this->createdAt,
            'availableAt' => $this->availableAt,
            'deliveredAt' => $this->deliveredAt,
        ];
    }
}
