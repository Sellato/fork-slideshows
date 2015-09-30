<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Form;
use Backend\Core\Engine\DataGridDB;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model;

/**
 * This is the edit-action, it will display a form to edit an existing slideshow
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 * @author Jelmer Prins <jelmer@sumocoders.be>
 */
class Edit extends ActionEdit
{
    public function execute()
    {
        // get parameters
        $this->id = $this->getParameter('id', 'int');

        parent::execute();

        $this->getData();
        $this->loadDataGrid();
        $this->handleForm();
        $this->parse();
        $this->display();
    }

    private function getData()
    {
        $this->record = (array) Model::get($this->id);

        // no item found, throw an exceptions, because somebody is fucking with our URL
        if (empty($this->record)) {
            $redirectURL = BackendModel::createURLForAction(
                'Index',
                null,
                null,
                array('error' => 'non-existing')
            );
            $this->redirect($redirectURL);
        }
    }

    private function loadDataGrid()
    {
        $imagesDatagrid = new DataGridDB(
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
            Language::lbl('Edit'),
            BackendModel::createURLForAction('EditSlide') . '&amp;id=[id]'
        );
        $imagesDatagrid->setColumnFunction(array(__CLASS__, 'generatePreview'), '[image]', 'image');

        $this->tpl->assign('images', (string) $imagesDatagrid->getContent());
    }

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
        return '<img src="' . FRONTEND_FILES_URL . Model::IMAGE_FOLDER . '100x/' . $var . '" />';
    }

    private function handleForm()
    {
        // create form
        $this->frm = new Form('edit');

        // create elements
        $txtTitle = $this->frm->addText(
            'title',
            $this->record['title'],
            null,
            'inputText title',
            'inputTextError title'
        );

        // is the form submitted?
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $txtTitle->isFilled(Language::err('TitleIsRequired'));

            // no errors?
            if ($this->frm->isCorrect()) {
                // build item
                $item['id'] = $this->id;
                $item['title'] = $txtTitle->getValue();

                Model::update($item);

                BackendModel::updateExtraData($this->record['extra_id'], 'extra_label', $item['title']);

                // everything is saved, so redirect to the overview
                $redirectURL = BackendModel::createURLForAction('Index');
                $redirectURL .= '&report=edited&var=' . urlencode($item['title']);
                $redirectURL .= '&id=' . $this->id . '&highlight=row-' . $item['id'];
                $this->redirect($redirectURL);

            }
        }
    }
}
