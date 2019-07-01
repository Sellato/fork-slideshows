<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Form;
use Backend\Core\Language\Language;
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
    public function execute(): void
    {
        // get parameters
        $this->id = $this->getRequest()->query->getInt('id');

        parent::execute();

        $this->getData();
        $this->loadDataGrid();
        $this->handleForm();
        $this->parse();
        $this->display();
    }

    private function getData(): void
    {
        $this->record = (array) Model::get($this->id);

        // no item found, throw an exceptions, because somebody is fucking with our URL
        if (empty($this->record)) {
            $redirectURL = BackendModel::createURLForAction(
                'Index',
                null,
                null,
                ['error' => 'non-existing']
            );
            $this->redirect($redirectURL);

            return;
        }
    }

    private function loadDataGrid(): void
    {
        $imagesDatagrid = new DataGridDatabase(
            'SELECT id, image, title, sequence
             FROM slideshows_slides
             WHERE slideshow_id = ?',
            [$this->id]
        );
        $imagesDatagrid->enableSequenceByDragAndDrop();
        $imagesDatagrid->addColumn(
            'edit',
            null,
            Language::lbl('Edit'),
            BackendModel::createURLForAction('EditSlide') . '&amp;id=[id]'
        );
        $imagesDatagrid->setColumnFunction([__CLASS__, 'generatePreview'], '[image]', 'image');

        $this->template->assign('images', (string) $imagesDatagrid->getContent());
    }

    protected function parse(): void
    {
        // call parent
        parent::parse();

        // assign the active record and additional variables
        $this->template->assign('item', $this->record);
    }

    public static function generatePreview(string $var): string
    {
        return '<img src="' . FRONTEND_FILES_URL . Model::IMAGE_FOLDER . '100x/' . $var . '" />';
    }

    private function handleForm(): void
    {
        // create form
        $this->form = new Form('edit');

        // create elements
        $txtTitle = $this->form->addText(
            'title',
            $this->record['title'],
            null,
            'form-control title',
            'form-control danger title'
        );

        $possibleTemplates = Model::getPossibleTemplates();
        if (count($possibleTemplates) > 1) {
            $template = $this->form->addDropdown(
                'template',
                $possibleTemplates,
                $this->record['template']
            );
        }

        // is the form submitted?
        if ($this->form->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->form->cleanupFields();

            // validate fields
            $txtTitle->isFilled(Language::err('TitleIsRequired'));

            // no errors?
            if ($this->form->isCorrect()) {
                // build item
                $item['id'] = $this->id;
                $item['title'] = $txtTitle->getValue();

                $item['template'] = reset($possibleTemplates);
                if (count($possibleTemplates) > 1) {
                    $item['template'] = $template->getValue();
                }

                Model::update($item);

                BackendModel::updateExtraData($this->record['extra_id'], 'extra_label', $item['title']);

                // everything is saved, so redirect to the overview
                $redirectURL = BackendModel::createURLForAction(
                    'Index',
                    null,
                    null,
                    [
                        'report' => 'edited',
                        'var' => urlencode($item['title']),
                        'id' => $this->id,
                        'highlight' => 'row-' . $item['id'],
                    ]
                );
                $this->redirect($redirectURL);

                return;
            }
        }
    }
}
