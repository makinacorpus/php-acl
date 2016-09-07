# Simple ACL API for PHP

This a playground/sandbox project.

This API is not meant to store ACLs but to provide a dynamic at runtime API
to collect resources ACLs and to check for their validity.

I do have lots to document here.

## Data types

original: void
id: int|string
type: string
permission: string
profile: (type, id, ?object)
resource: (type, id, ?object)
entry: (profile, permission[], id) // ACE (Access Control Entry)
entrylist: (resource, entry[]) // ACL (Access Control List)

## Objects

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

converter: // Integration with framework
  - supportsResource(object) : boolean
  - supportsProfile(object) : boolean
  - asResource(object) : resource
  - asProfile(object) : profile

dynamicaclvoter(?type) : voter(?type)
  - entrystore[]
  - collector[]
  - vote(resource, profile) : boolean

// Global ACL checker (no type)
manager: checker
  - voter[]
  - converter[]
  - vote(object, object, permission) // resource, profile, permission

## Role/group/user structures

rolepermission: string
role: profile(type=role, rolepermission[])
user: profile(type=user)
group: profile(type=group)
user_role(group,user,role)

## Assertions

Voters are taken first, ACL later
Voters VS ACL: who wins depends upon configuration (per type?)

ACE are not dynamic, and may be cached (wipe out when resources change)
  - this means, bloom or bitmask filters are usable

ACL are not dynamic, since they are composed of ACE
  - this means caching should happen here

