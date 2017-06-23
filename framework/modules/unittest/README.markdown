# Kohana-PHPUnit integration

| ver   | Stable                                                                                                                               | Develop                                                                                                                                |
|-------|--------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| 3.3.x | [![Build Status - 3.3/master](https://travis-ci.org/kohana/unittest.svg?branch=3.3%2Fmaster)](https://travis-ci.org/kohana/unittest) | [![Build Status - 3.3/develop](https://travis-ci.org/kohana/unittest.svg?branch=3.3%2Fdevelop)](https://travis-ci.org/kohana/unittest) |
| 3.4.x | [![Build Status - 3.4/master](https://travis-ci.org/kohana/unittest.svg?branch=3.4%2Fmaster)](https://travis-ci.org/kohana/unittest) | [![Build Status - 3.4/develop](https://travis-ci.org/kohana/unittest.svg?branch=3.4%2Fdevelop)](https://travis-ci.org/kohana/unittest) |

This module integrates PHPUnit with Kohana and is used to run all the core Kohana tests. In most cases you will not
need to use this module for testing your own projects. If there are particular helpers provided here that you rely on,
that may be a sign that your own code is too closely coupled to the behaviour of the Kohana core classes.

If you look through any of the tests provided in this module you'll probably notice all theHorribleCamelCase.
I've chosen to do this because it's part of the PHPUnit coding conventions and is required for certain features such as auto documentation.

## Requirements and installation

Dependencies are listed in the composer.json - run `composer install` to install the module and all external requirements.
Note that more usually you will add this module to your own module's composer.json:

```json
{
  "require-dev": {
    "kohana/unittest": "3.3.*@dev"
  }
}
```

## Usage

	$ phpunit --bootstrap=modules/unittest/bootstrap.php modules/unittest/tests.php

Alternatively you can use a `phpunit.xml` to have a more fine grained control
over which tests are included and which files are whitelisted.

Make sure you only whitelist the highest files in the cascading filesystem, else
you could end up with a lot of "class cannot be redefined" errors.  

If you use the `tests.php` testsuite loader then it will only whitelist the
highest files. see `config/unittest.php` for details on configuring the
`tests.php` whitelist.
