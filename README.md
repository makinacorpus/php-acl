# Simple ACL API for PHP

This is more a playground than a real API.

This API is not meant to store ACLs but to provide a dynamic at runtime API
to collect resources ACLs and to check for their validity.

## Structures

original: void
id: int|string
type: string
permission: string
profile: (type, id, ?object)
resource: (type, id, ?object)
entry: (profile, permission[], id) // ACE (Access Control Entry)
entrylist: (resource, entry[]) // ACL (Access Control List)

## Functions

voter(?type)
  - supports(resource)
  - vote(resource, profile)

entrystore(?type):
  - supports(resource) : boolean
  - save(entrylist)
  - load(resource) : entrylist
  - delete(resource)

collector(?type) // Integration with framework
  - collect(resource) : entrylist
  - supports(resource) : boolean

profileconverter: // Integration with framework
  - canConvertAsProfile(object) : boolean
  - asProfile(object) : profile

resourceconverter: // Integration with framework
  - canConvertAsResource(object) : boolean
  - asResource(object) : resource

aclvoter(?type) : voter(?type)
  - entrystore[]
  - collector[]
  - vote(resource, profile) : boolean

// Global ACL checker (no type)
manager: checker
  - aclvoter
  - voter[]
  - converter[]
  - vote(object, object, permission) // resource, profile, permission

## Assertions

Voters are taken first, ACL later
Voters VS ACL: who wins depends upon configuration (per type?)

ACE are not dynamic, and may be cached (wipe out when resources change)
  - this means, bloom or bitmask filters are usable

ACL are not dynamic, since they are composed of ACE
  - this means caching should happen here
