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

class PageAlbum extends ActiveRecord
{
    const TABLE_NAME = 'page_album';

    static $belongs_to = array(
        'album' => array(
            'class_name' => 'Album',
            'foreign_key' => 'album_id'
        )
    );

    public $id;
    
    public $page_id;
    public $album_id;

    public static function deleteByAlbumId($album_id)
    {
        if ($page_album = PageAlbum::findByAlbumId($album_id)) {
            $page_album->delete();
        }
    }
    
    public static function deleteByPageId($page_id)
    {
        if ($page_album = PageAlbum::findByPageId($page_id)) {
            $page_album->delete();
        }
    }

    public static function findByAlbumId($album_id)
    {
        return self::find(array(
            'where' => array('album_id = ?', $album_id),
            'limit' => 1,
            'include' => array('album')
        ));
    }

    public static function findByPageId($page_id)
    {
        return self::find(array(
            'where' => array('page_id = ?', $page_id),
            'limit' => 1,
            'include' => array('album')
        ));
    }

    public static function findByParentPage($parent_id, $limit = null)
    {
        
    }

    public function getColumns()
    {
        return array(
            'id', 'page_id', 'album_id'
        );
    }
}