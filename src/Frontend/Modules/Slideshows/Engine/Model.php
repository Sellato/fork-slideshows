<?php

namespace Frontend\Modules\Slideshows\Engine;

use Frontend\Core\Engine\Model as FrontendModel;

/**
 * In this file we store all generic functions that we will be using in the slideshow module
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Model
{
    /**
     * get
     *
     * @param int $id
     * @return array
     */
    public static function get($id)
    {
        $return = (array) FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT *
             FROM slideshows
             WHERE id = ?',
            $id
        );

        if (empty($return)) {
            return array();
        }

        $return['slides'] = (array) FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT *
             FROM slideshows_slides
             WHERE slideshow_id = ?
             ORDER BY sequence',
            $return['id']
        );

        foreach ($return['slides'] as &$slide) {
            $slide['image_full'] = FRONTEND_FILES_URL . '/Slideshows/source/' . $slide['image'];
        }

        return $return;
    }
}
