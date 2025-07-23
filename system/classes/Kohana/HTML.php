<?php

/**
 * HTML helper class. Provides generic methods for generating various HTML
 * tags and making output HTML safe.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_HTML
{
    /**
     * @var  array  preferred order of attributes
     */
    public static $attribute_order = array
        (
        'action',
        'method',
        'type',
        'id',
        'name',
        'value',
        'href',
        'src',
        'width',
        'height',
        'cols',
        'rows',
        'size',
        'maxlength',
        'rel',
        'media',
        'accept-charset',
        'accept',
        'tabindex',
        'accesskey',
        'alt',
        'title',
        'class',
        'style',
        'selected',
        'checked',
        'readonly',
        'disabled',
    );

    /**
     * @var bool use strict XHTML mode?
     */
    public static $strict = true;

    /**
     * @var bool automatically target external URLs to a new window?
     */
    public static $windowed_urls = false;

    /**
     * Convert special characters to HTML entities. All untrusted content
     * should be passed through this method to prevent XSS injections.
     *
     *     echo HTML::chars($username);
     *
     * @param   string  $value          string to convert
     * @param   bool $double_encode encode existing entities
     * @return  string
     */
    public static function chars($value, $double_encode = true)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, Kohana::$charset, $double_encode);
    }

    /**
     * Convert all applicable characters to HTML entities. All characters
     * that cannot be represented in HTML with the current character set
     * will be converted to entities.
     *
     *     echo HTML::entities($username);
     *
     * @param   string  $value          string to convert
     * @param   bool $double_encode encode existing entities
     * @return  string
     */
    public static function entities($value, $double_encode = true)
    {
        return htmlentities((string) $value, ENT_QUOTES, Kohana::$charset, $double_encode);
    }

    /**
     * Create HTML link anchors. Note that the title is not escaped, to allow
     * HTML elements within links (images, etc.).
     *
     *     echo HTML::anchor('/user/profile', 'My Profile');
     *
     * @param string $uri URL or URI string
     * @param string $title link text
     * @param array $attributes HTML anchor attributes
     * @param mixed $protocol protocol to pass to URL::base()
     * @param bool $index include the index page
     * @return  string
     * @throws Kohana_Exception
     * @uses    URL::base
     * @uses    URL::site
     * @uses    HTML::attributes
     */
    public static function anchor($uri, $title = null, array $attributes = null, $protocol = null, $index = true)
    {
        if ($title === null) {
            // Use the URI as the title
            $title = $uri;
        }

        if ($uri === '') {
            // Only use the base URL
            $uri = URL::base($protocol, $index);
        } else {
            if (strpos($uri, '://') !== false) {
                if (HTML::$windowed_urls === true AND empty($attributes['target'])) {
                    // Make the link open in a new window
                    $attributes['target'] = '_blank';
                }
            } elseif ($uri[0] !== '#' AND $uri[0] !== '?') {
                // Make the URI absolute for non-fragment and non-query anchors
                $uri = URL::site($uri, $protocol, $index);
            }
        }

        // Add the sanitized link to the attributes
        $attributes['href'] = $uri;

        return '<a' . HTML::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Creates an HTML anchor to a file. Note that the title is not escaped,
     * to allow HTML elements within links (images, etc.).
     *
     *     echo HTML::file_anchor('media/doc/user_guide.pdf', 'User Guide');
     *
     * @param string $file name of file to link to
     * @param string $title link text
     * @param array $attributes HTML anchor attributes
     * @param mixed $protocol protocol to pass to URL::base()
     * @param bool $index include the index page
     * @return  string
     * @throws Kohana_Exception
     * @uses    HTML::attributes
     * @uses    URL::base
     */
    public static function file_anchor($file, $title = null, array $attributes = null, $protocol = null, $index = false)
    {
        if ($title === null) {
            // Use the file name as the title
            $title = basename($file);
        }

        // Add the file link to the attributes
        $attributes['href'] = URL::site($file, $protocol, $index);

        return '<a' . HTML::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Creates an email (mailto:) anchor. Note that the title is not escaped,
     * to allow HTML elements within links (images, etc.).
     *
     *     echo HTML::mailto($address);
     *
     * @param   string  $email      email address to send to
     * @param   string  $title      link text
     * @param   array   $attributes HTML anchor attributes
     * @return  string
     * @uses    HTML::attributes
     */
    public static function mailto($email, $title = null, array $attributes = null)
    {
        if ($title === null) {
            // Use the email address as the title
            $title = $email;
        }

        return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;' . $email . '"' . HTML::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Creates a style sheet link element.
     *
     *     echo HTML::style('media/css/screen.css');
     *
     * @param string $file file name
     * @param array $attributes default attributes
     * @param mixed $protocol protocol to pass to URL::base()
     * @param bool $index include the index page
     * @return  string
     * @throws Kohana_Exception
     * @uses    HTML::attributes
     * @uses    URL::base
     */
    public static function style($file, array $attributes = null, $protocol = null, $index = false)
    {
        if (strpos($file, '://') === false AND strpos($file, '//') !== 0) {
            // Add the base URL
            $file = URL::site($file, $protocol, $index);
        }

        // Set the stylesheet link
        $attributes['href'] = $file;

        // Set the stylesheet rel
        $attributes['rel'] = empty($attributes['rel']) ? 'stylesheet' : $attributes['rel'];

        // Set the stylesheet type
        $attributes['type'] = 'text/css';

        return '<link' . HTML::attributes($attributes) . ' />';
    }

    /**
     * Creates a script link.
     *
     *     echo HTML::script('media/js/jquery.min.js');
     *
     * @param string $file file name
     * @param array $attributes default attributes
     * @param mixed $protocol protocol to pass to URL::base()
     * @param bool $index include the index page
     * @return  string
     * @throws Kohana_Exception
     * @uses    HTML::attributes
     * @uses    URL::base
     */
    public static function script($file, array $attributes = null, $protocol = null, $index = false)
    {
        if (strpos($file, '://') === false AND strpos($file, '//') !== 0) {
            // Add the base URL
            $file = URL::site($file, $protocol, $index);
        }

        // Set the script link
        $attributes['src'] = $file;

        // Set the script type
        $attributes['type'] = 'text/javascript';

        return '<script' . HTML::attributes($attributes) . '></script>';
    }

    /**
     * Creates an image link.
     *
     *     echo HTML::image('media/img/logo.png', ['alt' => 'My Company']);
     *
     * @param string $file file name
     * @param array $attributes default attributes
     * @param mixed $protocol protocol to pass to URL::base()
     * @param bool $index include the index page
     * @return  string
     * @throws Kohana_Exception
     * @uses    HTML::attributes
     * @uses    URL::base
     */
    public static function image($file, array $attributes = null, $protocol = null, $index = false)
    {
        if (strpos($file, '://') === false) {
            // Add the base URL
            $file = URL::site($file, $protocol, $index);
        }

        // Add the image link
        $attributes['src'] = $file;

        return '<img' . HTML::attributes($attributes) . ' />';
    }

    /**
     * Compiles an array of HTML attributes into an attribute string.
     * Attributes will be sorted using HTML::$attribute_order for consistency.
     *
     *     echo '<div'.HTML::attributes($attrs).'>'.$content.'</div>';
     *
     * @param   array   $attributes attribute list
     * @return  string
     */
    public static function attributes(array $attributes = null)
    {
        if (empty($attributes))
            return '';

        $sorted = [];
        foreach (HTML::$attribute_order as $key) {
            if (isset($attributes[$key])) {
                // Add the attribute to the sorted list
                $sorted[$key] = $attributes[$key];
            }
        }

        // Combine the sorted attributes
        $attributes = $sorted + $attributes;

        $compiled = '';
        foreach ($attributes as $key => $value) {
            if ($value === null) {
                // Skip attributes that have null values
                continue;
            }

            if (is_int($key)) {
                // Assume non-associative keys are mirrored attributes
                $key = $value;

                if (!HTML::$strict) {
                    // Just use a key
                    $value = false;
                }
            }

            // Add the attribute key
            $compiled .= ' ' . $key;

            if ($value OR HTML::$strict) {
                // Add the attribute value
                $compiled .= '="' . HTML::chars($value) . '"';
            }
        }

        return $compiled;
    }

}
