<?php

/**
 * A version of the stock PHPUnit testcase that includes some extra helpers
 * and default settings
 */
abstract class Kohana_Unittest_TestCase extends PHPUnit\Framework\TestCase
{
    /**
     * Make sure PHPUnit backs up globals
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * A set of unittest helpers that are shared between normal / database
     * testcases
     * @var Kohana_Unittest_Helpers
     */
    protected $_helpers = null;

    /**
     * A default set of environment to be applied before each test
     * @var array
     */
    protected $environmentDefault = [];

    /**
     * Creates a predefined environment using the default environment
     *
     * Extending classes that have their own setUp() should call
     * parent::setUp()
     */
    public function setUp()
    {
        $this->_helpers = new Unittest_Helpers;

        $this->setEnvironment($this->environmentDefault);
    }

    /**
     * Restores the original environment overridden with setEnvironment()
     *
     * Extending classes that have their own tearDown()
     * should call parent::tearDown()
     */
    public function tearDown()
    {
        $this->_helpers->restore_environment();
    }

    /**
     * Removes all kohana related cache files in the cache directory
     *
     * @return void
     */
    public function cleanCacheDir()
    {
        Unittest_Helpers::clean_cache_dir();
    }

    /**
     * Helper function that replaces all occurrences of '/' with
     * the OS-specific directory separator
     *
     * @param string $path The path to act on
     * @return string
     */
    public function dirSeparator($path)
    {
        return Unittest_Helpers::dir_separator($path);
    }

    /**
     * Allows easy setting & backing up of environment config
     *
     * Option types are checked in the following order:
     *
     * * Server Var
     * * Static Variable
     * * Config option
     *
     * @param array $environment List of environment to set
     * @return false|null
     * @throws Kohana_Exception
     * @throws ReflectionException
     */
    public function setEnvironment(array $environment)
    {
        return $this->_helpers->set_environment($environment);
    }

    /**
     * Check for internet connectivity
     *
     * @return bool Whether an internet connection is available
     */
    public function hasInternet()
    {
        return Unittest_Helpers::has_internet();
    }

    /**
     * Evaluate an HTML or XML string and assert its structure and/or contents.
     *
     * TODO:
     * this should be removed when phpunit-dom-assertions gets released
     * https://github.com/phpunit/phpunit-dom-assertions
     *
     * @param array $matcher
     * @param string $actual
     * @param string $message
     * @param bool $isHtml
     * @uses Unittest_TestCase::tag_match
     */
    public static function assertTag($matcher, $actual, $message = '', $isHtml = true)
    {
        $dom = PHPUnit\Util\Xml::load($actual, $isHtml);
        $tags = self::findNodes($dom, $matcher, $isHtml);
        $matched = count($tags) > 0 && $tags[0] instanceof DOMNode;

        self::assertTrue($matched, $message);
    }

    /**
     * This assertion is the exact opposite of assertTag
     *
     * Rather than asserting that $matcher results in a match, it asserts that
     * $matcher does not match
     *
     * TODO:
     * this should be removed when phpunit-dom-assertions gets released
     * https://github.com/phpunit/phpunit-dom-assertions
     *
     * @param array $matcher
     * @param string $actual
     * @param string $message
     * @param bool $isHtml
     * @uses Unittest_TestCase::tag_match
     */
    public static function assertNotTag($matcher, $actual, $message = '', $isHtml = true)
    {
        $dom = PHPUnit\Util\Xml::load($actual, $isHtml);
        $tags = static::findNodes($dom, $matcher, $isHtml);
        $matched = count($tags) > 0 && $tags[0] instanceof DOMNode;

        self::assertFalse($matched, $message);
    }

