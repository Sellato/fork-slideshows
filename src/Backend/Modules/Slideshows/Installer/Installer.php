<?php

namespace Backend\Modules\Slideshows\Installer;

use Backend\Core\Installer\ModuleInstaller;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Installer for the slideshow module
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Installer extends ModuleInstaller
{
    /**
     * Install the module
     */
    public function install()
    {
        // load install.sql
        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');

        // add 'slideshow' as a module
        $this->addModule('Slideshows');

        // import locale
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        // module rights
        $this->setModuleRights(1, 'Slideshows');

        // action rights
        $this->setActionRights(1, 'Slideshows', 'Add');
        $this->setActionRights(1, 'Slideshows', 'AddSlide');
        $this->setActionRights(1, 'Slideshows', 'Delete');
        $this->setActionRights(1, 'Slideshows', 'DeleteSlide');
        $this->setActionRights(1, 'Slideshows', 'Edit');
        $this->setActionRights(1, 'Slideshows', 'EditSlide');
        $this->setActionRights(1, 'Slideshows', 'Index');

        // set navigation
        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $this->setNavigation(
            $navigationModulesId,
            'Slideshows',
            'slideshows/index',
            array('slideshows/add', 'slideshows/add_slide', 'slideshows/edit', 'slideshows/edit_slide')
        );
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesSettingsId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesSettingsId, 'Slideshows', 'slideshows/settings');

        // create folders if needed
        $imagePath = FRONTEND_FILES_PATH . '/Slideshows';
        $gitIgnore = '*' . PHP_EOL . '!.gitignore';
        $fs = new Filesystem();

        if (!$fs->exists($imagePath . '/source')) {
            $fs->mkdir($imagePath . '/source');
            $fs->dumpFile($imagePath . '/source/.gitignore', $gitIgnore);
        }
        if (!$fs->exists($imagePath . '/100x')) {
            $fs->mkdir($imagePath . '/100x');
            $fs->dumpFile($imagePath . '/100x/.gitignore', $gitIgnore);
        }
    }
}
