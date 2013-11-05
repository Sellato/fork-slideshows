<?php

/**
 * The frontend-model for the slideshow module
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class FrontendSlideshowsModel
{
    /**
     * get
     *
     * @param int $id
     * @return array
     */
    public static function get($id)
    {
        $return = (array) FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM slideshows WHERE id = ?', $id);
        $return['slides'] = (array) FrontendModel::getContainer()->get('database')->getRecords('SELECT * FROM slideshows_slides WHERE slideshow_id = ? ORDER BY sequence', $return['id']);
        foreach($return['slides'] as &$slide)
        {
            $slide['image_full'] = FRONTEND_FILES_URL . '/slideshows/source/' . $slide['image'];
        }
        return $return;
    }
}