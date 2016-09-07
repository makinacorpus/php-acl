<?php

namespace MakinaCorpus\ACL\Impl\Drupal;

use MakinaCorpus\ACL\Store\EntryStoreInterface;

class DrupalTableEntryStore implements EntryStoreInterface
{
    static public function getTableSchema()
    {

    }

    private $database;
    private $table;

    /**
     * Default constructor
     *
     * @param \DatabaseConnection $database
     *   Drupal database connection
     * @param string $table
     *   Table name in which to store
     */
    public function __construct(\DatabaseConnection $database, $table)
    {

    }
}
