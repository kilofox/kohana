# Developing locally

Kohana uses the [Gitflow workflow](https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow) since version 3.4.

## Branch name meanings

 - **master** - The `master` branch is for releases. Only release merge commits can be applied to this branch. You should never make a non-merge commit to this branch, and all merge commits should come from the release branch or hotfix branch. This branch lasts forever.
 - **develop** - The `develop` branch serves as an integration branch for features. This branch lasts forever.
 - **feature/*** - Feature branches are used to develop new features for the upcoming or a distant future release. This branch should be branched from the `develop` branch only. It should be merged back into `develop` when a feature is complete. This branch is deleted after it's done.
 - **release/*** - Release branches are for maintenance work before a release. This branch should be branched from the `develop` branch only. Change the version number here, and apply any other maintenance items needed before actually releasing. Merges from `master` should only come from this branch. It should be merged back into `develop` when it's complete as well. This branch is deleted after it's done.
 - **hotfix/*** - Hotfix branches are for emergency maintenance after a release. If an important security or other kind of important issue is discovered after a release, it should be done here. This branch should be created from `master` and merged back into `master` and `develop` when complete. This branch is deleted after it's done.

To work on the project you'd do the following:

```
$ git clone git://github.com/kilofox/kohana.git
...
$ cd kohana
...
$ git checkout develop
Switched to branch 'develop'
```

# Contributing to the project

All features and bugfixes must be fully tested and reference an issue in [GitHub](https://github.com/kilofox/kohana/issues), **there are absolutely no exceptions**.

It's highly recommended that you write/run unit tests during development as it can help you pick up on issues early on. See the Unit Testing section below.

## Creating new features

New features should be developed in separate branches so as to isolate them until they're stable.

**Features without tests written will be rejected! There are NO exceptions.**

The naming convention for feature branches is:

`feature/{issue number}-{short hyphenated description}`

e.g.

`feature/4045-rewriting-config-system`

When a new feature is complete and fully tested it can be merged into its respective release branch using
`git pull --no-ff`. The `--no-ff` switch is important as it tells Git to always create a commit detailing what branch you're merging from. This makes it a lot easier to analyse a feature's history.

Here's a quick example:

```
$ git status
On branch feature/4045-rewriting-everything
$ git checkout develop
Switched to branch 'develop'
$ git merge --no-ff feature/4045-rewriting-everything
```

**If a change you make intentionally breaks the API then please correct the relevant tests before pushing!**

## Bug fixing

If you're making a bugfix then before you start create a unit test which reproduces the bug, using the `@ticket` notation in the test to reference the bug's issue number (e.g. `@ticket 4045` for issue #4045).

If you run the unit tests then the one you've just made should fail.

Once you've written the bugfix, run the tests again before you commit to make sure that the fix actually works, then commit both the fix and the test.

**Bug fixes without tests written will be rejected! There are NO exceptions.**

There is no need to create separate branches for bugfixes, creating them in the main `develop` branch is perfectly acceptable.

## Tagging releases

Tag names should be prefixed with a `v`.

For example, if you were creating a tag for the `3.3.6` release the tag name would be `v3.3.6`.

# Merging changes from remote repositories

Now that you have a remote repository, you can pull changes in the remote "kohana" repository into your local repository:

    $ git pull kohana master

**Note:** Before you pull changes you should make sure that any modifications you've made locally have been committed.

Sometimes a commit you've made locally will conflict with one made in the remote "kohana" repo. There are a couple of scenarios where this might happen:

##### The conflict is due to a few unrelated commits and you want to keep changes made in both commits

You'll need to manually modify the files to resolve the conflict, see the "Basic Merge Conflicts" section in the [Git SCM book](https://git-scm.com/book/en/v2/Git-Branching-Basic-Branching-and-Merging) for more info.

##### You've fixed something locally which someone else has already done in the remote repo

The simplest way to fix this is to remove all the changes that you've made locally. You can do this using:

    $ git reset --hard kohana

##### You've fixed something locally which someone else has already fixed but you also have separate commits you'd like to keep

If this is the case then you'll want to use a tool called rebase. First of all we need to get rid of the conflicts created due to the merge:

    $ git reset --hard HEAD

Then find the hash of the offending local commit and run:

`git rebase -i {offending commit hash}`

e.g.

    $ git rebase -i 57d0b2

A text editor will open with a list of commits. Delete the line containing the offending commit before saving the file and closing your editor.

Git will remove the commit and you can then pull/merge the remote changes.

# Unit Testing

Kohana currently uses PHPUnit for unit testing. This is installed with composer.

## How to run the tests

 * Install [Phing](https://phing.info).
 * Make sure you have the unittest module enabled.
 * Install [Composer](https://getcomposer.org).
 * Run `php composer.phar install` from the root of this repository.
 * Finally, run `phing test`.

This will run the unit tests for core and all the modules and tell you if anything failed. If you haven't changed anything and you get failures, please [create a new issue on GitHub](https://github.com/kilofox/kohana/issues/new) and paste the output (including the error) in the issue.
