# ORM

Kohana 3.x includes a powerful Object Relational Mapping (ORM) module that uses the active record pattern and database introspection to determine a model's column information. ORM is integrated tightly with the [Validation] library.

The ORM allows for manipulation and control of data within a database as though it was a PHP object. Once you define the relationships ORM allows you to pull data from your database, manipulate the data in any way you like, and then save the result back to the database without the use of SQL. By creating relationships between models that follow convention over configuration, much of the repetition of writing queries to create, read, update, and delete information from the database can be reduced or entirely removed. All the relationships can be handled automatically by the ORM library, and you can access related data as standard object properties.

ORM is included with the Kohana 3.x installation but needs to be enabled before you can use it. In your `application/bootstrap.php` file modify the call to Kohana::modules and include the ORM modules.

## Getting started

Before we use ORM, we must enable the modules required

    Kohana::modules([
        // ...
        'database' => MODPATH . 'database',
        'orm' => MODPATH . 'orm',
        // ...
    ]);

[!!] The database module is required for the ORM module to work. Of course the database module has to be configured to use an existing database.

You can now create your [models](models) and [use ORM](using).
