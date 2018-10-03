# Developing locally

Clone the repo and develop on the Devel branch. Each release will have its own Tag.

I know its simple but for now will work ;)


# Contributing to the project

All features and bugfixes must be fully tested and reference an issue in  [GitHub](https://github.com/koseven/koseven/issues), **there are absolutely no exceptions**.

It's highly recommended that you write/run unit tests during development as it can help you pick up on issues early on.  See the Unit Testing section below.

## Cloning repo
Go to https://github.com/koseven/koseven for each repo in the top right theres a button that says Fork. Click there to clone each repo. That will copy the repos to your github user, ex: https://github.com/neo22s/koseven

Clone your project in local and use devel branch
```
git clone git@github.com:neo22s/koseven.git
cd koseven
git checkout devel
```

This will clone the koseven project

Ready ;)

## How to commit
If you have made modifications to the code.

```
git status # to see what's going on
git commit -a -m 'message here, this will commit the changes on the tracked files'
git push origin devel # will "upload" the changes to your repo
```

Tricks
```
git add . # will add all the files, even new ones
git add -u # will add all the tracked files even the deleted ones
git commit -a -m 'working closed etc  #725' # this will commit and mention an issue in the repo
```


## Pull Requests

Now you have new code at your fork ex https://github.com/neo22s/koseven. To move them to the original https://github.com/koseven/koseven repo you need to go to https://github.com/neo22s/koseven, and click on Pull Request (next to compare). This will create a pull request to the original code and the responsible will decide to merge it or not.

Notes:
- Try to submit pull requests against devel branch for easier merging
- Try not to pollute your pull request with unintended changes--keep them simple and small
- Try to share which browsers your code has been tested in before submitting a pull request

## Keep sync with original repo
First time, add a remote with the upstream
```
git remote add upstream https://github.com/koseven/koseven.git
```

Everytime you want to sync just
```
git fetch upstream
git merge upstream/devel
```

Remember to be at you devel branch!



## Bug fixing 

Make a PR with the fix, explain as in detail as possiblle.

## Tagging releases

Tag names should be prefixed with a `v`, this helps to separate tag references from branch references in Git.

For example, if you were creating a tag for the `3.1.0` release the tag name would be `v3.1.0`


# Unit Testing

Koseven currently uses PHPUnit for unit testing. This is installed with composer.

## How to run the tests

 * Install [Composer](http://getcomposer.org)
 * Run `php composer.phar install` from the root of this repository
 * Finally, run `vendor/bin/phpunit --bootstrap=modules/unittest/bootstrap.php modules/unittest/tests.php`

This will run the unit tests for core and all the modules and tell you if anything failed. If you haven't changed anything and you get failures, please create a new issue on  and paste the output (including the error) in the issue. Please note that a few tests only pass on linux systems.