    /**
     * Parse out the options from the tag using DOM object tree.
     *
     * TODO:
     * This should be removed when [phpunit-dom-assertions](https://github.com/phpunit/phpunit-dom-assertions) gets released.
     *
     * @param DOMDocument $dom
     * @param array       $options
     * @param bool        $isHtml
     * @return array
     */
    public static function findNodes(DOMDocument $dom, array $options, $isHtml = true)
    {
        $valid = [
            'id', 'class', 'tag', 'content', 'attributes', 'parent',
            'child', 'ancestor', 'descendant', 'children', 'adjacent-sibling'
        ];

        $filtered = [];
        $options = self::assertValidKeys($options, $valid);

        // find the element by id
        if ($options['id']) {
            $options['attributes']['id'] = $options['id'];
        }

        if ($options['class']) {
            $options['attributes']['class'] = $options['class'];
        }

        $nodes = [];

        // find the element by a tag type
        if ($options['tag']) {
            if ($isHtml) {
                $elements = self::getElementsByCaseInsensitiveTagName(
                        $dom, $options['tag']
                );
            } else {
                $elements = $dom->getElementsByTagName($options['tag']);
            }

            foreach ($elements as $element) {
                $nodes[] = $element;
            }
        } // no tag selected, get them all
        else {
            $tags = [
                'a', 'abbr', 'acronym', 'address', 'area', 'b', 'base', 'bdo',
                'big', 'blockquote', 'body', 'br', 'button', 'caption', 'cite',
                'code', 'col', 'colgroup', 'dd', 'del', 'div', 'dfn', 'dl',
                'dt', 'em', 'fieldset', 'form', 'frame', 'frameset', 'h1', 'h2',
                'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'i', 'iframe',
                'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link',
                'map', 'meta', 'noframes', 'noscript', 'object', 'ol', 'optgroup',
                'option', 'p', 'param', 'pre', 'q', 'samp', 'script', 'select',
                'small', 'span', 'strong', 'style', 'sub', 'sup', 'table',
                'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',
                'tr', 'tt', 'ul', 'var',
                // HTML5
                'article', 'aside', 'audio', 'bdi', 'canvas', 'command',
                'datalist', 'details', 'dialog', 'embed', 'figure', 'figcaption',
                'footer', 'header', 'hgroup', 'keygen', 'mark', 'meter', 'nav',
                'output', 'progress', 'ruby', 'rt', 'rp', 'track', 'section',
                'source', 'summary', 'time', 'video', 'wbr'
            ];

            foreach ($tags as $tag) {
                if ($isHtml) {
                    $elements = self::getElementsByCaseInsensitiveTagName(
                            $dom, $tag
                    );
                } else {
                    $elements = $dom->getElementsByTagName($tag);
                }

                foreach ($elements as $element) {
                    $nodes[] = $element;
                }
            }
        }

        if (empty($nodes)) {
            return $nodes;
        }

        // filter by attributes
        if ($options['attributes']) {
            foreach ($nodes as $node) {
                $invalid = false;

                foreach ($options['attributes'] as $name => $value) {
                    // match by regexp if like "regexp:/foo/i"
                    if (preg_match('/^regexp\s*:\s*(.*)/i', $value, $matches)) {
                        if (!preg_match($matches[1], $node->getAttribute($name))) {
                            $invalid = true;
                        }
                    } // class can match only a part
                    elseif ($name === 'class') {
                        // split to individual classes
                        $findClasses = explode(
                            ' ', preg_replace("/\s+/", ' ', $value)
                        );

                        $allClasses = explode(
                            ' ', preg_replace("/\s+/", ' ', $node->getAttribute($name))
                        );

                        // make sure each class given is in the actual node
                        foreach ($findClasses as $findClass) {
                            if (!in_array($findClass, $allClasses)) {
                                $invalid = true;
                            }
                        }
                    } // match by exact string
                    else {
                        if ($node->getAttribute($name) <> $value) {
                            $invalid = true;
                        }
                    }
                }

                // if every attribute given matched
                if (!$invalid) {
                    $filtered[] = $node;
                }
            }

            $nodes = $filtered;
            $filtered = [];

            if (empty($nodes)) {
                return $nodes;
            }
        }

        // filter by content
        if ($options['content'] !== null) {
            foreach ($nodes as $node) {
                $invalid = false;

                // match by regexp if like "regexp:/foo/i"
                if (preg_match('/^regexp\s*:\s*(.*)/i', $options['content'], $matches)) {
                    if (!preg_match($matches[1], self::getNodeText($node))) {
                        $invalid = true;
                    }
                } // match empty string
                elseif ($options['content'] === '') {
                    if (self::getNodeText($node) !== '') {
                        $invalid = true;
                    }
                } // match by exact string
                elseif (strstr(self::getNodeText($node), $options['content']) === false) {
                    $invalid = true;
                }

                if (!$invalid) {
                    $filtered[] = $node;
                }
            }

            $nodes = $filtered;
            $filtered = [];

            if (empty($nodes)) {
                return $nodes;
            }
        }

        // filter by parent node
        if ($options['parent']) {
            $parentNodes = self::findNodes($dom, $options['parent'], $isHtml);
            $parentNode = $parentNodes[0] ?? null;

            foreach ($nodes as $node) {
                if ($parentNode !== $node->parentNode) {
                    continue;
                }

                $filtered[] = $node;
            }

            $nodes = $filtered;
            $filtered = [];

            if (empty($nodes)) {
                return $nodes;
            }
        }

        // filter by child node
        if ($options['child']) {
            $childNodes = self::findNodes($dom, $options['child'], $isHtml);
            $childNodes = !empty($childNodes) ? $childNodes : [];

            foreach ($nodes as $node) {
                foreach ($node->childNodes as $child) {
                    foreach ($childNodes as $childNode) {
                        if ($childNode === $child) {
                            $filtered[] = $node;
                        }
                    }
                }
            }

            $nodes = $filtered;
            $filtered = [];

            if (empty($nodes)) {
                return $nodes;
            }
        }

        // filter by adjacent-sibling
        if ($options['adjacent-sibling']) {
            $adjacentSiblingNodes = self::findNodes($dom, $options['adjacent-sibling'], $isHtml);
            $adjacentSiblingNodes = !empty($adjacentSiblingNodes) ? $adjacentSiblingNodes : [];

            foreach ($nodes as $node) {
                $sibling = $node;

                while ($sibling = $sibling->nextSibling) {
                    if ($sibling->nodeType !== XML_ELEMENT_NODE) {
                        continue;
                    }

                    foreach ($adjacentSiblingNodes as $adjacentSiblingNode) {
                        if ($sibling === $adjacentSiblingNode) {
                            $filtered[] = $node;
                            break;
                        }
                    }

                    break;
                }
            }

            $nodes = $filtered;
            $filtered = [];

            if (empty($nodes)) {
                return $nodes;
            }
        }

        // filter by ancestor
        if ($options['ancestor']) {
            $ancestorNodes = self::findNodes($dom, $options['ancestor'], $isHtml);
            $ancestorNode = $ancestorNodes[0] ?? null;

            foreach ($nodes as $node) {
                $parent = $node->parentNode;

                while ($parent && $parent->nodeType !== XML_HTML_DOCUMENT_NODE) {
                    if ($parent === $ancestorNode) {
                        $filtered[] = $node;
                    }

                    $parent = $parent->parentNode;
                }
            }

            $nodes = $filtered;
            $filtered = [];

            if (empty($nodes)) {
                return $nodes;
            }
        }

        // filter by descendant
        if ($options['descendant']) {
            $descendantNodes = self::findNodes($dom, $options['descendant'], $isHtml);
            $descendantNodes = !empty($descendantNodes) ? $descendantNodes : [];

            foreach ($nodes as $node) {
                foreach (self::getDescendants($node) as $descendant) {
                    foreach ($descendantNodes as $descendantNode) {
                        if ($descendantNode === $descendant) {
                            $filtered[] = $node;
                        }
                    }
                }
            }

            $nodes = $filtered;
            $filtered = [];

            if (empty($nodes)) {
                return $nodes;
            }
        }

        // filter by children
        if ($options['children']) {
            $validChild = ['count', 'greater_than', 'less_than', 'only'];
            $childOptions = self::assertValidKeys(
                    $options['children'], $validChild
            );

            foreach ($nodes as $node) {
                $childNodes = $node->childNodes;

                foreach ($childNodes as $childNode) {
                    if ($childNode->nodeType !== XML_CDATA_SECTION_NODE &&
                        $childNode->nodeType !== XML_TEXT_NODE) {
                        $children[] = $childNode;
                    }
                }

                // we must have children to pass this filter
                if (!empty($children)) {
                    // exact count of children
                    if ($childOptions['count'] !== null) {
                        if (count($children) !== $childOptions['count']) {
                            break;
                        }
                    } // range count of children
                    elseif ($childOptions['less_than'] !== null &&
                        $childOptions['greater_than'] !== null) {
                        if (count($children) >= $childOptions['less_than'] ||
                            count($children) <= $childOptions['greater_than']) {
                            break;
                        }
                    } // less than a given count
                    elseif ($childOptions['less_than'] !== null) {
                        if (count($children) >= $childOptions['less_than']) {
                            break;
                        }
                    } // more than a given count
                    elseif ($childOptions['greater_than'] !== null) {
                        if (count($children) <= $childOptions['greater_than']) {
                            break;
                        }
                    }

                    // match each child against a specific tag
                    if ($childOptions['only']) {
                        $onlyNodes = self::findNodes(
                                $dom, $childOptions['only'], $isHtml
                        );

                        // try to match each child to one of the 'only' nodes
                        foreach ($children as $child) {
                            $matched = false;

                            foreach ($onlyNodes as $onlyNode) {
                                if ($onlyNode === $child) {
                                    $matched = true;
                                }
                            }

                            if (!$matched) {
                                break 2;
                            }
                        }
                    }

                    $filtered[] = $node;
                }
            }

            $nodes = $filtered;

            if (empty($nodes)) {
                return $nodes;
            }
        }

        // return the first node that matches all criteria
        return $nodes;
    }

