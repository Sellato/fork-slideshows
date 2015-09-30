<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model as BackendSlideshowsModel;

/**
 * This is the index-action (default), it will display the overview
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Index extends BackendBaseActionIndex
{
    public function execute()
    {
        parent::execute();
        $this->loadDataGrid();

        $this->parse();
        $this->display();
    }

    public function loadDataGrid()
    {
        $this->dataGrid = new BackendDataGridDB(BackendSlideshowsModel::QRY_BROWSE, BL::getWorkingLanguage());

        $this->dataGrid->setColumnURL('title', BackendModel::createURLForAction('Edit') . '&amp;id=[id]');
        $this->dataGrid->setColumnFunction(
            array(new BackendDataGridFunctions(), 'getLongDate'),
            array('[created_on]'),
            'created_on',
            true
        );
        $this->dataGrid->addColumn(
            'edit',
            null,
            BL::lbl('Edit'),
            BackendModel::createURLForAction('edit') . '&amp;id=[id]'
        );

    }

    protected function parse()
    {
        parent::parse();

        if ($this->dataGrid->getContent() != '') {
            $this->tpl->assign('dataGrid', $this->dataGrid->getContent());
        }
    }
}
