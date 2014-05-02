<?php
if (!defined('IN_CMS')) { exit(); }

/**
 * Gallery
 * 
 * The Gallery plugin for Wolf CMS is a third-party plugin for managing photo albums and displaying them on your website.
 * 
 * @package     Plugins
 * @subpackage  gallery
 * 
 * @author      Nic Wortel <nic.wortel@nth-root.nl>
 * @copyright   Nic Wortel, 2013
 * @version     0.1.0
 */

use_helper('ActiveRecord');
use_helper('Validate');

class Album extends ActiveRecord
{
    const TABLE_NAME = 'gallery_album';

    static $has_many = array(
        'children' => array(
            'class_name' => 'Album',
            'foreign_key' => 'parent_id'
        ),
        'images' => array(
            'class_name' => 'GalleryImage',
            'foreign_key' => 'album_id'
        )
    );
    static $belongs_to = array(
        'parent' => array(
            'class_name' => 'Album',
            'foreign_key' => 'parent_id'
        )
    );

    protected $id;
    protected $title;
    protected $slug;
    protected $description;
    protected $parent_id;
    protected $created_on;
    protected $updated_on;
    protected $created_by_id;
    protected $updated_by_id;
    protected $position;
    // non db fields
    protected $errors = array();
    protected $parent = false;
    protected $uri = false;
    public $images = array();

    public function __get($property)
    {
        $getter = 'get' . Inflector::camelize($property);
        if (method_exists($this, $getter)) {
            return call_user_func(array($this, $getter));
        } elseif (property_exists($this, $property)) {
            return $this->{$property};
        } else {
            throw new Exception('Class '. get_called_class() .' does not have property '. $property);
        }
    }

    public function __set($property, $value)
    {
        $setter = 'set' . Inflector::camelize($property);
        if (method_exists($this, $setter)) {
            return call_user_func(array($this, $setter), $value);
        } else {
            $this->{$property} = $value;
        }
    }

    public function beforeDelete()
    {
        $children = $this->children();
        
        foreach ($children as $child) {
            if (!$child->delete()) {
                return false;
            }
        }

        $images = GalleryImage::findByAlbumId($this->id);

        foreach ($images as $image) {
            if (!$image->delete()) {
                return false;
            }
        }

        $page_albums = PageAlbum::findByAlbumId($this->id);

        foreach ($page_albums as $page_album) {
            if (!$page_album->delete()) {
                return false;
            }
        }
        
        return true;
    }

    public function beforeInsert()
    {
        $this->created_on       = date('Y-m-d H:i:s');
        $this->created_by_id    = AuthUser::getRecord()->id;

        return true;
    }
    
    public function beforeSave()
    {
        $this->slug             = Node::toSlug($this->title);
        
        $this->updated_on       = date('Y-m-d H:i:s');
        $this->updated_by_id    = AuthUser::getRecord()->id;
        
        return true;
    }

    public function afterSave()
    {
        $old_images = $this->images;

        foreach ($old_images as $old_image) {
            $not_in = true;

            if (isset($_POST['images'])) {
                foreach ($_POST['images'] as $key => $image) {
                    if ($old_image->id == $image['id']) {
                        $not_in = false;

                        $old_image->setFromData($image);
                        $old_image->save();

                        unset($_POST['images'][$key]);
                        break;
                    }
                }
            }

            if ($not_in) {
                if (!$old_image->delete()) {
                    print_r($old_image);
                    die;
                }
            }
        }

        foreach ($_POST['images'] as $image) {
            $image = new GalleryImage();
            $image->setFromData($image);
            $image->album_id = $this->id;
            $image->save();
        }
        
        return true;
    }

    public function children()
    {
        return self::childrenOf($this->id);
    }

    public static function childrenOf($parent_id)
    {
        return self::findByParentId($parent_id);
    }

    public static function findAll()
    {
        return self::find();
    }

    public static function findById($id)
    {
        return self::find(array(
            'where' => array('id = ?', (int) $id),
            'limit' => 1,
            'include' => array('images' => array('attachment'))
        ));
    }

    public static function findByParentId($parent_id)
    {
        return self::find(array(
            'where' => array('parent_id = ?', $parent_id),
            'order' => 'position ASC'
        ));
    }

