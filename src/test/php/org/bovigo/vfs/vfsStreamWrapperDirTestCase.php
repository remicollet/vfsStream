<?php
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper around mkdir().
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/vfsStreamWrapperBaseTestCase.php';
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper around mkdir().
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class vfsStreamWrapperMkDirTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * mkdir() should not overwrite existing root
     *
     * @test
     */
    public function mkdirNoNewRoot()
    {
        $this->assertFalse(mkdir(vfsStream::url('another')));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertSame($this->foo, vfsStreamWrapper::getRoot());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     */
    public function mkdirNonRecursively()
    {
        $this->assertFalse(mkdir($this->barURL . '/another/more'));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertTrue(mkdir($this->fooURL . '/another'));
        $this->assertEquals(3, count($this->foo->getChildren()));
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     */
    public function mkdirRecursively()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another/more', null, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
    }

    /**
     * no root > new directory becomes root
     *
     * @test
     */
    public function mkdirWithoutRootCreatesNewRoot()
    {
        vfsStreamWrapper::register();
        $this->assertTrue(@mkdir(vfsStream::url('foo')));
        $this->assertEquals(vfsStreamContent::TYPE_DIR, vfsStreamWrapper::getRoot()->getType());
        $this->assertEquals('foo', vfsStreamWrapper::getRoot()->getName());
    }

    /**
     * trying to create a subdirectory of a file should not work
     *
     * @test
     */
    public function mkdirOnFileReturnsFalse()
    {
        $this->assertFalse(mkdir($this->baz1URL . '/another/more', null, true));
    }

    /**
     * assure that a directory iteration works as expected
     *
     * @test
     */
    public function directoryIteration()
    {
        $dir = dir($this->fooURL);
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEquals(2, $i, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        $dir->rewind();
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEquals(2, $i, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        $dir->close();
    }

    /**
     * assert is_dir() returns correct result
     *
     * @test
     */
    public function is_dir()
    {
        $this->assertTrue(is_dir($this->fooURL));
        $this->assertTrue(is_dir($this->barURL));
        $this->assertFalse(is_dir($this->baz1URL));
        $this->assertFalse(is_dir($this->baz2URL));
        $this->assertFalse(is_dir($this->fooURL . '/another'));
        $this->assertFalse(is_dir(vfsStream::url('another')));
    }

    /**
     * can not unlink without root
     *
     * @test
     */
    public function canNotUnlinkDirectoryWithoutRoot()
    {
        vfsStreamWrapper::register();
        $this->assertFalse(@rmdir(vfsStream::url('foo')));
    }

    /**
     * rmdir() can not remove files
     *
     * @test
     */
    public function rmdirCanNotRemoveFiles()
    {
        $this->assertFalse(rmdir($this->baz1URL));
        $this->assertFalse(rmdir($this->baz2URL));
    }

    /**
     * rmdir() can not remove a non-existing directory
     *
     * @test
     */
    public function rmdirCanNotRemoveNonExistingDirectory()
    {
        $this->assertFalse(rmdir($this->fooURL . '/another'));
    }

    /**
     * rmdir() can not remove non-empty directories
     *
     * @test
     */
    public function rmdirCanNotRemoveNonEmptyDirectory()
    {
        $this->assertFalse(rmdir($this->fooURL));
        $this->assertFalse(rmdir($this->barURL));
    }

    /**
     * rmdir() can remove empty directories
     *
     * @test
     */
    public function rmdirCanRemoveEmptyDirectory()
    {
        vfsStream::newDirectory('empty')->at($this->foo);
        $this->assertTrue($this->foo->hasChild('empty'));
        $this->assertTrue(rmdir($this->fooURL . '/empty'));
        $this->assertFalse($this->foo->hasChild('empty'));
    }

    /**
     * rmdir() can remove empty directories
     *
     * @test
     */
    public function rmdirCanRemoveEmptyRoot()
    {
        $this->foo->removeChild('bar');
        $this->foo->removeChild('baz2');
        $this->assertTrue(rmdir($this->fooURL));
        $this->assertFalse(file_exists($this->fooURL)); // make sure statcache was cleared
        $this->assertNull(vfsStreamWrapper::getRoot());
    }
}
?>