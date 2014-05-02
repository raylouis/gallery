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

if (!defined('GALLERY')) {
    define('GALLERY', PLUGINS_ROOT.'/gallery');
}

Plugin::setInfos(array(
    'id'                    =>    'gallery',
    'title'                 =>    __('Gallery'),
    'description'           =>    __('Lets you add photo albums to your website.'),
    'type'                  =>    'both',
    'author'                =>    'Nic Wortel',
    'version'               =>    '0.1.0',
    'website'               =>    'http://www.wolfcms.org/',
    'require_wolf_version'  =>    '0.7.7'
));

AutoLoader::addFolder(GALLERY.'/models');

Plugin::addController('gallery', __('Photo albums'), 'gallery_view', true);

Behavior::add('gallery', 'gallery/gallery.php');

Observer::observe('view_page_edit_tab_links', 'gallery_page_album_tab_link');
Observer::observe('view_page_edit_tabs', 'gallery_page_album_tab');
Observer::observe('page_add_after_save', 'gallery_page_album_tab_save');
Observer::observe('page_edit_after_save', 'gallery_page_album_tab_save');
Observer::observe('media_attachment_before_delete', 'gallery_attachment_delete');
Observer::observe('page_delete', 'gallery_page_album_delete_page');

function gallery_page_album_tab_link(&$page) {
    if ($page->behavior_id == 'gallery') {
        echo '<li class="tab"><a href="#page_album">' . __('Album') . '</a></li>';
    }
}

function gallery_page_album_tab(&$page)
{
    if ($page_album = PageAlbum::findByPageId($page->id)) {
        $current = $page_album->album_id;
    } else {
        $current = false;
    }

    echo new View('../../plugins/gallery/views/tab', array(
        'current' => $current,
        'albums' => Album::findAll()
    ));
}

function gallery_page_album_tab_save(&$page)
{
    if ($page_album = PageAlbum::findByPageId($page->id)) {
        if (isset($_POST['page_album']) && $_POST['page_album']['album_id'] != '' && $_POST['page_album']['album_id'] != 'NULL') {
            $page_album->album_id = $_POST['page_album']['album_id'];
            if (!$page_album->save()) {

            }
        } else {
            PageAlbum::deleteByPageId($page->id);
        }
    } else {
        if (isset($_POST['page_album']) && $_POST['page_album']['album_id'] != '' && $_POST['page_album']['album_id'] != 'NULL') {
            $page_album = new PageAlbum();
            $page_album->page_id = $page->id;
            $page_album->album_id = $_POST['page_album']['album_id'];
            $page_album->save();
        }
    }
}

function gallery_page_album_delete_page(&$page)
{
    PageAlbum::deleteByPageId($page->id);
}

function gallery_attachment_delete(&$attachment)
{
    GalleryImage::deleteByAttachmentId($attachment->id);
}