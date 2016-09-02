<?php

namespace MakinaCorpus\ACL\Symfony;

use MakinaCorpus\ACL\EntryCollectorInterface;
use MakinaCorpus\ACL\EntryListBuilder;
use MakinaCorpus\ACL\Resource;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Collect entries using the Symfony event dispatcher
 */
class EventCollector implements EntryCollectorInterface
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
    public function supports(Resource $resource)
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
