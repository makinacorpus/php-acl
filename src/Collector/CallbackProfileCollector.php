<?php

namespace MakinaCorpus\ACL\Collector;

class CallbackProfileCollector implements ProfileCollectorInterface
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
    public function collect(ProfileSetBuilder $builder)
    {
        call_user_func($this->callback, $builder);
    }
}
