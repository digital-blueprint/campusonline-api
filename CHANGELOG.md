# v0.3.8

* Deprecate ResearchProject in favor of GenericAPI
* GenericApi::getResource(): better handle CO returning bogus results for non-integer queries for integer fields
  It now returns null instead of failing and/or fetching all available resources and running out of memory.

# v0.3.7

* Added a new FilterBuilder class for building filter expressions. Deprecated the old filter code.

# v0.3.6

* Added a new GenericApi class for generic data exports.

# v0.2.18

* ResourceApi subclasses gained a checkConnection() method which makes sure the API is reachable and the access token is valid

# v0.2.13

* added OrganizationUnitApi::getOrganizationUnitsById() for more efficiently fetching multiple organizations.
