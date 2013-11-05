<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action (default), it will display the slideshows-overview
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendSlideshowsIndex extends BackendBaseActionIndex
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->loadDataGrid();
        $this->parse();
        $this->display();
    }

    /**
     * Load the datagrid
     */
    public function loadDataGrid()
    {
        $this->dataGrid = new BackendDataGridDB(BackendSlideshowsModel::QRY_BROWSE, BL::getWorkingLanguage());

        $this->dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[id]');
        $this->dataGrid->setColumnFunction(array('BackendDataGridFunctions', 'getLongdate'), array('[created_on]'), 'created_on');
        $this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit') . '&amp;id=[id]');
    }

    /**
     * Parse the datagrid
     */
    protected function parse()
    {
        parent::parse();

        if($this->dataGrid->getContent() != '') $this->tpl->assign('dataGrid', $this->dataGrid->getContent());
    }
}
