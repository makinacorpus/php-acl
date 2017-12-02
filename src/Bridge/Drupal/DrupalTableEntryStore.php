<?php

namespace MakinaCorpus\ACL\Bridge\Drupal;

use MakinaCorpus\ACL\Collector\EntryListBuilder;
use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\PermissionMap;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\ResourceCollection;
use MakinaCorpus\ACL\Store\EntryStoreInterface;
use MakinaCorpus\ACL\Permission;

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
    static public function getDefaultTableSchema()
    {
        return [
            'fields' => [
                'resource_id' => [
                    'type'      => 'int',
                    'unsigned'  => true,
                    'not null'  => true,
                ],
                'profile_type' => [
                    'type'      => 'varchar',
                    'length'    => 255,
                    'not null'  => true,
                ],
                'profile_id' => [
                    'type'      => 'varchar',
                    'length'    => 255,
                    'not null'  => true,
                ],
                // Optimization for the the most basics operations which are
                // the one that will be needed in 99.99% percent of the time:
                // you will probably never need to restricte SQL queries with
                // arbitrary permissions such as "lock", "share" or others.
                //
                // @todo sorry, PostgreSQL type should be boolean here, but
                //   drupal being drupal, and its PG support being awful, we
                //   are just going to use integers instead
                'can_view' => [
                    'type'      => 'int',
                    'unsigned'  => true,
                    'not null'  => true,
                    'default'   => 0,
                    'size'      => 'tiny',
                ],
                'can_update' => [
                    'type'      => 'int',
                    'unsigned'  => true,
                    'not null'  => true,
                    'default'   => 0,
                    'size'      => 'tiny',
                ],
                'can_delete' => [
                    'type'      => 'int',
                    'unsigned'  => true,
                    'not null'  => true,
                    'default'   => 0,
                    'size'      => 'tiny',
                ],
                // Permissions is just an arbitrary textual reprensentation of
                // permissions which is not supposed to be queried or indexed,
                // it exists in order to be able for select queries to have a
                // human readable result, or to be able to potentially rebuild
                // broken bitmasks.
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
    private $permissionMap;

    /**
     * Default constructor
     */
    public function __construct(\DatabaseConnection $database, $table, $resourceType, PermissionMap $permissionMap = null)
    {
        $this->database = $database;
        $this->table = $table;
        $this->permissionMap = $permissionMap ? $permissionMap : new PermissionMap();
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * Get default table schema
     *
     * @return array
     *   Schema suitable for the Drupal Schema API
     */
    public function getTableSchema()
    {
        return self::getDefaultTableSchema();
    }

    /**
     * Alter Drupal query using the configured table
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
    public function supports($type, $permission)
    {
        return $type === $this->type && $this->permissionMap->supports($permission);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsType($type)
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
    public function deleteAll(ResourceCollection $resources)
    {
        throw new \Exception("Not implemented yet");
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

        $builder = new EntryListBuilder($resource);

        foreach ($rows as $row) {
            $builder->add($row->profile_type, $row->profile_id, explode(',', $row->permissions));
        }

        return $builder->convertToEntryList();
    }

    /**
     * {@inheritdoc}
     */
    public function loadAll(ResourceCollection $resources)
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function save(Resource $resource, EntryList $list)
    {
        $query = $this->database->insert($this->table);
        $query->fields(['resource_id', 'profile_type', 'profile_id', 'can_view', 'can_update', 'can_delete', 'permissions', 'bitmask']);

        $resourceId = $resource->getId();

        foreach ($list->getEntries() as $entry) {
            $profile = $entry->getProfile();

            $query->values([
                $resourceId,
                $profile->getType(),
                $profile->getId(),
                implode(',', $entry->getPermissions()),
                (int)$entry->hasPermission(Permission::VIEW),
                (int)$entry->hasPermission(Permission::UPDATE),
                (int)$entry->hasPermission(Permission::DELETE),
                implode(',', $entry->getPermissions()),
                0, // @todo bitmask
            ]);
        }

        // @todo delete/insert is innefficient
        $this->delete($resource);
        $query->execute();
    }
}
