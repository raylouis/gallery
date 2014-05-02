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

class GalleryImage extends ActiveRecord
{
    const TABLE_NAME = 'gallery_image';

    static $belongs_to = array(
        'album' => array(
            'class_name' => 'Album',
            'foreign_key' => 'album_id'
        ),
        'attachment' => array(
            'class_name' => 'Attachment',
            'foreign_key' => 'attachment_id'
        )
    );

    protected $id;
    protected $title;
    protected $description;
    protected $album_id;
    protected $attachment_id;
    protected $position;

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

    public static function deleteByAlbumId($album_id)
    {
        $images = self::findByAlbumId($album_id);

        foreach ($images as $image) {
            if (!$image->delete()) {
                return false;
            }
        }
    }

    public static function deleteByAttachmentId($attachment_id)
    {
        $images = self::findByAttachmentId($attachment_id);

        foreach ($images as $image) {
            if (!$image->delete()) {
                return false;
            }
        }
    }

    public static function findByAlbumId($album_id)
    {
        return self::find(array(
            'where' => array('album_id = ?', $album_id),
            'order' => 'position ASC'
        ));
    }

    public static function findByAttachmentId($attachment_id)
    {
        return self::find(array(
            'where' => array('attachment_id = ?', $attachment_id),
            'order' => 'position ASC'
        ));
    }

    public function getColumns()
    {
        return array(
            'id', 'title', 'description', 'album_id', 'attachment_id', 'position'
        );
    }

    public function title()
    {
        return $this->title;
    }
}