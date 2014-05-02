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

class Gallery
{
    public function __construct(&$page, $params)
    {
        $this->page = & $page;
        $this->params = $params;

        if ($album = Album::findByUriAndPageId($params, $page->id)) {

        } else {
            page_not_found();
        }

        $this->page->title                      = $album->title;
        $this->page->part->body->content_html .= new View('../../plugins/gallery/views/frontend/album', array(
            'album' => $album
        ));
    }
}