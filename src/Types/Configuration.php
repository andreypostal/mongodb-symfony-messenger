<?php
namespace Andrey\Symfony\Messenger\Mongo\Types;

use SensitiveParameter;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;

use const FILTER_VALIDATE_BOOL;

final class Configuration
{
    private const DEFAULT_URI = 'mongodb://localhost:27017';
    private const DEFAULT_QUEUE_NAME = 'default';
    private const DEFAULT_DATABASE_NAME = 'mongo_symfony_messenger';
    private const DEFAULT_COLLECTION_NAME = 'items';
    private const DEFAULT_CAPPED_SIZE = 100 * 1024 * 1024; // 100mb

    public string $uri;
    public string $database;
    public string $collection;
    public string $queueName;
    public bool $tailable;
    public bool $autoSetup;
    public int $cappedSize;

    /**
     * @return array<string, string>
     */
    private function getOptions(): array
    {
         return array_reduce(
            array: array_keys(get_class_vars(self::class)),
            callback: function(array $list, string $current): array {
                $list[] = Helper::camelToSnake($current);
                return $list;
            },
            initial: [],
        );
    }

    /**
     * @param array $values
     *
     * @return bool
     */
    private function containsInvalidOptions(#[SensitiveParameter] array $values): bool
    {
        $options = $this->getOptions();
        $keyDiff = count(array_diff(array_keys($values), $options));

        return $keyDiff > 0;
    }

    /**
     * @param array $values
     *
     * @return void
     */
    private function load(#[SensitiveParameter] array $values): void
    {
        if ($this->containsInvalidOptions($values)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown options provided. Available options are [%s].',
                    implode(', ', $this->getOptions()),
                ),
            );
        }

        $this->uri = $values['uri'] ?? self::DEFAULT_URI;
        $this->database = $values['database'] ?? self::DEFAULT_DATABASE_NAME;
        $this->collection = $values['collection'] ?? self::DEFAULT_COLLECTION_NAME;
        $this->queueName = $values['queue_name'] ?? self::DEFAULT_QUEUE_NAME;
        $this->autoSetup = filter_var($values['autoSetup'] ?? true, FILTER_VALIDATE_BOOL);
        $this->tailable = filter_var($values['tailable'] ?? $this->autoSetup, FILTER_VALIDATE_BOOL);
        $this->cappedSize = filter_var($values['capped_size'] ?? self::DEFAULT_CAPPED_SIZE, FILTER_VALIDATE_INT);
    }

    /**
     * @param string $dsn
     * @param array $options
     *
     * @return self
     */
    public static function unserialize(#[SensitiveParameter] string $dsn, array $options): self
    {
        $urlInfo = parse_url($dsn);
        if ($urlInfo === false) {
            throw new InvalidArgumentException('Invalid DSN given.');
        }

        mb_parse_str($urlInfo['query'] ?? '', $query);
        $values = [ 'uri' => Helper::buildUri($urlInfo) ?: null, ] + $query + $options;

        $instance = new self;
        $instance->load($values);

        return $instance;
    }
}
