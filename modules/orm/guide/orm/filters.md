# Filters

Filters in ORM work much like they used to when they were part of the Validate class in 3.0.x. However, they have been modified to match the flexible syntax of [Validation] rules in 3.1.x.

Filters run as soon as the field is set in your model and should be used to format the data before it is inserted into the Database. Filters are defined the same way you define [rules](validation), as an array returned by the `ORM::filters()` method, like the following:

    public function filters()
    {
        return [
            // Field Filters
            // $field_name => [mixed $callback[, array $params = [':value']]],
            'username' => [
                // PHP Function Callback, default implicit param of ':value'
                ['trim'],
            ],
            'password' => [
                // Callback method with object context and params
                [[$this, 'hash_password'], [':value', Model_User::salt()]],
            ],
            'created_on' => [
                // Callback static method with params
                ['Format::date', [':value', 'Y-m-d H:i:s']],
            ],
            'other_field' => [
                // Callback static method with implicit param of ':value'
                ['MyClass::static_method'],
                // Callback method with object context with implicit param of ':value'
                [[$this, 'change_other_field']],
                // PHP function callback with explicit params
                ['str_replace', ['luango', 'thomas', ':value']],
                // Function as the callback (PHP 5.3+)
                [function($value) {
                    // Do something to $value and return it.
                    return some_function($value);
                }],
            ],

        ];
    }

[!!] When defining filters, you may use the parameters `:value`, `:field`, and `:model` to refer to the field value, field name, and the model instance respectively.
