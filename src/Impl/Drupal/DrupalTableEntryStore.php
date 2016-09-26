<?php

namespace MakinaCorpus\ACL\Impl\Drupal;

use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Store\EntryStoreInterface;
use MakinaCorpus\ACL\Entry;
use MakinaCorpus\ACL\Profile;

/**
 * This implementation will only allow a "view" permission for queries.
 *
 * It also implies that your resources have numerical identifiers to make
 * query faster; for all others cases, you may not use this implementation.
 */
class DrupalTableEntryStore implements EntryStoreInterface
{
    /**
     * Get the Drupal table schema
     *
     * @return array
     */
    static public function getTableSchema()
    {
        return [
            'fields' => [
                'resource_id' => [
                    'type'      => 'int',
                    'unsigned'  => true,
                    'not null'  => true,
                    'default'   => 0,
                ],
                'profile_type' => [
                    'type'      => 'varchar',
                    'length'    => 255,
                    'not null'  => true,
                    'default'   => '',
                ],
                'profile_id' => [
                    'type'      => 'int',
                    'unsigned'  => true,
                    'not null'  => true,
                    'default'   => 0,
                ],
                'can_view' => [
                    'type'      => 'int',
                    'unsigned'  => true,
                    'not null'  => true,
                    'default'   => 0,
                    'size'      => 'tiny',
                ],
                'permissions' => [
                    'type'      => 'text',
                    'not null'  => true,
                    'default'   => '',
                ],
                'bitmask' => [
                    'type'      => 'int',
                    'size'      => 'big', // 64 bits = 64 possible permissions
                    'not null'  => true,
                    'default'   => 0,
                ],
            ],
            'primary key' => ['resource_id', 'profile_type', 'profile_id'],
        ];
    }

    private $database;
    private $table;
    private $type;
    private $viewPermission;
    private $tablesToJoin = [];

    /**
     * Default constructor
     *
     * @param \DatabaseConnection $database
     *   Drupal database connection
     * @param string $table
     *   Table name in which to store
     * @param string $type
     *   Resource type this store supports
     * @param string[] $tablesToJoin
     *   For query alteration support, you need to provide a list of tables to
     *   join for access queries, keys are table names, values are the primary
     *   identifier column name
     */
    public function __construct(
        \DatabaseConnection $database,
        $table,
        $type = null,
        $viewPermission = Permission::VIEW,
        array $tablesToJoin = []
    ) {
        $this->database = $database;
        $this->table = $table;
        $this->type = $type;
        $this->viewPermission = $viewPermission;
        $this->tablesToJoin = [];
    }

    /**
     * Alter Drupal query using the configured table
     *
     * @throws \Exception
     */
    public function alterQuery()
    {
        if (!$this->tablesToJoin) {
            return;
        }

        // @todo port drupal node access query alteration
        throw new \Exception("Not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return $type === $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Resource $resource)
    {
        if (!$this->supports($resource->getType())) {
            throw new \LogicException(sprintf("this implementation does not supports resource with type %s", $resource->getType()));
        }

        $this->database->query("DELETE FROM {" . $this->table . "} WHERE resource_id = ?", [$resource->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(Resource $resource)
    {
        if (!$this->supports($resource->getType())) {
            throw new \LogicException(sprintf("this implementation does not supports resource with type %s", $resource->getType()));
        }

        $rows = $this->database->query("SELECT * FROM {" . $this->table . "} WHERE resource_id = ?", [$resource->getId()]);
        $entries = [];

        foreach ($rows as $row) {
            $entries[] = new Entry(new Profile($row->profile_type, $row->profile_id), explode(',', $row->permissions));
        }

        return new EntryList($resource, $entries);
    }

    /**
     * {@inheritdoc}
     */
    public function save(EntryList $list)
    {
        $query = $this->database->insert($this->table);
        $query->fields(['resource_id', 'profile_type', 'profile_id', 'can_view', 'permissions', 'bitmask']);

        $resource = $list->getResource();
        $resourceId = $resource->getId();

        foreach ($list->getEntries() as $entry) {
            $profile = $entry->getProfile();

            $query->values([
                $resourceId,
                $profile->getType(),
                $profile->getId(),
                implode(',', $entry->getPermissions()),
                (int)$entry->hasPermission($this->viewPermission),
                0, // @todo bitmask?
            ]);
        }

        $this->delete($resource);
        $query->execute();
    }
}
