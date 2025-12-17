# Changelog

## Unreleased

- Unify public rest API names

## v0.3.27

- PublicRestApi: generalize API access and move it to AbstractApi
- PublicRestApi: add LectureshipApi and CourseRegistrationApi

## v0.3.26

- LegacyWebService: Return an array of arrays for the course contacts attribute for better serialization support

## v0.3.25

- Drop support for PHP 8.1
- Add basic API support for the Campusonline Public REST APIs (experimental)
- Add support for the Public REST API 'rooms' (experimental)
- Add support for the Public REST API 'organizations' (experimental)
- Add support for the Public REST API 'courses' (experimental)
- Allow kevinrob/guzzle-cache-middleware v7

## v0.3.24

- Try to escape illegal control characters in JSON string returned by the CO export API in case the decode fails

## v0.3.23

- Add FORCE_CACHE_MISS option which ignores a cache hit and forces a re-request of the resources from CO

## v0.3.22

- Add support for kevinrob/guzzle-cache-middleware v6
- dev: drop psalm
- dev: update to phpstan v2

## v0.3.21

- Add organizations attribute to rooms data

## v0.3.20

- Fix get course by ID
- Refactor and modernize

## v0.3.19

- Port to PHPUnit 10
- Undeprecate UCardApi/StudentApi
- GenericApi: add support for passing a language parameter to the API
- LegacyWebService: silence some XML parsing PHP warnings in case of invalid responses

## v0.3.18

- Add new FilterBuilder::extractValidFilterSubstrings() for extracting substrings from a user specified
  input string which are all valid filter values. This can be used to implement a search with user specified inputs.

## v0.3.17

- GenericApi: allow numbers in filter keys

## v0.3.16

- Loosen some constraints on dependencies

## v0.3.15

- Add some missing direct dependency requirements

## v0.3.14

- Drop support for PHP 7.4/8.0

## v0.3.13

- Drop support for PHP 7.3

## v0.3.9

- Support kevinrob/guzzle-cache-middleware v5

## v0.3.8

- Deprecate ResearchProject in favor of GenericAPI
- GenericApi::getResource(): better handle CO returning bogus results for non-integer queries for integer fields
  It now returns null instead of failing and/or fetching all available resources and running out of memory.

## v0.3.7

- Added a new FilterBuilder class for building filter expressions. Deprecated the old filter code.

## v0.3.6

- Added a new GenericApi class for generic data exports.

## v0.2.18

- ResourceApi subclasses gained a checkConnection() method which makes sure the API is reachable and the access token is valid

## v0.2.13

- added OrganizationUnitApi::getOrganizationUnitsById() for more efficiently fetching multiple organizations.
