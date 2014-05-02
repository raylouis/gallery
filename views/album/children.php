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
<ul<?php if ($level == 1) echo ' id="site-map" class="sortable tree-root"'; else echo ' class="sortable child"'; ?>>
<?php foreach($children as $child): ?> 
    <li id="page_<?php echo $child->id; ?>" class="node level-<?php echo $level; if ( ! $child->has_children) echo ' no-children'; else if ($child->is_expanded) echo ' children-visible'; else echo ' children-hidden'; ?>">
      <span>
      <div class="page">
        <span class="w1">
          <?php if ($child->has_children): ?><img align="middle" alt="toggle children" class="expander<?php if($child->is_expanded) echo ' expanded'; ?>" src="<?php echo URI_PUBLIC;?>wolf/admin/images/<?php echo $child->is_expanded ? 'collapse': 'expand'; ?>.png" title="" /><?php endif; ?>
            <a class="edit-link" href="<?php echo get_url('plugin/gallery/album/edit/'.$child->id); ?>" title="<?php echo $child->id.' | '.$child->slug; ?>"><img align="middle" class="icon" src="<?php echo URI_PUBLIC;?>wolf/icons/file-folder-32.png" alt="page icon" /> <span class="title"><?php echo $child->title; ?></span></a> <img class="handle_reorder" src="<?php echo URI_PUBLIC;?>wolf/admin/images/drag_to_sort.gif" alt="<?php echo __('Drag and Drop'); ?>" /> <img class="handle_copy" src="<?php echo URI_PUBLIC;?>wolf/admin/images/drag_to_copy.gif" alt="<?php echo __('Drag to Copy'); ?>" align="middle" />
          <img alt="" class="busy" id="busy-<?php echo $child->id; ?>" src="<?php echo URI_PUBLIC;?>wolf/admin/images/spinner.gif" title="" />
        </span>
      </div>
        <div class="view-page">
            <a href="<?php echo $child->url(); ?>" target="_blank"><img src="<?php echo URI_PUBLIC;?>wolf/icons/action-open-16.png" align="middle" alt="<?php echo __('View album'); ?>" title="<?php echo __('View album'); ?>" /></a>
        </div>
      <div class="modify">
        <?php if (AuthUser::hasPermission('catalog_category_add')): ?>
            <a class="add-child-link" href="<?php echo get_url('plugin/gallery/album/add', $child->id); ?>"><img src="<?php echo URI_PUBLIC;?>wolf/icons/action-add-16.png" align="middle" title="<?php echo __('Add child'); ?>" alt="<?php echo __('Add child'); ?>" /></a>&nbsp;
        <?php endif; ?>
        
        <?php if (AuthUser::hasPermission('catalog_category_delete')): ?>
            <a class="remove" href="<?php echo get_url('plugin/gallery/album/delete', $child->id); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete :albumtitle and its underlying albums?', array(':albumtitle' => $child->title)); ?>');"><img src="<?php echo URI_PUBLIC;?>wolf/icons/action-delete-16.png" alt="<?php echo __('Delete'); ?>" title="<?php echo __('Delete'); ?>" /></a>
        <?php endif; ?>
      </div>
      </span>
<?php if ($child->has_children && $child->is_expanded) echo $child->children_rows; ?>
    </li>
<?php endforeach; ?>
</ul>