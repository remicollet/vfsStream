<?php
/**
 * Directory container.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 */
require_once dirname(__FILE__) . '/vfsStreamAbstractContent.php';
require_once dirname(__FILE__) . '/vfsStreamException.php';
/**
 * Directory container.
 *
 * @package     stubbles_vfs
 */
class vfsStreamDirectory extends vfsStreamAbstractContent implements Iterator
{
    /**
     * list of directory children
     *
     * @var  array<vfsStreamContent>
     */
    protected $children = array();

    /**
     * constructor
     *
     * @param   string  $name
     * @throws  vfsStreamException
     */
    public function __construct($name)
    {
        if (strstr($name, '/') !== false) {
            throw new vfsStreamException('Directory name can not contain /.');
        }
        
        $this->type = vfsStreamContent::TYPE_DIR;
        parent::__construct($name);
    }

    /**
     * factory method to create a directory structure for a given string
     *
     * @param   string  $dir
     * @return  vfsStreamDirectory
     */
    public static function create($dir)
    {
        if ('/' === $dir{0}) {
            $dir = substr($dir, 1);
        }
        
        $dirParts = explode('/', $dir);
        $content = new self($dirParts[0]);
        if (count($dirParts) > 1) {
            $content->addChild(self::create(self::getChildName($dir, $dirParts[0])));
        }
        
        return $content;
    }

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size()
    {
        $size = 0;
        foreach ($this->children as $child) {
            $size += $child->size();
        }
        
        return $size;
    }

    /**
     * renames the content
     *
     * @param   string  $newName
     * @throws  vfsStreamException
     */
    public function rename($newName)
    {
        if (strstr($newName, '/') !== false) {
            throw new vfsStreamException('Directory name can not contain /.');
        }
        
        parent::rename($newName);
    }

    /**
     * adds child to the directory
     *
     * @param  vfsStreamContent  $child
     */
    public function addChild(vfsStreamContent $child)
    {
        $this->children[] = $child;
    }

    /**
     * removes child from the directory
     *
     * @param   string  $name
     * @return  bool
     */
    public function removeChild($name)
    {
        foreach ($this->children as $key => $child) {
            if ($child->appliesTo($name) === false) {
                continue;
            }
            
            unset($this->children[$key]);
            return true;
        }
        
        return false;
    }

    /**
     * checks whether the container contains a child with the given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function hasChild($name)
    {
        if ($this->appliesTo($name) === true) {
            $childName = self::getChildName($name, $this->name);
        } else {
            $childName = $name;
        }
        
        foreach ($this->children as $child) {
            if ($child->appliesTo($childName) === false) {
                continue;
            }
            
            if ($child->getName() === $childName || $child->hasChild($childName) === true) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * returns the child with the given name
     *
     * @param   string  $name
     * @return  vfsStreamContent
     */
    public function getChild($name)
    {
        if ($this->appliesTo($name) === true) {
            $childName = self::getChildName($name, $this->name);
        } else {
            $childName = $name;
        }
        
        foreach ($this->children as $child) {
            if ($child->appliesTo($childName) === false) {
                continue;
            }
            
            if ($child->getName() === $childName) {
                return $child;
            }
            
            if ($child->hasChild($childName) === true) {
                return $child->getChild($childName);
            }
        }
        
        return null;
    }

    /**
     * helper method to calculate the child name
     *
     * @param   string  $name
     * @return  string
     */
    protected static function getChildName($name, $ownName)
    {
        return substr($name, strlen($ownName) + 1);
    }

    /**
     * returns a list of children for this directory
     *
     * @return  array<vfsStreamContent>
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * resets children pointer
     */
    public function rewind()
    {
        reset($this->children);
    }

    /**
     * returns the current child
     *
     * @return  vfsStreamContent
     */
    public function current()
    {
        $child = current($this->children);
        if (false === $child) {
            return null;
        }
        
        return $child;
    }

    /**
     * returns the name of the current child
     *
     * @return  string
     */
    public function key()
    {
        $child = current($this->children);
        if (false === $child) {
            return null;
        }
        
        return $child->getName();
    }

    /**
     * iterates to next child
     */
    public function next()
    {
        next($this->children);
    }

    /**
     * checks if the current value is valid
     *
     * @return  bool
     */
    public function valid()
    {
        return (false !== current($this->children));
    }
}
?>