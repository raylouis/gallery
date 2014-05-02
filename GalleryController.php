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

class GalleryController extends PluginController
{
    const PLUGIN_NAME = 'gallery';

    public function __construct() {
        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('../../plugins/gallery/views/sidebar'));
    }

    private function upload($album_id) {
        $errors = false;
        
        if (isset($_FILES['file'])) {
            $uploaded_files = array();

            foreach ($_FILES['file']['name'] as $key => $name) {
                $file = array(
                    'name'      => $_FILES['file']['name'][$key],
                    'type'      => $_FILES['file']['type'][$key],
                    'tmp_name'  => $_FILES['file']['tmp_name'][$key],
                    'error'     => $_FILES['file']['error'][$key],
                    'size'      => $_FILES['file']['size'][$key]
                );

                $file = new UploadedFile($file);
                if ($file->error > 0) {
                    $errors[] = $file->errorMessage();
                }

                $uploaded_files[] = $file;
            }
        }
        
        if ($errors !== false) {
            Flash::setNow('error', implode('<br />', $errors));
        } else {
            foreach ($uploaded_files as $key => $file) {
                $filename = pathinfo($file->name, PATHINFO_FILENAME);
                $extension = pathinfo($file->name, PATHINFO_EXTENSION);

                $data = array(
                    'title' => $filename,
                    'filename' => str_replace(' ','-',$file->name),
                    'mime_type' => $file->type
                );
                
                $attachment = new Attachment($data);
                
                $to = CMS_ROOT . DS . 'public' . DS . 'media_uploads' . DS . $attachment->filename;
                
                if ($file->moveTo($to)) {
                    if (!$attachment->save()) {
                        print_r($attachment);
                        die;
                    }

                    $image = new GalleryImage();
                    $image->attachment_id = $attachment->id;
                    $image->album_id = $album_id;
                    $image->save();
                }
            }
        }
    }

    private function store($model, $action, $id = FALSE)
    {
        $models = array(
            'album' => 'albums'
        );

        if (!isset($models[$model])) {
            return false;
        }

        $model_name = array();
        $model_name['camelized'] = Inflector::camelize($model);
        $model_name['underscored'] = Inflector::underscore($model);
        $model_name['humanized'] = Inflector::humanize($model);

        if ($action == 'edit' && !$id) {
            throw new Exception(__('Trying to edit :model when $id is false.', array(':model' => __($model_name['humanized']))));
        }

        $data = $_POST[$model];
        Flash::set('post_data', (object) $data);

        if ($action == 'add') {
            $obj = new $model_name['camelized']($data);
            $obj->setFromData($data);
        } else {
            $obj = $model_name['camelized']::findById($id);
            $obj->setFromData($data);
        }

        if ($obj->isValid()) {
            if ($obj->save()) {
                Flash::set('success', __(':model has been saved!', array(':model' => __($model_name['humanized']))));
            } else {
                Flash::set('error', __(':model has not been saved!', array(':model' => __($model_name['humanized']))));
            
                $url = 'plugin/gallery/' . $model . '/';
                $url .= ( $action == 'edit') ? 'edit/' . $id : 'add/';
                redirect(get_url($url));
            }
        } else {
            Flash::setNow('error', implode('<br/>', $obj->getErrors()));

            if ($model == 'album') {
                $this->display('gallery/views/album/edit', array(
                    'action' => $action,
                    'album' => $obj
                ));
            }
        }

        if ($model = 'album') {
            $this->upload($obj->id);
        }

        if (isset($_POST['commit'])) {
            redirect(get_url('plugin/gallery/' . $models[$model]));
        } else {
            redirect(get_url('plugin/gallery/' . $model . '/edit/' . $obj->id));
        }
    }

    public function album($action, $id = NULL)
    {
        if ($action == 'add') {
            if (get_request_method() == 'POST') {
                return $this->store('album', 'add', $id);
            }

            if (!is_numeric($id)) {
                redirect(get_url('plugin/gallery/albums'));
            }

            $data = Flash::get('post_data');
            $data['parent_id'] = (int) $id;
            $album = new Album();
            if (!is_null($data)) {
                $album->setFromData($data);
            }

            $this->display('gallery/views/album/edit', array(
                'action' => 'add',
                'album' => $album
            ));
        } elseif ($action == 'delete') {
            if (is_numeric($id)) {
                if ($album = Album::findById($id)) {
                    if ($album->delete()) {
                        Flash::set('success', __("Album ':title' has been deleted!", array(':title' => $album->title)));
                    } else {
                        Flash::set('error', __("An error has occured, therefore ':title' could not be deleted!", array(':title' => $album->title)));
                    }
                } else {
                    Flash::set('error', __('The album could not be found!'));
                }
            } else {
                Flash::set('error', __('The album could not be found!'));
            }

            redirect(get_url('plugin/gallery/albums'));
        } elseif ($action == 'edit') {
            if (is_numeric($id)) {
                if (get_request_method() == 'POST') {
                    return $this->store('album', 'edit', $id);
                }

                if ($album = Album::findById($id)) {
                    $this->display('gallery/views/album/edit', array(
                        'action' => 'edit',
                        'album' => $album
                    ));
                } else {
                    Flash::set('error', __('The album could not be found!'));
                    redirect(get_url('plugin/gallery/albums'));
                }
            } else {
                Flash::set('error', __('The album could not be found!'));
                redirect(get_url('plugin/gallery/albums'));
            }
        } else {
            redirect(get_url('plugin/gallery/albums'));
        }
    }

    public function albumChildren($parent_id, $level, $return=false)
    {
        $expanded_rows = isset($_COOKIE['gallery_album_expanded_rows']) ? explode(',', $_COOKIE['gallery_album_expanded_rows']) : array();

        // get all children of the page (parent_id)
        $children = Album::childrenOf($parent_id);

        foreach ($children as $index => $child) {
            $children[$index]->has_children = Album::hasChildren($child->id);
            $children[$index]->is_expanded = in_array($child->id, $expanded_rows);

            if ($children[$index]->has_children && $children[$index]->is_expanded) {
                $children[$index]->children_rows = $this->albumChildren($child->id, $level + 1, true);
            }
        }

        $content = new View('../../plugins/gallery/views/album/children', array(
            'children' => $children,
            'level' => $level + 1,
            'settings' => Plugin::getAllSettings(self::PLUGIN_NAME)
        ));

        if ($return) {
            return $content;
        }
        else {
            echo $content;
        }
    }

    public function albums()
    {
        $root = Album::findRootAlbum();

        $this->display('gallery/views/album/index', array(
            'root' => $root,
            'content_children' => $this->albumChildren($root->id, 0, true)
        ));
    }

    public function index()
    {
        $this->albums();
    }

    public function reorder($model = NULL)
    {
        if ($model == 'album') {
            if ($albums = $_POST['page']) {
                $i = array();
                foreach ($albums as $album_id => $parent_id) {
                    if (!isset($i[$parent_id])) {
                        $i[$parent_id] = 1;
                    }
                    $album = Album::findById($album_id);
                    $album->position = (int) $i[$parent_id];
                    $album->parent_id = (int) $parent_id;
                    $album->save();
                    $i[$parent_id]++;
                }
            }
        }
    }
}