<?php

namespace MakinaCorpus\ACL\Collector;

class CallbackEntryCollector implements EntryCollectorInterface
{
    private $callback;

    /**
     * Default constructor
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(EntryListBuilderInterface $builder)
    {
        call_user_func($this->callback, $builder);
    }
}
