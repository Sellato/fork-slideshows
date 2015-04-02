<?php

namespace Backend\Modules\Slideshows\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model as BackendSlideshowsModel;

/**
 * This is the edit-action, it will display a form to edit an existing slideshow
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Edit extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute()
    {
        // get parameters
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id !== null && BackendSlideshowsModel::exists($this->id)) {
            parent::execute();
            $this->getData();
            $this->loadForm();
            $this->validateForm();
            $this->parse();
            $this->display();
        } else {
            // no item found, throw an exception, because somebody is fucking with our URL
            $this->redirect(BackendModel::createURLForAction('Index') . '&error=non-existing');
        }
    }

    /**
     * Get the data
     * If a revision-id was specified in the URL we load the revision and not the actual data.
     */
    private function getData()
    {
        $this->record = (array) BackendSlideshowsModel::get($this->id);

        // no item found, throw an exceptions, because somebody is fucking with our URL
        if (empty($this->record)) {
            $this->redirect(BackendModel::createURLForAction('Index') . '&error=non-existing');
        }

        $imagesDatagrid = new BackendDataGridDB(
            'SELECT id, image, title, sequence
             FROM slideshows_slides
             WHERE slideshow_id = ?
             ORDER BY sequence',
             array($this->id)
         );
        $imagesDatagrid->enableSequenceByDragAndDrop();
        $imagesDatagrid->addColumn(
            'edit',
            null,
            BL::lbl('Edit'),
            BackendModel::createURLForAction('EditSlide') . '&amp;id=[id]'
        );
        $imagesDatagrid->setColumnFunction(array(__CLASS__, 'generatePreview'), '[image]', 'image');
        if ($imagesDatagrid->getContent() != '') {
            $this->tpl->assign('images', $imagesDatagrid->getContent());
        }
    }

    /**
     * Load the form
     */
    private function loadForm()
    {
        // create form
        $this->frm = new BackendForm('edit');

        // create elements
        $this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
    }

    /**
     * Parse the form
     */
    protected function parse()
    {
        // call parent
        parent::parse();
        // assign the active record and additional variables
        $this->tpl->assign('item', $this->record);
    }

    /**
     * Helper function for generating previews
     *
     * @param $var
     * @return string
     */
    public static function generatePreview($var)
    {
        return '<img src="' . FRONTEND_FILES_URL . '/slideshows/100x/' . $var . '" />';
    }

    /**
     * Validate the form
     */
    private function validateForm()
    {
        // is the form submitted?
        if ($this->frm->isSubmitted()) {
            // get the status
            $status = \SpoonFilter::getPostValue('status', array('active', 'draft'), 'active');

            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));


            // no errors?
            if ($this->frm->isCorrect()) {
                // build item
                $item['id'] = $this->id;
                $item['language'] = BL::getWorkingLanguage();
                $item['title'] = $this->frm->getField('title')->getValue();

                BackendSlideshowsModel::update($item);

                $data = array('id' => $this->id, 'extra_label' => $item['title'], 'language' => $item['language']);
                BackendModel::getContainer()->get('database')->update(
                    'modules_extras',
                    array('data' => serialize($data)),
                    'id = ?',
                    $this->record['extra_id']
                );

                // everything is saved, so redirect to the overview
                $redirectURL = BackendModel::createURLForAction('Index');
                $redirectURL .= '&report=edited&var=' . urlencode($item['title']);
                $redirectURL .= '&id=' . $this->id . '&highlight=row-' . $item['id'];
                $this->redirect($redirectURL);

            }
        }
    }
}
