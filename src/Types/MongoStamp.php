<?php
namespace Andrey\Symfony\Messenger\Mongo\Types;

use MongoDB\BSON\ObjectId;
use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

class MongoStamp implements NonSendableStampInterface
{
    private ObjectId $id;

    public function __construct(string|ObjectId $id)
    {
        $this->id = $id instanceof ObjectId ? $id : new ObjectId($id);
    }

    public function getId(): ObjectId
    {
        return $this->id;
    }
}
