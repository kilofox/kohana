<?php

/**
 * Class documentation generator.
 *
 * @package    Kohana/Userguide
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2013 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Kodoc_Class extends Kodoc
{
    /**
     * @var  ReflectionClass The ReflectionClass for this class
     */
    public $class;

    /**
     * @var  string  modifiers like abstract, final
     */
    public $modifiers;

    /**
     * @var  string  description of the class from the comment
     */
    public $description;

    /**
     * @var  array  array of tags, retrieved from the comment
     */
    public $tags = [];

    /**
     * @var  array  array of this class's constants
     */
    public $constants = [];

    /**
     * @var array Parent classes/interfaces of this class/interface
     */
    public $parents = [];

    /**
     * Loads a class and uses [reflection](https://www.php.net/reflection) to parse
     * the class. Reads the class modifiers, constants and comment. Parses the
     * comment to find the description and tags.
     *
     * @param string $class Class name
     * @throws Kohana_Exception
     * @throws ReflectionException
     */
    public function __construct($class)
    {
        $this->class = new ReflectionClass($class);

        if ($modifiers = $this->class->getModifiers()) {
            $this->modifiers = '<small>' . implode(' ', Reflection::getModifierNames($modifiers)) . '</small> ';
        }

        $this->constants = $this->class->getConstants();

        // If ReflectionClass::getParentClass() won't work if the class in
        // question is an interface
        if ($this->class->isInterface()) {
            $this->parents = $this->class->getInterfaces();
        } else {
            $parent = $this->class;

            while ($parent = $parent->getParentClass()) {
                $this->parents[] = $parent;
            }
        }

        if (!$comment = $this->class->getDocComment()) {
            foreach ($this->parents as $parent) {
                if ($comment = $parent->getDocComment()) {
                    // Found a description for this class
                    break;
                }
            }
        }

        list($this->description, $this->tags) = Kodoc::parse($comment, false);
    }

    /**
     * Gets the constants of this class as HTML.
     *
     * @return  array
     */
    public function constants()
    {
        return array_map(function ($value) {
            return Debug::vars($value);
        }, $this->constants);
    }

    /**
     * Get the description of this class as HTML. Includes a warning when the
     * class or one of its parents could not be found.
     *
     * @return  string  HTML
     */
    public function description()
    {
        $result = $this->description;

        // If this class extends Kodoc_Missing, add a warning about possible
        // incomplete documentation
        foreach ($this->parents as $parent) {
            if ($parent->name === 'Kodoc_Missing') {
                $result .= "[!!] **This class, or a class parent, could not be
				           found or loaded. This could be caused by a missing
				           module or other dependency. The documentation for
				           class may not be complete!**";
            }
        }

        return Kodoc_Markdown::markdown($result);
    }

    /**
     * Gets a list of the class properties as [Kodoc_Property] objects.
     *
     * @return  array
     */
    public function properties()
    {
        $props = $this->class->getProperties();

        $defaults = $this->class->getDefaultProperties();

        usort($props, [$this, '_prop_sort']);

        foreach ($props as $key => $property) {
            // Create Kodoc Properties for each property
            $props[$key] = new Kodoc_Property($this->class->name, $property->name, Arr::get($defaults, $property->name));
        }

        return $props;
    }

    protected function _prop_sort($a, $b)
    {
        // If one property is public, and the other is not, it goes on top
        if ($a->isPublic() && ( !$b->isPublic()))
            return -1;
        if ($b->isPublic() && ( !$a->isPublic()))
            return 1;

        // If one property is protected and the other is private, it goes on top
        if ($a->isProtected() && $b->isPrivate())
            return -1;
        if ($b->isProtected() && $a->isPrivate())
            return 1;

        // Otherwise just do alphabetical
        return strcmp($a->name, $b->name);
    }

    /**
     * Gets a list of the class properties as [Kodoc_Method] objects.
     *
     * @return  array
     */
    public function methods()
    {
        $methods = $this->class->getMethods();

        usort($methods, [$this, '_method_sort']);

        foreach ($methods as $key => $method) {
            $methods[$key] = new Kodoc_Method($this->class->name, $method->name);
        }

        return $methods;
    }

    /**
     * Sort methods based on their visibility and declaring class based on:
     *
     *  * methods will be sorted public, protected, then private.
     *  * methods that are declared by an ancestor will be after classes
     *    declared by the current class
     *  * lastly, they will be sorted alphabetically
     *
     */
    protected function _method_sort($a, $b)
    {
        // If one method is public, and the other is not, it goes on top
        if ($a->isPublic() && ( !$b->isPublic()))
            return -1;
        if ($b->isPublic() && ( !$a->isPublic()))
            return 1;

        // If one method is protected and the other is private, it goes on top
        if ($a->isProtected() && $b->isPrivate())
            return -1;
        if ($b->isProtected() && $a->isPrivate())
            return 1;

        // The methods have the same visibility, so check the declaring class depth:


        /*
          echo Debug::vars('a is '.$a->class.'::'.$a->name,'b is '.$b->class.'::'.$b->name,
          'are the classes the same?', $a->class === $b->class,'if they are, the result is:',strcmp($a->name, $b->name),
          'is a this class?', $a->name === $this->class->name,-1,
          'is b this class?', $b->name === $this->class->name,1,
          'otherwise, the result is:',strcmp($a->class, $b->class)
          );
         */

        // If both methods are defined in the same class, just compare the method names
        if ($a->class === $b->class)
            return strcmp($a->name, $b->name);

        // If one of them was declared by this class, it needs to be on top
        if ($a->name === $this->class->name)
            return -1;
        if ($b->name === $this->class->name)
            return 1;

        // Otherwise, get the parents of each method's declaring class, then compare which function has more "ancestors"
        $adepth = 0;
        $bdepth = 0;

        $parent = $a->getDeclaringClass();
        do {
            $adepth++;
        } while ($parent = $parent->getParentClass());

        $parent = $b->getDeclaringClass();
        do {
            $bdepth++;
        } while ($parent = $parent->getParentClass());

        return $bdepth - $adepth;
    }

    /**
     * Get the tags of this class as HTML.
     *
     * @return  array
     * @throws Kohana_Exception
     */
    public function tags()
    {
        $result = [];

        foreach ($this->tags as $name => $set) {
            foreach ($set as $text) {
                $result[$name][] = Kodoc::format_tag($name, $text);
            }
        }

        return $result;
    }

}
