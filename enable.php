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

$PDO = Record::getConnection();

$PDO->exec("CREATE  TABLE IF NOT EXISTS `" . TABLE_PREFIX . "gallery_album` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(255) NOT NULL ,
  `slug` VARCHAR(255) NOT NULL ,
  `description` TEXT NULL DEFAULT '' ,
  `parent_id` INT UNSIGNED NULL ,
  `created_on` DATETIME NOT NULL ,
  `updated_on` DATETIME NOT NULL ,
  `created_by_id` INT UNSIGNED NOT NULL ,
  `updated_by_id` INT UNSIGNED NOT NULL ,
  `position` INT UNSIGNED NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `slug_UNIQUE` (`slug` ASC, `parent_id` ASC) ,
  INDEX `fk_album_album1` (`parent_id` ASC) ,
  CONSTRAINT `fk_album_album1`
    FOREIGN KEY (`parent_id` )
    REFERENCES `" . TABLE_PREFIX . "gallery_album` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB");

$PDO->exec("CREATE  TABLE IF NOT EXISTS `" . TABLE_PREFIX . "gallery_image` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(255) NULL ,
  `description` TEXT NULL ,
  `album_id` INT UNSIGNED NOT NULL ,
  `attachment_id` INT UNSIGNED NOT NULL ,
  `position` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_item_album` (`album_id` ASC) ,
  INDEX `fk_item_media_attachment1` (`attachment_id` ASC) ,
  CONSTRAINT `fk_item_album`
    FOREIGN KEY (`album_id` )
    REFERENCES `" . TABLE_PREFIX . "gallery_album` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_media_attachment1`
    FOREIGN KEY (`attachment_id` )
    REFERENCES `" . TABLE_PREFIX . "media_attachment` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB");

$sql = "CREATE  TABLE IF NOT EXISTS `".TABLE_PREFIX."page_album` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `page_id` INT UNSIGNED NOT NULL ,
  `album_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_page_album_album1` (`album_id` ASC) ,
  CONSTRAINT `fk_page_album_album1`
    FOREIGN KEY (`album_id` )
    REFERENCES `".TABLE_PREFIX."gallery_album` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB";

$PDO->exec($sql);

$sql = "INSERT INTO `" . TABLE_PREFIX . "gallery_album`
  (
    `title`,
    `slug`,
    `created_on`,
    `updated_on`,
    `created_by_id`,
    `updated_by_id`,
    `position`
  )
SELECT
    '" . __('Gallery') . "',
    '',
    '" . date('Y-m-d H:i:s') . "',
    '" . date('Y-m-d H:i:s') . "',
    '" . AuthUser::getRecord()->id . "',
    '" . AuthUser::getRecord()->id . "',
    0
      FROM Dual
     WHERE (SELECT COUNT(*) FROM `" . TABLE_PREFIX . "gallery_album`) < 1;";

$PDO->exec($sql);