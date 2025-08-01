<?php

/**
 * Documentation generator.
 *
 * @package    Kohana/Userguide
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2013 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Kodoc
{
    /**
     * @var string  PCRE fragment for matching 'Class', 'Class::method', 'Class::method()' or 'Class::$property'
     */
    public static $regex_class_member = '((\w++)(?:::(\$?\w++))?(?:\(\))?)';

    /**
     * Make a class#member API link using an array of matches from [Kodoc::$regex_class_member]
     *
     * @param array $matches [1 => 'link text', 2 => 'class name', [3 => 'member name']]
     * @return  string
     * @throws Kohana_Exception
     */
    public static function link_class_member($matches)
    {
        $link = $matches[1];
        $class = $matches[2];
        $member = null;

        if (isset($matches[3])) {
            // If the first char is a $ it is a property, e.g. Kohana::$base_url
            if ($matches[3][0] === '$') {
                $member = '#property:' . substr($matches[3], 1);
            } elseif (preg_match('/^[A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*$/', $matches[3])) {
                $member = '#constant:' . substr($matches[3], 0);
            } else {
                $member = '#' . $matches[3];
            }
        }

        return HTML::anchor(Route::get('docs/api')->uri(['class' => $class]) . $member, $link);
    }

    public static function factory($class)
    {
        return new Kodoc_Class($class);
    }

    /**
     * Creates an HTML list of all classes sorted by category (or package if no category)
     *
     * @return View The View object for rendering the menu
     * @throws Kohana_Exception
     */
    public static function menu()
    {
        $classes = Kodoc::classes();

        ksort($classes);

        $menu = [];

        $route = Route::get('docs/api');

        foreach ($classes as $class) {
            if (Kodoc::is_transparent($class, $classes))
                continue;

            $class = Kodoc_Class::factory($class);

            // Test if we should show this class
            if (!Kodoc::show_class($class))
                continue;

            $link = HTML::anchor($route->uri(['class' => $class->class->name]), $class->class->name);

            if (isset($class->tags['package'])) {
                foreach ($class->tags['package'] as $package) {
                    if (isset($class->tags['category'])) {
                        foreach ($class->tags['category'] as $category) {
                            $menu[$package][$category][] = $link;
                        }
                    } else {
                        $menu[$package]['Base'][] = $link;
                    }
                }
            } else {
                $menu['[Unknown]']['Base'][] = $link;
            }
        }

        // Sort the packages
        ksort($menu);

        return View::factory('userguide/api/menu')
                ->bind('menu', $menu);
    }

    /**
     * Returns an array of all the classes available, built by listing all files in the classes folder.
     *
     * @param array|null $list array of files, obtained using Kohana::list_files
     * @return  array   an array of all the class names
     */
    public static function classes(array $list = null)
    {
        if ($list === null) {
            $list = Kohana::list_files('classes');
        }

        $classes = [];

        // This will be used a lot!
        $ext_length = strlen(EXT);

        foreach ($list as $name => $path) {
            if (is_array($path)) {
                $classes += Kodoc::classes($path);
            } elseif (substr($name, -$ext_length) === EXT) {
                // Remove "classes/" and the extension
                $class = substr($name, 8, -$ext_length);

                // Convert slashes to underscores
                $class = str_replace(DIRECTORY_SEPARATOR, '_', $class);

                $classes[$class] = $class;
            }
        }

        return $classes;
    }

    /**
     * Get all classes and methods of files in a list.
     *
     * >  I personally don't like this as it was used on the index page.  Way too much stuff on one page.  It has potential for a package index page though.
     * >  For example:  class_methods( Kohana::list_files('classes/sprig') ) could make a nice index page for the sprig package in the api browser
     * >     ~bluehawk
     *
     */
    public static function class_methods(array $list = null)
    {
        $list = Kodoc::classes($list);

        $classes = [];

        foreach ($list as $class) {
            // Skip transparent extension classes
            if (Kodoc::is_transparent($class))
                continue;

            $_class = new ReflectionClass($class);

            $methods = [];

            foreach ($_class->getMethods() as $_method) {
                $declares = $_method->getDeclaringClass()->name;

                // Remove the transparent prefix from declaring classes
                if ($child = Kodoc::is_transparent($declares)) {
                    $declares = $child;
                }

                if ($declares === $_class->name || $declares === "Core") {
                    $methods[] = $_method->name;
                }
            }

            sort($methods);

            $classes[$_class->name] = $methods;
        }

        return $classes;
    }

    /**
     * Generate HTML for the content of a tag.
     *
     * @param string $tag Name of the tag without @
     * @param string $text Content of the tag
     * @return  string  HTML
     * @throws Kohana_Exception
     */
    public static function format_tag($tag, $text)
    {
        if ($tag === 'license') {
            if (strpos($text, '://') !== false)
                return HTML::anchor($text);
        }
        elseif ($tag === 'link') {
            $split = preg_split('/\s+/', $text, 2);

            return HTML::anchor(
                    $split[0], isset($split[1]) ? $split[1] : $split[0]
            );
        } elseif ($tag === 'copyright') {
            // Convert the copyright symbol
            return str_replace('(c)', '&copy;', $text);
        } elseif ($tag === 'throws') {
            $route = Route::get('docs/api');

            if (preg_match('/^(\w+)\W(.*)$/D', $text, $matches)) {
                return HTML::anchor(
                        $route->uri(['class' => $matches[1]]), $matches[1]
                    ) . ' ' . $matches[2];
            }

            return HTML::anchor(
                    $route->uri(['class' => $text]), $text
            );
        } elseif ($tag === 'see' || $tag === 'uses') {
            if (preg_match('/^' . Kodoc::$regex_class_member . '/', $text, $matches))
                return Kodoc::link_class_member($matches);
        }

        return $text;
    }

    /**
     * Parse a comment to extract the description and the tags
     *
     * [!!] Converting the output to HTML in this method is deprecated in 3.3
     *
     * @param string $comment The DocBlock to parse
     * @param bool $html Whether to convert the return values to HTML (deprecated)
     * @return  array   [string $description, array $tags]
     * @throws Kohana_Exception
     */
    public static function parse($comment, $html = true)
    {
        // Normalize all new lines to \n
        $comment = str_replace(["\r\n", "\n"], "\n", $comment);

        // Split into lines while capturing without leading whitespace
        preg_match_all('/^\s*\* ?(.*)\n/m', $comment, $lines);

        // Tag content
        $tags = [];

        /**
         * Process a tag and add it to $tags
         *
         * @param string $tag Name of the tag without @
         * @param string $text Content of the tag
         * @return  void
         * @throws Kohana_Exception
         */
        $add_tag = function ($tag, $text) use ($html, & $tags) {
            // Don't show @access lines, they are shown elsewhere
            if ($tag !== 'access') {
                if ($html) {
                    $text = Kodoc::format_tag($tag, $text);
                }

                // Add the tag
                $tags[$tag][] = $text;
            }
        };

        $comment = $tag = null;
        $end = count($lines[1]) - 1;

        foreach ($lines[1] as $i => $line) {
            // Search this line for a tag
            if (preg_match('/^@(\S+)\s*(.+)?$/', $line, $matches)) {
                if ($tag) {
                    // Previous tag is finished
                    $add_tag($tag, $text);
                }

                $tag = $matches[1];
                $text = isset($matches[2]) ? $matches[2] : '';

                if ($i === $end) {
                    // No more lines
                    $add_tag($tag, $text);
                }
            } elseif ($tag) {
                // This is the continuation of the previous tag
                $text .= "\n" . $line;

                if ($i === $end) {
                    // No more lines
                    $add_tag($tag, $text);
                }
            } else {
                $comment .= "\n" . $line;
            }
        }

        $comment = trim($comment, "\n");

        if ($comment && $html) {
            // Parse the comment with Markdown
            $comment = Kodoc_Markdown::markdown($comment);
        }

        return [$comment, $tags];
    }

    /**
     * Get the source of a function
     *
     * @param string $file the filename
     * @param int $start start line?
     * @param int $end end line?
     */
    public static function source($file, $start, $end)
    {
        if (!$file)
            return false;

        $file = file($file, FILE_IGNORE_NEW_LINES);

        $file = array_slice($file, $start - 1, $end - $start + 1);

        if (preg_match('/^(\s+)/', $file[0], $matches)) {
            $padding = strlen($matches[1]);

            foreach ($file as & $line) {
                $line = substr($line, $padding);
            }
        }

        return implode("\n", $file);
    }

    /**
     * Test whether a class should be shown, based on the api_packages config option
     *
     * @param Kodoc_Class $class the class to test
     * @return  bool  whether this class should be shown
     * @throws Kohana_Exception
     */
    public static function show_class(Kodoc_Class $class)
    {
        $api_packages = Kohana::$config->load('userguide.api_packages');

        // If api_packages is true, all packages should be shown
        if ($api_packages === true)
            return true;

        // Get the package tags for this class (as an array)
        $packages = Arr::get($class->tags, 'package', ['None']);

        $show_this = false;

        // Loop through each package tag
        foreach ($packages as $package) {
            // If this package is in the allowed packages, set show this to true
            if (in_array($package, explode(',', $api_packages)))
                $show_this = true;
        }

        return $show_this;
    }

    /**
     * Checks whether a class is a transparent extension class or not.
     *
     * This method takes an optional $classes parameter, a list of all defined
     * class names. If provided, the method will return false unless the extension
     * class exists. If not, the method will only check known transparent class
     * prefixes.
     *
     * Transparent prefixes are defined in the userguide.php config file:
     *
     *     'transparent_prefixes' => [
     *         'Kohana' => true,
     *     ];
     *
     * Module developers can therefore add their own transparent extension
     * namespaces and exclude them from the userguide.
     *
     * @param string $class The name of the class to check for transparency
     * @param array $classes An optional list of all defined classes
     * @return  false                     If this is not a transparent extension class
     * @throws Kohana_Exception
     */
    public static function is_transparent($class, $classes = null)
    {

        static $transparent_prefixes = null;

        if (!$transparent_prefixes) {
            $transparent_prefixes = Kohana::$config->load('userguide.transparent_prefixes');
        }

        // Split the class name at the first underscore
        $segments = explode('_', $class, 2);

        if (count($segments) === 2 && isset($transparent_prefixes[$segments[0]])) {
            if ($segments[1] === 'Core') {
                // Cater for Module extends Module_Core naming
                $child_class = $segments[0];
            } else {
                // Cater for Foo extends Module_Foo naming
                $child_class = $segments[1];
            }

            // It is only a transparent class if the unprefixed class also exists
            if ($classes && !isset($classes[$child_class]))
                return false;

            // Return the name of the child class
            return $child_class;
        }
        else {
            // Not a transparent class
            return false;
        }
    }

}
