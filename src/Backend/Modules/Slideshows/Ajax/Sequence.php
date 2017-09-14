<?php

namespace Backend\Modules\Slideshows\Ajax;

use Backend\Core\Engine\Base\AjaxAction;
use Backend\Modules\Slideshows\Engine\Model;
use Symfony\Component\HttpFoundation\Response;

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
    public function execute(): void
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', ''));

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
        $this->output(Response::HTTP_OK, null, 'sequence updated');
    }
}
