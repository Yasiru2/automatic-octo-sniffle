<?php
/**
 * FW Food menu 2.0.0
 * @copyright (C) 2017 Fastw3b
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.fastw3b.net/ Official website
 **/

defined('_JEXEC') or die('Restricted access');

if (!function_exists('com_install')) {
	function com_install() {
		com_fwfoodmenuInstallerScript :: update($adaptor = null);
	}
}

if (!class_exists('com_fwfoodmenuInstallerScript')) {
	class com_fwfoodmenuInstallerScript {
		function install($adaptor) {
			return $this->update($adaptor);
		}
		function update($adaptor) {
			$db = JFactory :: getDBO();
			$tables = $db->getTableList();

			if (!in_array($db->getPrefix().'fwfoodmenu_menu_price', $tables)) {
				$db->setQuery('CREATE TABLE `#__fwfoodmenu_menu_price` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`menu_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ordering` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`name` VARCHAR(50) NULL DEFAULT NULL,
	`size` VARCHAR(50) NULL DEFAULT NULL,
	`price` DECIMAL(10,2) NULL DEFAULT NULL,
	`special_price` DECIMAL(10,2) NULL DEFAULT NULL,
	`energy` VARCHAR(20) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `name` (`name`),
	INDEX `ordering` (`ordering`),
	INDEX `menu_id` (`menu_id`))'
				);
				if ($db->execute()) {
					$db->setQuery('INSERT INTO `#__fwfoodmenu_menu_price` (menu_id, size, price, special_price, energy) SELECT id, weight, price, special_price, energy FROM `#__fwfoodmenu_menu`');
					$db->execute();
				}
			}
			$db->setQuery('SHOW FIELDS FROM #__fwfoodmenu_category');
			$fields = $db->loadColumn();

			if (!in_array('parent', $fields)) {
				$db->setQuery('ALTER TABLE `#__fwfoodmenu_category` ADD COLUMN parent int unsigned not null default 0, add index(parent)');
				$db->execute();
			}
			if (!in_array('params', $fields)) {
				$db->setQuery('ALTER TABLE `#__fwfoodmenu_category` ADD COLUMN params TEXT NULL');
				$db->execute();
			}
			if (!in_array('alias', $fields)) {
				$db->setQuery('ALTER TABLE `#__fwfoodmenu_category` ADD COLUMN alias VARCHAR(200) NULL');
				if ($db->execute()) {
					$db->setQuery('SELECT id, name FROM `#__fwfoodmenu_category`');
					if ($list = $db->loadObjectList()) {
						foreach ($list as $row) {
							if (!$row->name) continue;
							$db->setQuery('UPDATE `#__fwfoodmenu_category` SET alias = '.$db->quote(JFilterOutput::stringURLSafe($row->name)).' WHERE id = '.(int)$row->id);
							$db->execute();
						}
					}
				}
			}
			if (!in_array('short_description', $fields)) {
				$db->setQuery('ALTER TABLE `#__fwfoodmenu_category` ADD COLUMN short_description varchar(250) null');
				$db->execute();
			}
			if (!in_array('pdf_menu', $fields)) {
				$db->setQuery('ALTER TABLE `#__fwfoodmenu_category` ADD COLUMN pdf_menu varchar(250) null');
				$db->execute();
			}

			$db->setQuery('SHOW FIELDS FROM #__fwfoodmenu_menu');
			$fields = $db->loadColumn();

			if (!in_array('is_new', $fields)) {
				$db->setQuery('ALTER TABLE `#__fwfoodmenu_menu` ADD COLUMN is_new tinyint unsigned default NULL');
				$db->execute();
			}
			if (!in_array('is_recommended', $fields)) {
				$db->setQuery('ALTER TABLE `#__fwfoodmenu_menu` ADD COLUMN is_recommended tinyint unsigned default NULL');
				$db->execute();
			}
			if (!in_array('image_size', $fields)) {
				$db->setQuery('ALTER TABLE `#__fwfoodmenu_menu` ADD COLUMN image_size int unsigned default NULL');
				$db->execute();
				$db->setQuery('SELECT id, image FROM `#__fwfoodmenu_menu` WHERE image IS NOT NULL');
				if ($list = $db->loadObjectList()) {
					$path = JPATH_SITE.'/media/com_fwfoodmenu/menu/';
					foreach ($list as $row) {
						if ($row->image and file_exists($path.$row->image)) {
							$db->setQuery('UPDATE `#__fwfoodmenu_menu` SET image_size = '.(int)filesize($path.$row->image).' WHERE id = '.(int)$row->id);
							$db->execute();
						}
					}
				}
			}
			jimport('joomla.filesystem.file');
			$path = JPATH_SITE.'/components/com_fwfoodmenu/views/menu/tmpl/grid.xml';
			if (file_exists($path)) {
				JFile::delete($path);
			}

			return true;
		}
	}
}
