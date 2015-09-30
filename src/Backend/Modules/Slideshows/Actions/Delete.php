<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model;

/**
 * This action will delete a slideshow
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Delete extends ActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->id = $this->getParameter('id', 'int');

        // get record
        $this->record = Model::get($this->id);

        if (empty($this->record)) {
            $this->redirect(BackendModel::createURLForAction('Index') . '&error=non-existing');
        }

        // delete slideshow
        Model::delete($this->id);

        // item was deleted, so redirect
        $this->redirect(
            BackendModel::createURLForAction('Index') . '&report=deleted&var=' . urlencode($this->record['title'])
        );
    }
}
