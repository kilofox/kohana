# Contributing

Kohana is community driven, and we rely on community contributions for the documentation.

## Guidelines

Documentation should use complete sentences, good grammar, and be as clear as possible. Use lots of example code, but make sure the examples follow the Kohana conventions and style.

Make sure your commit messages are clear and descriptive. Bad: "docs(userguide): add docs", Good: "docs(userguide): add initial draft of hello world tutorial". Bad: "fix(database): fix typos", Good: "fix(database): fix typos on the query builder page".

## Quick Method

To quickly point out something that needs improvement, report a [bug report](https://github.com/kilofox/kohana/issues/new).

If you want to contribute some changes, you can do so right from your browser without even knowing git!

You will need to fork the [kilofox/kohana](https://github.com/kilofox/kohana) repository by clicking on the Fork button in the top right.

![Fork the module](contrib-github-fork.png)

The files that make the User Guide portion are found in `system/guide/kohana/` or `modules/<module>/guide/<module>/`, and the API browser portion is made from the comments in the source code itself. Navigate to one of the files you want to change and click the edit button in the top right of the file viewer.

![Click on edit to edit the file](contrib-github-edit.png)

Make the changes and add a **detailed commit message**. Repeat this for as many files as you want to improve. (Note that you can't preview what the changes will look unless you actually test it locally.)

After you have made your changes, send a pull request so your improvements can be reviewed to be merged into the official documentation.

![Send a pull request](contrib-github-pull.png)

Once your pull request has been accepted, you can delete your repository if you want. Your commit will have been copied to the official branch.

## If you know Git

1. Fork the specific repo you want to contribute to on GitHub. (For example, go to https://github.com/kilofox/kohana and click the fork button.)

2. Now you need to add your fork as a "git remote" to your application and ensure you are on the right branch.

        cd my-kohana-app

        # Add your repository as a new remote.
        git remote add <your name> git://github.com/<your name>/kohana.git

        # Get the correct branch.
        git checkout develop

3. Now go into the repo of the area of docs you want to contribute to and add your forked repo as a new remote, and push to it.

        # Make some changes to the docs.
        nano file.md

        # Commit your changes - Use a descriptive commit message! If there is a redmine ticket for the changes you are making include "Fixes #XXXXX" in the commit message so its tracked.
        git commit -a -m "Corrected a typo in the ORM docs. Fixes #12345."

        # Make sure we are up to date with the latest changes.
        git merge origin/develop

        # Now push your changes to your fork.
        git push <your name> develop

4. Finally, send a pull request on GitHub.
