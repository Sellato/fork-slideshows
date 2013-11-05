<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Installer for the slideshow module
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class SlideshowsInstaller extends ModuleInstaller
{
	/**
	 * Install the module
	 */
	public function install()
	{
		// load install.sql
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// add 'blog' as a module
		$this->addModule('slideshows');

		// import locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		// module rights
		$this->setModuleRights(1, 'slideshows');

		// action rights
		$this->setActionRights(1, 'slideshows', 'add');
		$this->setActionRights(1, 'slideshows', 'add_slide');
		$this->setActionRights(1, 'slideshows', 'delete');
		$this->setActionRights(1, 'slideshows', 'delete_slide');
		$this->setActionRights(1, 'slideshows', 'edit');
		$this->setActionRights(1, 'slideshows', 'edit_slide');
		$this->setActionRights(1, 'slideshows', 'index');

        SpoonDirectory::create(FRONTEND_FILES_PATH . '/slideshows');
        SpoonDirectory::create(FRONTEND_FILES_PATH . '/slideshows/source');
        SpoonDirectory::create(FRONTEND_FILES_PATH . '/slideshows/100x');

        SpoonFile::setContent(FRONTEND_FILES_PATH . '/slideshows/source/.gitignore', "*\n!.gitignore");
        SpoonFile::setContent(FRONTEND_FILES_PATH . '/slideshows/100x/.gitignore', "*\n!.gitignore");

		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
        $this->setNavigation($navigationModulesId, 'Slideshows', 'slideshows/index', array('slideshows/add', 'slideshows/add_slide', 'slideshows/edit', 'slideshows/edit_slide'));
	}
}
