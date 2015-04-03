<?php

namespace Backend\Modules\Slideshows\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model as BackendSlideshowsModel;

/**
 * This action will delete a slideshow
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Delete extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // group exists and id is not null?
        if ($this->id !== null && BackendSlideshowsModel::exists($this->id)) {
            parent::execute();

            // get record
            $this->record = BackendSlideshowsModel::get($this->id);

            // delete group
            BackendSlideshowsModel::delete($this->id);

            // trigger event
            BackendModel::triggerEvent($this->getModule(), 'after_delete', array('id' => $this->id));

            // item was deleted, so redirect
            $this->redirect(
                BackendModel::createURLForAction('Index') . '&report=deleted&var=' . urlencode($this->record['title'])
            );
        } else {
            // no item found, redirect to the overview with an error
            $this->redirect(BackendModel::createURLForAction('Index') . '&error=non-existing');
        }
    }
}
