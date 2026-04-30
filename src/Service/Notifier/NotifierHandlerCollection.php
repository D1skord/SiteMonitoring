<?php

namespace App\Service\Notifier;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class NotifierHandlerCollection
{
    private readonly array $handlers;

    /**
     * @param iterable<object> $handlers
     */
    public function __construct(
        #[AutowireIterator('notifier.handler')]
        iterable $handlers
    )
    {
        $this->handlers = $handlers instanceof \Traversable ? iterator_to_array($handlers) : $handlers;
    }

    public function getHandlerByName(string $name): ?NotifierHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->getName() == $name) {
                return $handler;
            }
        }

        return null;
    }

    public function getHandlerByNamespace(string $namespace): ?NotifierHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if (get_class($handler) == preg_replace('#/+#', '/', $namespace)) {
                return $handler;
            }
        }

        return null;
    }

    public function getListHandlers(): array
    {
        foreach ($this->handlers as $item)
            $arrayListHandlers[($item)->getName()] = $item::class;

        return $arrayListHandlers ?? [];
    }
}