    public static function findRootAlbum()
    {
        return self::find(array(
            'where' => 'parent_id IS NULL',
            'order' => 'id ASC',    
            'limit' => 1
        ));
    }

    public static function findBySlug($slug, &$parent = FALSE) {
        $parent_id = $parent ? $parent->id : 1;
        
        return self::find(array(
            'where' => array('slug = ? AND parent_id = ?', $slug, $parent_id),
            'limit' => 1,
            'include' => array('images' => array('attachment'))
        ));
    }

    public static function findByUri($slugs)
    {
        $url = '';
        
        foreach($slugs as $slug) {
            $url = ltrim($url . '/' . $slug, '/');
            
            if ($album = self::findBySlug($slug, $parent)) {
                
            }
            else {
                break;
            }
            
            $parent = $album;
        }
        
        if (isset($album)) {
            return $album;
        }
        else {
            return false;
        }
    }

    public static function findByUriAndPageId($slugs, $page_id)
    {
        if ($page_album = PageAlbum::findByPageId($page_id)) {
            $album = $page_album->album;
            $parent = $album;

            foreach ($slugs as $slug) {
                if ($album = self::findBySlug($slug, $parent)) {

                } else {
                    break;
                }

                $parent = $album;
            }

            if (isset($album)) {
                return $album;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function getColumns()
    {
        return array(
            'id', 'title', 'slug', 'description', 'parent_id', 'position',
            'created_on', 'updated_on', 'created_by_id', 'updated_by_id'
        );
    }

    public function getErrors()
    {
        return $this->validate();
    }

    public static function hasChildren($id)
    {
        return (boolean) self::countFrom('Album', 'parent_id = ?', array($id));
    }

    public function isValid()
    {
        if (count($this->validate()) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns the Page object's parent.
     *
     * The option $level parameter allows the user to specify the level on
     * which the found Page object should be.
     *
     * @param   int     $level  Optional level parameter
     * @return  Page    The object's parent.
     */
    public function parent($level = null)
    {
        // check to see if it's already been retrieved, if not get the parent!
        //if ($this->parent === false && $this->parent_id != 0) {
        /*if ($this->parent_id != 0) {
            if ($page_album = PageAlbum::findByAlbumId($this->parent_id)) {
                $this->parent = Page::findById($page_album->page_id);
            } else {
                $this->parent = self::findById($this->parent_id);
            }
        }*/

        if ($page_album = PageAlbum::findByAlbumId($this->id)) {
            $this->parent = Page::findById($page_album->page_id)->parent();
        } elseif($this->parent_id != 0) {
            $this->parent = self::findById($this->parent_id);
        }


        if ($level === null)
            return $this->parent;

        if ($level > $this->level())
            return false;
        else if ($this->level() == $level)
            return $this;
        else
            return $this->parent->parent($level);
    }

    public function setTitle($value)
    {
        $this->title = trim($value);
    }

    public function setParentId($value)
    {
        $this->parent_id = (int) $value;
    }

    /**
     * Returns the uri for this node.
     *
     * Note: The uri does not start nor end with a '/'.
     *
     * @return string   The node's full uri.
     */
    public function uri()
    {
        if ($this->uri === false) {
            if ($page_album = PageAlbum::findByAlbumId($this->id)) {
                $slug = Page::findById($page_album->page_id)->slug;
            } else {
                $slug = $this->slug;
            }

            if ($this->parent() !== false) {
                $this->uri = trim($this->parent()->uri().'/'.$slug, '/');
            } else {
                $this->uri = trim($slug, '/');
            }
        }

        return $this->uri;
    }

    /**
     * Returns the current page object's url.
     *
     * Usage: <?php echo $this->url(); ?> or <?php echo $page->url(); ?>
     *
     * @return string   The url of the page object.
     */
    public function url($suffix = true)
    {
        if ($suffix === false) {
            return URL_PUBLIC . $this->uri();
        } else {
            return URL_PUBLIC . $this->uri() . ($this->uri() != '' ? URL_SUFFIX : '');
        }
    }

    private function validate()
    {
        $errors = array();

        if (empty($this->title)) {
            $errors[] = __('You have to specify a title!');
        }

        return $errors;
    }
}