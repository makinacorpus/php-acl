# Simple ACL API for PHP

**This a playground/sandbox project.**

This API is not meant to store ACLs but to provide a dynamic at runtime API
to collect resources ACLs and to check for their validity. I do have lots to
document here.

## History

Simple ACL API for PHP projects. One could ask why anyone would write another
one which is a legitimate question, aside of the fact this was originally a
purely educational project, it is today a full fledge API for many reasons:

 *  during educational researches that laid to this API, it appeared that most
    existing ACL API don't fully embrace the correct concepts naming;

 *  it is framework independent, despite the fact that you may need quite some
    time to set it up without framework-driven service dependency management;

 *  it has been development with performance as a first-class constraint;

 *  it is storage backend independent, nevertheless it can be used without any
    storage, plugged on dynamic event-driven ACL builder: this means you can
    use the API to programatically define ACLs in a reproducible way at runtime
    the same way you would implement access rights voters.

The very first use-case this library was born from is to replace the Drupal
*Node Access* API with something that would be generic enough to be used from
anything else than actual *Drupal nodes*, but yet to be compatible with the
Drupal node access API. Drupal node access is a full ACL system but without any
common lexicon which makes it hard to understand: *profiles* become *grants*,
*profile types* become *realms*, permissions are restricted to *view*, *update*
and *delete*, and entries are *node access records*.

The second use-case this library aims to solve is to be usage throught the
*Symfony's Security component*. This component is deeply integrated into most
of Symfony framework, providing a facade for controllers and templates,
rewriting this would be a pure loss of time whereas integrating it using the
*voters* system makes it transparently usable within the Symfony framework.

In definitive, this API was wrote to be framework independent, naturally
comprehensible, well performing, and usable in an environment where Drupal
and Symfony both live in the same runtime transparently.

## Concepts

Here is the basis ACL thesaurus:

 *  a user in a computer system is defined by its identity, but may have many
    **profiles** (a *profile set*);

 *  a **profile** has a type (e.g. *group*, *user*, ...) and an identifier. For
    example *John Smith* is the *user 12* and belongs to the *admin group*, he
    then as the following *profiles*: *(user, 12)* and *(group, admin)*;

 *  a **resource** is a business object that does not have any meaning in this
    API, it is identified with a type (e.g. *page*, *file*, ...) and an
    identifier. For example working with a blog site, you could have the
    *(blog_entry, 12)* resource;

 *  this API allows external plugins to attach **entries** to any resource;

 *  an **entry** (or ACE in common lexicon) is is defined by a single profile
    and a set of permissions given to this profile, for example
    *(group, admin) -> (read, write, delete)* is a valid entry;

 *  **entries** are grouped together for a single resource are named
    **entry lists** (or ACL in the common lexicon).

Most ACL API's got it right, at the exception of the *profile* concept that is
oftenly forgotten for the benefit of framework driven concepts (role, user,
...).

# Usage

## Collecting user profile

## Basic usage

## Defining entries
