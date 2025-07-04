# Conventions and Coding Style

It is encouraged that you follow Kohana's coding style. This makes code more readable and allows for easier code sharing and contributing.

## Class Names and File Location

Class names in Kohana follow a strict convention to facilitate [autoloading](autoloading). Class names should have uppercase first letters with underscores to separate words. Underscores are significant as they directly reflect the file location in the filesystem.

The following conventions apply:

1. CamelCased class names should be used when it is undesirable to create a new directory level.
2. All class file names and directory names must match the case of the class as per [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).
3. All classes should be in the `classes` directory. This may be at any level in the [cascading filesystem](files).

### Examples  {#class-name-examples}

Remember that in a class, an underscore means a new directory. Consider the following examples:

| Class Name          | File Path                       |
|---------------------|---------------------------------|
| Controller_Template | classes/Controller/Template.php |
| Model_User          | classes/Model/User.php          |
| Model_BlogPost      | classes/Model/BlogPost.php      |
| Database            | classes/Database.php            |
| Database_Query      | classes/Database/Query.php      |
| Form                | classes/Form.php                |

## Coding Style

In order to produce highly consistent source code, we ask that everyone follow the [PSR-12](https://www.php-fig.org/psr/psr-12) coding standard except the naming conventions below.

### Naming Conventions

Kohana uses under_score naming, not camelCase naming.

#### Classes

    // Controller class, uses Controller_ prefix
    class Controller_Apple extends Controller
    {
    }

    // Model class, uses Model_ prefix
    class Model_Cheese extends Model
    {
    }

    // Regular class
    class Peanut
    {
    }

## PHPDoc

Kohana follows the [PSR-5](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md) PHPDoc standard.
