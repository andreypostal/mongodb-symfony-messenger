<?php
namespace Andrey\Symfony\Messenger\Mongo\Types\Interfaces;

interface MongoSenderAdapterInterface
{
    public function send(array $message, ?int $delay): string;
}
