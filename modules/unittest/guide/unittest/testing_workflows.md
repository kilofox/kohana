# Testing workflows

Having unittests for your application is a nice idea, but unless you actually use them they're about as useful as a chocolate firegaurd. There are quite a few ways of getting tests "into" your development process and this guide aims to cover a few of them.

## Integrating with IDEs

Modern IDEs have come a long way in the last couple of years and ones like NetBeans have pretty decent PHP / PHPUnit support.

### NetBeans (8.2+)

0. Open the Options window by choosing **Tools** > **Options** from the main menu. (If you are running on Mac OS X, choose NetBeans > Preferences.)
1. Select the **PHP** category in the upper pane and click the **Frameworks & Tools** tab. Select **PHPUnit** in the left pane of the tab and specify the path to the PHPUnit script. Click **Apply** and close the Options window.
2. Right-click the project node and select **Properties**.
3. Select **Testing** in the Categories pane. Select **PHPUnit** as a Testing Provider and specify the location of the test directories for the project.
4. Select **PHPUnit** under the Testing node in the Categories pane and specify the Bootstrap path to your `modules/unittest/bootstrap.php` file.

You can also specify a custom test suite loader (enter the path to your `modules/unittest/tests.php` file) and/or a custom configuration file (enter the path to your phpunit.xml file).

## Looping shell

If you're developing in a text editor such as textmate, vim, gedit etc. chances are PHPUnit support isn't natively supported by your editor.

In such situations you can run a simple bash script to loop over the tests every X seconds, here's an example script:

    while(true) do clear; phpunit; sleep 8; done;

You will probably need to adjust the timeout (`sleep 8`) to suit your own workflow, but 8 seconds seems to be about enough time to see what's erroring before the tests are re-run.

In the above example we're using a phpunit.xml config file to specify all the unit testing settings and to reduce the complexity of the looping script.

## Continuous Integration (CI)

Continuous integration is a team based tool which enables developers to keep tabs on whether changes committed to a project break the application. If a commit causes a test to fail then the build is classed as "broken" and the CI server then alerts developers by email, RSS, IM or glowing (bears|lava lamps) to the fact that someone has broken the build and that all hell's broken lose.

A popular CI server is [Hudson](http://hudson-ci.org/), which uses [Phing](http://phing.info/) to run the build tasks for your application.
