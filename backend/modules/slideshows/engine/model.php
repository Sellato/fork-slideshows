<?php

/**
 * In this file we store all generic functions that we will be using in the slideshows module
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendSlideshowsModel
{
	const QRY_BROWSE = 'SELECT i.id, i.title, UNIX_TIMESTAMP(i.created_on) AS created_on
									FROM slideshows AS i
									WHERE i.language = ?';

	/**
	 * Deletes one or more items
	 *
	 * @param mixed $ids The ids to delete.
	 */
	public static function delete($id)
	{
		// get db
		$db = BackendModel::getContainer()->get('database');

		// get item
		$item = self::get($id);

		// delete records
		$db->delete('modules_extras', 'id = ?', array($item['extra_id']));
		$db->delete('slideshows', 'id = ? AND language = ?', array($id, BL::getWorkingLanguage()));
	}

    /**
     * Deletes one or more items
     *
     * @param mixed $ids The ids to delete.
     */
    public static function deleteSlide($id)
    {
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
		return (bool) BackendModel::getContainer()->get('database')->getVar('SELECT i.id
														FROM slideshows AS i
														WHERE i.id = ? AND i.language = ?',
														array((int) $id, BL::getWorkingLanguage()));
	}

    /**
     * Checks if a slide exists
     *
     * @param int $id The id of the item to check for existence.
     * @return bool
     */
    public static function existsSlide($id)
    {
        return (bool) BackendModel::getContainer()->get('database')->getVar('SELECT i.id
														FROM slideshows_slides AS i
														WHERE i.id = ?',
            array((int) $id));
    }

    /**
     * Generate thumbnails
     *
     * @param string $filename
     * @return void
     */
    public static function generateThumbnails($filename)
    {
        $folderPath = FRONTEND_FILES_PATH . '/slideshows';
        if(SpoonFile::exists($folderPath . '/source/' . $filename))
        {
            $sizes = SpoonDirectory::getList($folderPath, false, array('source'));
            foreach($sizes as $size)
            {
                $sizeChunks = explode('x', $size);
                if($size[0] == '') $size[0] = null;
                if($size[1] == '') $size[1] = null;
                $thumb = new SpoonThumbnail($folderPath . '/source/' . $filename, $size[0], $size[1]);
                $thumb->setForceOriginalAspectRatio(false);
                $thumb->setStrict(false);
                $thumb->parseToFile($folderPath . '/' . $size . '/' . $filename);
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
		return (array) BackendModel::getContainer()->get('database')->getRecord('SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on
															FROM slideshows AS i
															WHERE i.id = ? AND i.language = ?',
															array((int) $id, BL::getWorkingLanguage()));
	}

    /**
     * Get all data for a given id
     *
     * @param int $id The Id of the item to fetch?
     * @return array
     */
    public static function getSlide($id)
    {
        $data = (array) BackendModel::getContainer()->get('database')->getRecord('SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on
															FROM slideshows_slides AS i
															WHERE i.id = ?',
            array((int) $id));

        if(empty($data)) return array();

        $data['image_preview'] = FRONTEND_FILES_URL . '/slideshows/100x/' . $data['image'];

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
}