    /**
     * Validate list of keys in the associative array.
     *
     * TODO:
     * This should be removed when [phpunit-dom-assertions](https://github.com/phpunit/phpunit-dom-assertions) gets released.
     *
     * @param array $hash
     * @param array $validKeys
     * @return array
     * @throws PHPUnit_Framework_Exception
     */
    public static function assertValidKeys(array $hash, array $validKeys)
    {
        $valids = [];

        // Normalize validation keys so that we can use both indexed and
        // associative arrays.
        foreach ($validKeys as $key => $val) {
            is_int($key) ? $valids[$val] = null : $valids[$key] = $val;
        }

        $validKeys = array_keys($valids);

        // Check for invalid keys.
        foreach ($hash as $key => $value) {
            if (!in_array($key, $validKeys)) {
                $unknown[] = $key;
            }
        }

        if (!empty($unknown)) {
            throw new PHPUnit_Framework_Exception(
            'Unknown key(s): ' . implode(', ', $unknown)
            );
        }

        // Add default values for any valid keys that are empty.
        foreach ($valids as $key => $value) {
            if (!isset($hash[$key])) {
                $hash[$key] = $value;
            }
        }

        return $hash;
    }

    /**
     * Gets elements by case-insensitive tag name.
     *
     * TODO:
     * This should be removed when [phpunit-dom-assertions](https://github.com/phpunit/phpunit-dom-assertions) gets released.
     *
     * @param DOMDocument $dom
     * @param string      $tag
     * @return DOMNodeList
     */
    protected static function getElementsByCaseInsensitiveTagName(DOMDocument $dom, $tag)
    {
        $elements = $dom->getElementsByTagName(strtolower($tag));

        if ($elements->length === 0) {
            $elements = $dom->getElementsByTagName(strtoupper($tag));
        }

        return $elements;
    }

