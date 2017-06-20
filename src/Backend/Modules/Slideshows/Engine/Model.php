<?php

namespace Backend\Modules\Slideshows\Engine;

use Frontend\Core\Engine\Theme;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Model as BackendModel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * In this file we store all generic functions that we will be using in the slideshows module
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 * @author Jelmer Prins <jelmer@sumocoders.be>
 */
class Model
{
    const QRY_BROWSE = 'SELECT i.id, i.title, UNIX_TIMESTAMP(i.created_on) AS created_on
                        FROM slideshows AS i
                        WHERE i.language = ?';

    const IMAGE_FOLDER = '/Slideshows/';

    const MODULE = 'Slideshows';

    /**
     * Deletes one or more items
     *
     * @param int $id The id to delete.
     */
    public static function delete($id)
    {
        // get db
        $db = BackendModel::getContainer()->get('database');

        // get item
        $item = self::get($id);

        // delete records
        $db->delete('modules_extras', 'id = ?', array($item['extra_id']));
        $db->delete('slideshows', 'id = ? AND language = ?', array($id, Language::getWorkingLanguage()));
    }

    /**
     * Deletes one or more items
     *
     * @param int $id The id to delete.
     */
    public static function deleteSlide($id)
    {
        // get the slide so we can delete the image
        $slide = self::getSlide($id);
        BackendModel::deleteThumbnails(FRONTEND_FILES_PATH . self::IMAGE_FOLDER, $slide['image']);

        // get db
        $db = BackendModel::getContainer()->get('database');

        // delete records
        $db->delete('slideshows_slides', 'id = ?', array($id));
    }

    /**
     * Checks if an item exists
     *
     * @param int $id The id of the item to check for existence.
     * @return bool
     */
    public static function exists($id)
    {
        return (bool) BackendModel::getContainer()->get('database')->getVar(
            'SELECT i.id
             FROM slideshows AS i
             WHERE i.id = ? AND i.language = ?',
            array((int) $id, Language::getWorkingLanguage())
        );
    }

    /**
     * Checks if a slide exists
     *
     * @param int $id The id of the item to check for existence.
     * @return bool
     */
    public static function existsSlide($id)
    {
        return (bool) BackendModel::getContainer()->get('database')->getVar(
            'SELECT i.id
             FROM slideshows_slides AS i
             WHERE i.id = ?',
            array((int) $id)
        );
    }

    /**
     * Generate thumbnails
     *
     * @param string $filename
     * @return void
     */
    public static function generateThumbnails($filename)
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $folderPath = FRONTEND_FILES_PATH . self::IMAGE_FOLDER;

        if ($fs->exists($folderPath . 'source/' . $filename)) {
            $sizes = $finder->directories()->in($folderPath)->exclude('source');
            foreach ($sizes as $size) {
                /** @var SplFileInfo $size */
                $sizeChunks = explode('x', $size->getBasename());
                if ($sizeChunks[0] == '') {
                    $sizeChunks[0] = null;
                }
                if ($sizeChunks[1] == '') {
                    $sizeChunks[1] = null;
                }

                $thumb = new \SpoonThumbnail($folderPath . 'source/' . $filename, $sizeChunks[0], $sizeChunks[1]);
                if ($sizeChunks[0] === null && $sizeChunks[1] === 0) {
                    $thumb->setForceOriginalAspectRatio(false);
                }
                $thumb->setStrict(false);
                $thumb->parseToFile($folderPath . $size->getBasename() . '/' . $filename);
            }
        }
    }

    /**
     * Get all data for a given id
     *
     * @param int $id The Id of the item to fetch?
     * @return array
     */
    public static function get($id)
    {
        return (array) BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on
             FROM slideshows AS i
             WHERE i.id = ? AND i.language = ?',
            array((int) $id, Language::getWorkingLanguage())
        );
    }

    /**
     * Get all data for a given id
     *
     * @param int $id The Id of the item to fetch?
     * @return array
     */
    public static function getSlide($id)
    {
        $data = (array) BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on
             FROM slideshows_slides AS i
             WHERE i.id = ?',
            array((int) $id)
        );

        if (empty($data)) {
            return array();
        }

        $data['image_preview'] = FRONTEND_FILES_URL . self::IMAGE_FOLDER . '100x/' . $data['image'];

        return $data;
    }

    /**
     * Inserts an item into the database
     *
     * @param array $item The data to insert.
     * @return int
     */
    public static function insert(array $item)
    {
        return BackendModel::getContainer()->get('database')->insert('slideshows', $item);
    }

    /**
     * Inserts a slide into the database
     *
     * @param array $item The data to insert.
     * @return int
     */
    public static function insertSlide(array $item)
    {
        return BackendModel::getContainer()->get('database')->insert('slideshows_slides', $item);
    }

    /**
     * Update an existing item
     *
     * @param array $item The new data.
     * @return int
     */
    public static function update(array $item)
    {
        BackendModel::getContainer()->get('database')->update('slideshows', $item, 'id = ?', $item['id']);
    }

    /**
     * Update an existing slide
     *
     * @param array $item The new data.
     * @return int
     */
    public static function updateSlide(array $item)
    {
        BackendModel::getContainer()->get('database')->update('slideshows_slides', $item, 'id = ?', $item['id']);
    }

    /**
     * Get next slide sequence
     *
     * @param int $slideshowId
     *
     * @return int
     */
    public static function getNextSlideSequence($slideshowId)
    {
        $lastSequence = (int) BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(sequence)
             FROM slideshows_slides
             WHERE slideshow_id = ?',
            array($slideshowId)
        );

        return ($lastSequence + 1);
    }

    /**
     * @return array
     */
    public static function getPossibleTemplates()
    {
        $templates = array();
        $finder = new Finder();
        $finder->name('*.html.twig');
        $finder->in(FRONTEND_MODULES_PATH . '/' . self::MODULE . '/Layout/Widgets');
        // if there is a custom theme we should include the templates there also
        if (Theme::getTheme() != 'core') {
            $path = FRONTEND_PATH . '/Themes/' . Theme::getTheme() . '/Modules/' . self::MODULE . '/Layout/Widgets';
            if (is_dir($path)) {
                $finder->in($path);
            }
        }
        foreach ($finder->files() as $file) {
            $templates[] = $file->getBasename();
        }

        return array_combine($templates, $templates);
    }
}
