<?php

namespace MakinaCorpus\ACL\Collector;

class CallbackEntryCollector implements EntryCollectorInterface
{
    private $callback;
    private $types = [];
    private $permissions = [];

    /**
     * Default constructor
     *
     * @param callable $callback
     */
    public function __construct(callable $callback, array $types = [], array $permissions = [])
    {
        $this->callback = $callback;
        $this->types = $types ? array_flip($types) : [];
        $this->permissions = $permissions ? array_flip($permissions) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type, $permission)
    {
        return (!$this->types || isset($this->types[$type])) && (!$this->permissions || isset($this->permissions[$permission]));
    }

    /**
     * {@inheritdoc}
     */
    public function collectEntryLists(EntryListBuilderInterface $builder)
    {
        call_user_func($this->callback, $builder);
    }
}
