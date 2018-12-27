[![Koseven Logo](https://i.imgur.com/2CeT8JL.png)](https://koseven.ga/)

[![Latest Stable Version](https://poser.pugx.org/koseven/koseven/v/stable)](https://packagist.org/packages/koseven/koseven)
[![Github Issues](http://githubbadges.herokuapp.com/koseven/koseven/issues.svg)](https://github.com/koseven/koseven/issues)
[![Pending Pull-Requests](http://githubbadges.herokuapp.com/koseven/koseven/pulls.svg)](https://github.com/koseven/koseven/pulls)
[![License](https://poser.pugx.org/koseven/koseven/license.svg)](https://packagist.org/packages/koseven/koseven)
[![Telegram](https://img.shields.io/badge/Telegram-joinChat-blue.svg)](https://telegram.me/koseven)
[![Build Status](https://travis-ci.org/koseven/koseven.svg?branch=devel)](https://travis-ci.org/koseven/koseven)
[![Coverage Status](https://coveralls.io/repos/github/koseven/koseven/badge.svg?branch=devel)](https://coveralls.io/github/koseven/koseven?branch=devel)

## [Download 3.3.8](https://github.com/koseven/koseven/releases/tag/3.3.8)

### [Join the Telegram group](https://telegram.me/koseven)
### [Join the Forum](https://gitq.com/koseven/koseven/)

Koseven is a PHP framework based on defunct [Kohana](http://kohanaframework.org/) 3.3.X . Fully compatible with Kohana and updated to work with PHP7

Koseven is an elegant, open source, and object oriented HMVC framework built using PHP7, by a team of volunteers. It aims to be swift, secure, and small.

Released under a [BSD license](LICENSE.md), Koseven can be used legally for any open source, commercial, or personal project.

## History/Why a Kohana alternative?

Kohana 3.3.x is used by us in many live projects, and the original team (where @neo22s belonged too) stopped the development a while ago and on Feb 4, 2017 Shadowhand announced the final retirement [Kohana is DEAD](http://discourse.kohanaframework.org/t/kohana-retirement-2017-07-01/1277).

Before the final announcement everyone started to check if it was possible to migrate existing projects to other alternative PHP frameworks. As it turned out switching would be a complex and lengthy job.
So it became clear that keeping the project alive and updated is a priority.

And so the Koseven repository was born and will keep this repository updated for future releases of PHP, giving the framework a clear perspective for the future.

## Will work as dropin of Kohana?

If you were using 3.3.x version you may need to make some small changes. Please refer to [upgrading from kohana](https://docs.koseven.ga/guide/kohana/upgrading-from-kohana) section of the documentation.

## What changes have you made?

So far is mostly as the last stable version of Kohana 3.3.6 released on Jul 25, 2016. But compatible with PHP 7.

## What are the future plans for the project?

Our focus is to keep the framework compatible with new releases of PHP, fix bugs and try to improve the speed. New features can be added using modules.

There is a dedicated [versions and roadmap](https://github.com/koseven/koseven/blob/devel/ROADMAP.md) page for the supported versions and plans.

## Are modules of the original Kohana compatible?

Yes they are, just be sure that they are compatible with Kohana 3.3.X. An overview of Koseven's team own modules can be found on [modules](https://koseven.ga/modules.html) page. There's also a list online with a nice overview of existing kohana modules maintained (or abandoned) by others. You can consult this list at [kohana-modules.com](https://kohana-modules.com)

### Why all modules in 1 repo?

This was personal choice of @neo22s to keep the project as simple and easier to manage. The modules are commonly used and are not enabled by default. If not used they can be removed from the codebase.

## I Need help!

Feel free to [open an issue on github](https://github.com/koseven/koseven/issues/new). Please be as specific as possible if you want to get help. You can also [Join the Telegram group](https://telegram.me/koseven) 

## Documentation

We are working to improve the original Kohana documentation but in the meantime feel free to use the one provided by Kohana.

Koseven documentation can be found at [docs.koseven.ga](https://docs.koseven.ga) which also contains an API browser.

The `userguide` module included in all Kohana/Koseven releases allows you to view the documentation locally. To use it you need to enable the`userguide` module in the bootstrap.php file (found in the `application` directory). Next you should be able to read the documentation from your own site via `/index.php/guide` (or just `/guide` if you are rewriting your URLs).

## Reporting bugs
If you've stumbled across a bug, please help us out by [reporting the bug](https://github.com/koseven/koseven/issues/new) you have found. Simply log in or register and submit a new issue, leaving as much information about the bug as possible, e.g.

* Steps to reproduce
* Expected result
* Actual result

This will help us to fix the bug as quickly as possible, and if you'd like to fix it yourself feel free to [fork us on GitHub](https://github.com/koseven) and submit a pull request!

## Reporting security vulnerabilities
Open an [issue](https://github.com/koseven/koseven/issues/new) on github and describe the problem as detailed as possible. Or do a pull request if you have a patch and describe the issue there.

## Contributing

Any help is more than welcome! Please see [Contributing](CONTRIBUTING.md)
