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

?>
<h1><?php echo __(ucfirst($action).' album'); ?></h1>

<form method="post" action="<?php if ($action == 'add') echo get_url('plugin/gallery/album/add'); else echo get_url('plugin/gallery/album/edit/' . $album->id); ?>" enctype="multipart/form-data">
    <input id="album_parent_id" name="album[parent_id]" type="hidden" value="<?php echo $album->parent_id; ?>" />    
    <table>
        <tbody>
            <tr>
                <td class="label"><label for="album_title"><?php echo __('Title'); ?></label></td>
                <td class="field"><input class="textbox" type="text" name="album[title]" id="album_title" value="<?php echo $album->title; ?>" /></td>
            </tr>
            <tr>
                <td class="label"><label for="album_description"><?php echo __('Description'); ?></label></td>
                <td class="field">
                    <textarea class="textarea" name="album[description]" id="album_description"><?php echo $album->description; ?></textarea>
                </td>
            </tr>
        </tbody>
    </table>

    <h2><?php echo __('Images'); ?></h2>

    <table id="images">
        <tbody>
            <tr>
                <th class="thumbnail"></th>
                <th><?php echo __('Title'); ?></th>
                <th><?php echo __('Description'); ?></th>
                <th class="delete"><?php echo __('Remove'); ?></th>
            </tr>
            <?php $image_key = 0; ?>
            <?php foreach ($album->images as $image): ?>
            <tr>
                <?php if ($image->id > 0): ?>
                <input type="hidden" name="images[<?php echo $image_key; ?>][id]" value="<?php echo $image->id; ?>" />
                <?php endif; ?>
                <td class="thumbnail"><?php echo $image->attachment->html_img('crop', 60); ?></td>
                <td><input type="text" name="images[<?php echo $image_key; ?>][title]" value="<?php echo $image->title; ?>" /></td>
                <td><input type="text" name="images[<?php echo $image_key; ?>][description]" value="<?php echo $image->description; ?>" /></td>
                <td class="delete"><a href="#" class="remove-image"><img width="16" height="16" title="<?php echo __('Remove'); ?>" alt="<?php echo __('Remove'); ?>" src="<?php echo URI_PUBLIC;?>wolf/icons/action-delete-16.png"></a></td>
            </tr>
            <?php $image_key++; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a class="add-images" href="#"><?php echo __('Add existing images to this album'); ?></a> <?php echo __('or'); ?> <a class="add-images" href="#"><?php echo __('upload new images'); ?></a></p>

    <h3><?php echo __('Upload new images'); ?></h3>

    <input class="textbox" type="file" multiple="multiple" name="file[]" id="file" />

    <p><?php echo __('Tip: you can select multiple files at the same time.'); ?></p>
    
    <p class="buttons">
        <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save and Close'); ?>" />
        <input class="button" name="continue" type="submit" accesskey="e" value="<?php echo __('Save and Continue Editing'); ?>" />
        <?php echo __('or'); ?> <a href="<?php echo get_url('plugin/gallery/albums'); ?>"><?php echo __('Cancel'); ?></a>
    </p>
</form>