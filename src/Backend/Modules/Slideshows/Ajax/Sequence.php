<?php

namespace Backend\Modules\Slideshows\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Slideshows\Engine\Model as BackendSlideshowsModel;

/**
 * Reorder categories
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Sequence extends BackendBaseAJAXAction
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim(\SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {
            // build item
            $item['id'] = (int) $id;

            // change sequence
            $item['sequence'] = $i;

            // update sequence
            BackendSlideshowsModel::updateSlide($item);
        }

        // success output
        $this->output(self::OK, null, 'sequence updated');
    }
}
