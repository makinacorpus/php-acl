<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Collect entries using the Symfony event dispatcher
 */
class EventProfileCollector implements ProfileCollectorInterface
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
    public function supports($object)
    {
        return $this->dispatcher->hasListeners(CollectProfileEvent::EVENT_COLLECT);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ProfileSetBuilder $builder)
    {
        $event = new CollectProfileEvent($builder);
        $this->dispatcher->dispatch(CollectProfileEvent::EVENT_COLLECT, $event);
    }
}
