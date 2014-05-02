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
<p class="button">
    <a href="<?php echo get_url("plugin/gallery/albums"); ?>">
        <img width="32" height="32" src="<?php echo URL_PUBLIC; ?>wolf/icons/file-image-32.png" align="middle" alt="<?php echo __('Albums'); ?>" />
        <?php echo __('Albums'); ?>
    </a>
</p>
<p class="button">
    <a href="<?php echo get_url("plugin/gallery/settings"); ?>">
        <img width="32" height="32" src="<?php echo URL_PUBLIC; ?>wolf/icons/settings-32.png" align="middle" alt="<?php echo __('Settings'); ?>" />
        <?php echo __('Settings'); ?>
    </a>
</p>
<p class="button">
    <a href="<?php echo get_url("plugin/gallery/documentation"); ?>">
        <img width="32" height="32" src="<?php echo URL_PUBLIC; ?>wolf/icons/page-32.png" align="middle" alt="<?php echo __('Documentation'); ?>" />
        <?php echo __('Documentation'); ?>
    </a>
</p>