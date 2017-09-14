<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Language\Language;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model;

/**
 * This is the index-action (default), it will display the overview
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Index extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();
        $this->loadDataGrid();

        $this->parse();
        $this->display();
    }

    public function loadDataGrid(): void
    {
        $dataGrid = new DataGridDatabase(Model::QRY_BROWSE, [Language::getWorkingLanguage()]);

        $dataGrid->setColumnURL('title', BackendModel::createURLForAction('Edit') . '&amp;id=[id]');
        $dataGrid->setColumnFunction(
            [new DataGridFunctions(), 'getLongDate'],
            ['[created_on]'],
            'created_on',
            true
        );
        $dataGrid->addColumn(
            'edit',
            null,
            Language::lbl('Edit'),
            BackendModel::createURLForAction('edit') . '&amp;id=[id]'
        );

        $this->template->assign('dataGrid', $dataGrid->getContent());
    }
}
