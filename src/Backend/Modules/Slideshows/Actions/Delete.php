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
    public function execute(): void
    {
        parent::execute();

        $this->id = $this->getRequest()->query->getInt('id');

        // get record
        $this->record = Model::get($this->id);

        if (empty($this->record)) {
            $redirectURL = BackendModel::createURLForAction(
                'Index',
                null,
                null,
                [
                    'error' => 'non-existing',
                ]
            );
            $this->redirect($redirectURL);
        }

        // delete slideshow
        Model::delete($this->id);

        // item was deleted, so redirect
        $redirectURL = BackendModel::createURLForAction(
            'Index',
            null,
            null,
            [
                'report' => 'deleted',
                'var' => urlencode($this->record['title']),
            ]
        );
        $this->redirect($redirectURL);
    }
}
