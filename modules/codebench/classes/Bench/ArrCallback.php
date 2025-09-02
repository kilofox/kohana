<?php

/**
 * @package    Kohana/Codebench
 * @category   Tests
 * @author     Geert De Deckere <geert@idoe.be>
 */
class Bench_ArrCallback extends Codebench
{
    public $description = 'Parsing <em>command(param,param)</em> strings in <code>Arr::callback()</code>.';
    public $loops = 10000;
    public $subjects = [
        // Valid callback strings
        'foo',
        'foo::bar',
        'foo(apple,orange)',
        'foo::bar(apple,orange)',
        '(apple,orange)', // no command, only params
        'foo((apple),(orange))', // params with brackets inside
        // Invalid callback strings
        'foo[apple,orange', // no closing bracket
    ];

    public function bench_geert_regex_1($subject): array
    {
        if (preg_match('/^([^(]*+)\((.*)\)$/', $subject, $matches))
            return $matches;

        return [];
    }

    public function bench_geert_regex_2($subject): array
    {
        // A rather experimental approach using \K which requires PCRE 7.2 ~ PHP 5.2.4
        // Note: $matches[0] = params, $matches[1] = command
        if (preg_match('/^([^(]*+)\(\K.*(?=\)$)/', $subject, $matches))
            return $matches;

        return [];
    }

    public function bench_geert_str($subject)
    {
        // A native string function approach which beats all the regexes
        if (strpos($subject, '(') !== false && substr($subject, -1) === ')')
            return explode('(', substr($subject, 0, -1), 2);

        return [];
    }

}
