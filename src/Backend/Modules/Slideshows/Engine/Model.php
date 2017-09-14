<?php

namespace Backend\Modules\Slideshows\Engine;

use Frontend\Core\Engine\Theme;
use Backend\Core\Language\Language;
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

    public static function delete(int $id): void
    {
        // get db
        $db = BackendModel::getContainer()->get('database');

        // get item
        $item = self::get($id);

        // delete the slides one by one so also all the files are deleted
        foreach (self::getSlidesForSlideshow($id) as $slide) {
            self::deleteSlide($slide['id']);
        }

        // delete records
        $db->delete('modules_extras', 'id = ?', [$item['extra_id']]);
        $db->delete('slideshows', 'id = ? AND language = ?', [$id, Language::getWorkingLanguage()]);
    }

    public static function deleteSlide(int $id): void
    {
        // get the slide so we can delete the image
        $slide = self::getSlide($id);
        BackendModel::deleteThumbnails(FRONTEND_FILES_PATH . self::IMAGE_FOLDER, $slide['image']);

        // get db
        $db = BackendModel::getContainer()->get('database');

        // delete records
        $db->delete('slideshows_slides', 'id = ?', [$id]);
    }

    public static function exists(int $id): bool
    {
        return (bool) BackendModel::getContainer()->get('database')->getVar(
            'SELECT i.id
             FROM slideshows AS i
             WHERE i.id = ? AND i.language = ?',
            [$id, Language::getWorkingLanguage()]
        );
    }

    public static function existsSlide(int $id): bool
    {
        return (bool) BackendModel::getContainer()->get('database')->getVar(
            'SELECT i.id
             FROM slideshows_slides AS i
             WHERE i.id = ?',
            [$id]
        );
    }

    public static function generateThumbnails(string $filename): void
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

    public static function get(int $id): array
    {
        return (array) BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on
             FROM slideshows AS i
             WHERE i.id = ? AND i.language = ?',
            [$id, Language::getWorkingLanguage()]
        );
    }

    public static function getSlide(int $id): array
    {
        $data = (array) BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on
             FROM slideshows_slides AS i
             WHERE i.id = ?',
            [$id]
        );

        if (empty($data)) {
            return [];
        }

        $data['image_preview'] = FRONTEND_FILES_URL . self::IMAGE_FOLDER . '100x/' . $data['image'];

        return $data;
    }
    public static function getSlidesForSlideshow(int $id): array
    {
        $slides = (array) BackendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on
             FROM slideshows_slides AS i
             WHERE i.slideshow_id = ?',
            [$id]
        );

        if (empty($slides)) {
            return [];
        }

        return array_map(
            function (array $slide) {
                $slide['image_preview'] = FRONTEND_FILES_URL . self::IMAGE_FOLDER . '100x/' . $slide['image'];

                return $slide;
            },
            $slides
        );
    }

    public static function insert(array $item): int
    {
        return BackendModel::getContainer()->get('database')->insert('slideshows', $item);
    }

    public static function insertSlide(array $item): int
    {
        return BackendModel::getContainer()->get('database')->insert('slideshows_slides', $item);
    }

    public static function update(array $item): void
    {
        BackendModel::getContainer()->get('database')->update('slideshows', $item, 'id = ?', $item['id']);
    }

    public static function updateSlide(array $item): void
    {
        BackendModel::getContainer()->get('database')->update('slideshows_slides', $item, 'id = ?', $item['id']);
    }

    public static function getNextSlideSequence(int $slideshowId): int
    {
        $lastSequence = (int) BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(sequence)
             FROM slideshows_slides
             WHERE slideshow_id = ?',
            [$slideshowId]
        );

        return ($lastSequence + 1);
    }

    public static function getPossibleTemplates(): array
    {
        $templates = [];
        $finder = new Finder();
        $finder->name('*.html.twig');
        $finder->in(FRONTEND_MODULES_PATH . '/Slideshows/Layout/Widgets');

        // if there is a custom theme we should include the templates there also
        $path = FRONTEND_PATH . '/Themes/' . Theme::getTheme() . '/Modules/Slideshows/Layout/Widgets';
        if (is_dir($path)) {
            $finder->in($path);
        }

        foreach ($finder->files() as $file) {
            $templates[] = $file->getBasename();
        }

        return array_combine($templates, $templates);
    }
}
