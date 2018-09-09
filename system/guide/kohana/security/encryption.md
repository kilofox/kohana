# Encryption

Kohana supports encryption and decryption via the [Encrypt] class,
which is a convenient wrapper for a variety of cryptography engines.
Encryption supports multiple instances of cryptography engines through a grouped singleton pattern.

## Supported cryptography engines

 *  [OpenSSL] ([Encrypt_Openssl])
 *  [Mcrypt] ([Encrypt_Mcrypt])

## Encryption configuration

The default config file is located in `system/config/encrypt.php`.
You should copy this file to `application/config/encrypt.php`.

The encryption configuration contains an array of configuration groups.
The structure of each configuration group looks like this:

    'default' => [
        'driver' => 'openssl',
        /**
         * The following options must be set:
         *
         * string   key     Secret passphrase.
         * integer  method  The cipher method, one of the return value from openssl_get_cipher_methods().
         */
        'method' => 'AES-256-CTR',
    ],

Notice there is no key provided. **You need to add that.**
It is strongly recommended that you choose a high-strength random key using the [pwgen linux program](http://linux.die.net/man/1/pwgen)...

    shell> pwgen 63 1
    trwQwVXX96TIJoKxyBHB9AJkwAOHixuV1ENZmIWyanI0j1zNgSVvqywy044Agaj

...or by going to [GRC.com/passwords.htm](https://www.grc.com/passwords.htm).

## Basic Usage

### Create an instance

Creating a new _Encrypt_ instance is simple, however it must be done using the [Encrypt::instance] method,
rather than the traditional `new` constructor.

    $encrypt = Encrypt::instance();

The default group will use whatever is set to [Encrypt::$default] and must have a corresponding configuration definition for that group.

To create an encryption instance using a group other than the default, simply provide the group name as an argument.

    $encrypt = Encrypt::instance('mcrypt');

### Encoding Data

Next, encode some data using the *encode* method:

    $encrypt = Encrypt::instance();
    $encryptedData = $encrypt->encode('Data to Encode');
    // $encryptedData now contains FBjxLR4K36AegNxk18owj2cgHiztmAGtRxvPHX63

[!!] Raw encrypted strings usually won't print in a browser,
and may not store properly in a VARCHAR or TEXT field.
For this reason, the _Encrypt_ automatically calls base64_encode on encode,
and base64_decode on decode, to prevent this problem.

[!!] One word of caution. The length of the encoded data expands quite a bit, so be sure your database column is long enough to store the encrypted data. If even one character is truncated, the data will not be recoverable.

### Decoding Data

To decode some data, load it from the place you stored it (most likely your database) then pass it to the *decode* method:

    $encrypt = Encrypt::instance();
    $decodedString = $encrypt->decode($encryptedData);
    echo $decodedString;
    // prints 'Data to Encode'

You can't know in advance what the encoded string will be, and it's not reproducible, either.
That is, you can encode the same value over and over, but you'll always obtain a different encoded version,
even without changing your key and cipher method. This is because Kohana adds some random entropy before encoding it with your value.
This ensures an attacker cannot easily discover your key and cipher method, even given a collection of encoded values.