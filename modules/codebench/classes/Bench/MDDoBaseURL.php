<?php

/**
 * @package    Kohana/Codebench
 * @category   Tests
 * @author     Geert De Deckere <geert@idoe.be>
 */
class Bench_MDDoBaseURL extends Codebench
{
    public $description = 'Optimization for the <code>doBaseURL()</code> method of <code>Kohana_Kodoc_Markdown</code>
		 for the Kohana Userguide.';
    public $loops = 10000;
    public $subjects = [
        // Valid matches
        '[filesystem](about.filesystem)',
        '[filesystem](about.filesystem "Optional title")',
        '[same page link](#id)',
        '[object-oriented](https://en.wikipedia.org/wiki/Object-oriented_programming)',
        // Invalid matches
        '![this is image syntax](about.filesystem)',
        '[filesystem](about.filesystem',
    ];

    public function bench_original($subject)
    {
        // The original regex contained a bug, which is fixed here for benchmarking purposes.
        // At the very start of the regex, (?!!) has been replace by (?<!!)
        return preg_replace_callback('~(?<!!)\[(.+?)\]\(([^#]\S*(?:\s*".+?")?)\)~', [$this, '_add_base_url_original'], $subject);
    }

    public function _add_base_url_original($matches)
    {
        if ($matches[2] && strpos($matches[2], '://') === false) {
            // Add the base URL to the link URL
            $matches[2] = 'http://BASE/' . $matches[2];
        }

        // Recreate the link
        return "[$matches[1]]($matches[2])";
    }

    public function bench_optimized_callback($subject)
    {
        return preg_replace_callback('~(?<!!)\[(.+?)\]\((?!\w++://)([^#]\S*(?:\s*+".+?")?)\)~', [$this, '_add_base_url_optimized'], $subject);
    }

    public function _add_base_url_optimized($matches)
    {
        // Add the base URL to the link URL
        $matches[2] = 'http://BASE/' . $matches[2];

        // Recreate the link
        return "[$matches[1]]($matches[2])";
    }

    public function bench_callback_gone($subject)
    {
        // What the optimized callback was doing is prepending some text to the URL.
        // We don't need a callback for that, and that should be clearly faster.
        return preg_replace('~(?<!!)(\[.+?\]\()(?!\w++://)([^#]\S*(?:\s*+".+?")?\))~', '$1http://BASE/$2', $subject);
    }

}
