PecserkeUniversalMappings
=========================

[![Build Status](https://travis-ci.org/tomas-pecserke/UniversalMappings.png?branch=master)](https://travis-ci.org/tomas-pecserke/UniversalMappings)
[![Latest Stable Version](https://poser.pugx.org/pecserke/universal-mappings/v/stable.png)](https://packagist.org/packages/pecserke/universal-mappings)
[![Latest Unstable Version](https://poser.pugx.org/pecserke/universal-mappings/v/unstable.png)](https://packagist.org/packages/pecserke/universal-mappings)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5722bfed-59e5-4dd0-b9a3-041900d64b82/mini.png)](https://insight.sensiolabs.com/projects/5722bfed-59e5-4dd0-b9a3-041900d64b82)

This component allows  you to write your [Doctrine2](http://www.doctrine-project.org/)
model classes once, and define only mapping information for each backend
([ORM](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/),
[MongoDB ODM](http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/),
[CouchDB ODM](http://doctrine-orm.readthedocs.org/projects/doctrine-couchdb/en/latest/),
[PHPCR ODM](http://docs.doctrine-project.org/projects/doctrine-phpcr-odm/en/latest/)).

PecserkeUniversalMappings is intended for use in bundles, which provide some database-backed functionality
without forcing the backend, need to write model classes for each supported backend,
or forcing bundle's users to provide their own model class implementation.

*Note:* This component is not meant for use in final product bundles.
Those usually have no need of universal mappings.

Documentation
-------------

[Read the documentation](Resources/doc/index.md)

Installation
------------

Installation instructions are located in [documentation](Resources/doc/index.md#installation).

License
-------

This component is under the MIT license. See the complete license in the bundle:

[LICENSE](LICENSE)

About
-----

This component is inspired by solution originally introduced into
[FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle)
and written by [David Buchmann](https://github.com/dbu).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the
[GitHub issue tracker](https://github.com/tomas-pecserke/UniversalMappings/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
