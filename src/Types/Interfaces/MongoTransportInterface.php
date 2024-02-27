<?php
namespace Andrey\Symfony\Messenger\Mongo\Types\Interfaces;

use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

interface MongoTransportInterface extends TransportInterface, SetupableTransportInterface, MessageCountAwareInterface, ListableReceiverInterface
{ }
