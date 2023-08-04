# Database

Kohana comes with a robust module for working with databases. By default, the database module supports drivers for [PDO](http://php.net/pdo) and [MySQLi](http://php.net/mysqli), but new drivers can be made for other database servers.

The database module needs to be enabled before you can use it. To enable, open your `application/bootstrap.php` file and modify the call to [Kohana::modules] by including the database module like so:

    Kohana::modules([
        ...
        'database' => MODPATH . 'database',
        ...
    ]);

Next, you will then need to [configure](config) the database module to connect to your database.

Once that is done then you can make [queries](query) and use the [results](results).

The database module also provides a [config driver](../../guide-api/Config_Database) (for storing [configuration](../kohana/files/config) in the database) and a [session driver](../../guide-api/Session_Database).
