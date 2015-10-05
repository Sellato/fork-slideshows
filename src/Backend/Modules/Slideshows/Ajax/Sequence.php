<?php

namespace Backend\Modules\Slideshows\Ajax;

use Backend\Core\Engine\Base\AjaxAction;
use Backend\Modules\Slideshows\Engine\Model;

/**
 * Reorder categories
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Sequence extends AjaxAction
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
            Model::updateSlide($item);
        }

        // success output
        $this->output(self::OK, null, 'sequence updated');
    }
}
