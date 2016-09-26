<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Collector\EntryCollectorInterface;
use MakinaCorpus\ACL\Collector\EntryListBuilder;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Collect entries using the Symfony event dispatcher
 */
class EventEntryCollector implements EntryCollectorInterface
{
    private $dispatcher;

    /**
     * Default constructor
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return $this->dispatcher->hasListeners(CollectEntryEvent::EVENT_COLLECT);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(EntryListBuilder $builder)
    {
        $event = new CollectEntryEvent($builder);
        $this->dispatcher->dispatch(CollectEntryEvent::EVENT_COLLECT, $event);
    }
}
