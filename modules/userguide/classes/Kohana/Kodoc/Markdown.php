<?php

/**
 * Custom Markdown parser for Kohana documentation.
 *
 * @package    Kohana/Userguide
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2013 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Kodoc_Markdown extends MarkdownExtra_Parser
{
    /**
     * @var  string  base URL for links
     */
    public static $base_url = '';

    /**
     * @var  string  base URL for images
     */
    public static $image_url = '';

    /**
     * Currently defined heading ids.
     * Used to prevent creating multiple headings with same id.
     *
     * @var  array
     */
    protected $_heading_ids = [];

    /**
     * @var  array  The generated table of contents.
     */
    protected static $_toc = [];

    /**
     * Slightly less terrible way to make it so the TOC only shows up when we
     * want it to.  set this to true to show the toc.
     */
    public static $show_toc = false;

    /**
     * Transform some text using [Kodoc_Markdown]
     *
     * @see Markdown()
     *
     * @param string $text Text to parse
     * @return  string  Transformed text
     */
    public static function markdown($text)
    {
        static $instance;

        if ($instance === null) {
            $instance = new Kodoc_Markdown;
        }

        return $instance->transform($text);
    }

    public function __construct()
    {
        // doImage is 10, add image URL just before
        $this->span_gamut['doImageURL'] = 9;

        // doLink is 20, add base URL just before
        $this->span_gamut['doBaseURL'] = 19;

        // Add API links
        $this->span_gamut['doAPI'] = 90;

        // Add note spans last
        $this->span_gamut['doNotes'] = 100;

        // Parse Kohana view inclusions at the very end
        $this->document_gamut['doIncludeViews'] = 99;

        // Show table of contents for userguide pages
        $this->document_gamut['doTOC'] = 100;

        // Call parent constructor.
        parent::__construct();
    }

    /**
     * Callback for the heading setext style
     *
     * Heading 1
     * =========
     *
     * @param array $matches Matches from regex call
     * @return  string  Generated HTML
     */
    function _doHeaders_callback_setext($matches)
    {
        if ($matches[3] === '-' && preg_match('{^- }', $matches[1]))
            return $matches[0];
        $level = $matches[3]{0} === '=' ? 1 : 2;
        $attr = $this->_doHeaders_attr($matches[2]);

        // Only auto-generate id if one doesn't exist
        if (empty($attr)) {
            $attr = ' id="' . $this->make_heading_id($matches[1]) . '"';
        }

        // Add this header to the page toc
        $this->_add_to_toc($level, $matches[1], $this->make_heading_id($matches[1]));

        $block = "<h$level$attr>" . $this->runSpanGamut($matches[1]) . "</h$level>";
        return "\n" . $this->hashBlock($block) . "\n\n";
    }

    /**
     * Callback for the heading atx style
     *
     * # Heading 1
     *
     * @param array $matches Matches from regex call
     * @return  string  Generated HTML
     */
    function _doHeaders_callback_atx($matches)
    {
        $level = strlen($matches[1]);
        $attr = $this->_doHeaders_attr(isset($matches[3]) ? $matches[3] : '');

        // Only auto-generate id if one doesn't exist
        if (empty($attr)) {
            $attr = ' id="' . $this->make_heading_id($matches[2]) . '"';
        }

        // Add this header to the page toc
        $this->_add_to_toc($level, $matches[2], $this->make_heading_id(empty($matches[3]) ? $matches[2] : $matches[3]));

        $block = "<h$level$attr>" . $this->runSpanGamut($matches[2]) . "</h$level>";
        return "\n" . $this->hashBlock($block) . "\n\n";
    }

    /**
     * Makes a heading id from the heading text
     * If any heading share the same name then subsequent headings will have an integer appended
     *
     * @param string $heading The heading text
     * @return  string  ID for the heading
     */
    function make_heading_id($heading)
    {
        $id = url::title($heading, '-', true);

        if (isset($this->_heading_ids[$id])) {
            $id .= '-';

            $count = 0;

            while (isset($this->_heading_ids[$id]) && ++$count) {
                $id .= $count;
            }
        }

        return $id;
    }

    public function doIncludeViews($text)
    {
        if (preg_match_all('/{{([^\s{}]++)}}/', $text, $matches, PREG_SET_ORDER)) {
            $replace = [];

            foreach ($matches as $set) {
                list($search, $view) = $set;

                if (Kohana::find_file('views', $view)) {
                    try {
                        $replace[$search] = View::factory($view)->render();
                    } catch (Exception $e) {
                        /**
                         * Capture the exception handler output and insert it instead.
                         *
                         * NOTE: Is this really the correct way to handle an exception?
                         */
                        $response = Kohana_exception::_handler($e);

                        $replace[$search] = $response->body();
                    }
                }
            }

            $text = strtr($text, $replace);
        }

        return $text;
    }

    /**
     * Add the current base URL to all local links.
     *
     *     [filesystem](about.filesystem "Optional title")
     *
     * @param string $text Span text
     * @return  string
     */
    public function doBaseURL($text)
    {
        // URLs containing "://" are left untouched
        return preg_replace('~(?<!!)(\[.+?\]\()(?!\w++://)(?!#)(\S*(?:\s*+".+?")?\))~', '$1' . Kodoc_Markdown::$base_url . '$2', $text);
    }

    /**
     * Add the current base URL to all local images.
     *
     *     ![Install Page](img/install.png "Optional title")
     *
     * @param string $text Span text
     * @return  string
     */
    public function doImageURL($text)
    {
        // URLs containing "://" are left untouched
        return preg_replace('~(!\[.+?\]\()(?!\w++://)(\S*(?:\s*+".+?")?\))~', '$1' . Kodoc_Markdown::$image_url . '$2', $text);
    }

    /**
     * Parses links to the API browser.
     *
     *     [Class_Name], [Class::method] or [Class::$property]
     *
     * @param string $text Span text
     * @return  string
     */
    public function doAPI($text)
    {
        return preg_replace_callback('/\[' . Kodoc::$regex_class_member . '\]/i', 'Kodoc::link_class_member', $text);
    }

    /**
     * Wrap notes in the applicable markup. Notes can contain single newlines.
     *
     *     [!!] Remember the milk!
     *
     * @param string $text Span text
     * @return  string
     */
    public function doNotes($text)
    {
        if (!preg_match('/^\[!!\]\s*+(.+?)(?=\n{2,}|$)/s', $text, $match)) {
            return $text;
        }

        return $this->hashBlock('<p class="note">' . $match[1] . '</p>');
    }

    protected function _add_to_toc($level, $name, $id)
    {
        self::$_toc[] = [
            'level' => $level,
            'name' => $name,
            'id' => $id
        ];
    }

    public function doTOC($text)
    {
        // Only add the toc do userguide pages, not api since they already have one
        if (self::$show_toc && Route::name(Request::current()->route()) === "docs/guide") {
            $toc = View::factory('userguide/page-toc')
                ->set('array', self::$_toc)
                ->render();

            if (($offset = strpos($text, '<p>')) !== false) {
                // Insert the page TOC just before the first <p>, which every
                // Markdown page should (will?) have.
                $text = substr_replace($text, $toc, $offset, 0);
            }
        }

        return $text;
    }

}