    /**
     * Get the text value of this node's child text node.
     *
     * TODO:
     * This should be removed when [phpunit-dom-assertions](https://github.com/phpunit/phpunit-dom-assertions) gets released.
     *
     * @param DOMNode $node
     * @return string
     */
    protected static function getNodeText(DOMNode $node)
    {
        if (!$node->childNodes instanceof DOMNodeList) {
            return '';
        }

        $result = '';

        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_TEXT_NODE ||
                $childNode->nodeType === XML_CDATA_SECTION_NODE) {
                $result .= trim($childNode->data) . ' ';
            } else {
                $result .= self::getNodeText($childNode);
            }
        }

        return str_replace('  ', ' ', $result);
    }

    /**
     * Recursively get flat array of all descendants of this node.
     *
     * TODO:
     * This should be removed when [phpunit-dom-assertions](https://github.com/phpunit/phpunit-dom-assertions) gets released.
     *
     * @param DOMNode $node
     * @return array
     */
    protected static function getDescendants(DOMNode $node)
    {
        $allChildren = [];
        $childNodes = $node->childNodes ?: [];

        foreach ($childNodes as $child) {
            if ($child->nodeType === XML_CDATA_SECTION_NODE ||
                $child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            $children = self::getDescendants($child);
            $allChildren = array_merge($allChildren, $children, [$child]);
        }

        return $allChildren;
    }

}
