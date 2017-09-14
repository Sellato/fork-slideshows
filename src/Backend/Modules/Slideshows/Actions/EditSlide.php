<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Form;
use Backend\Core\Language\Language;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model;

/**
 * This is the edit-action, it will display a form to edit an existing slide
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 * @author Jelmer Prins <jelmer@sumocoders.be>
 */
class EditSlide extends ActionEdit
{
    /**
     * Slide width
     *
     * @var int
     */
    private $slideWidth;

    /**
     * Slide height
     *
     * @var int
     */
    private $slideHeight;

    public function execute(): void
    {
        parent::execute();

        // get parameters
        $this->id = $this->getRequest()->query->getInt('id');

        $this->getData();
        $this->handleForm();
        $this->parse();
        $this->display();
    }

    private function getData(): void
    {
        $this->record = Model::getSlide($this->id);

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

        // get dimensions
        $moduleSettings = $this->get('fork.settings');
        $this->slideWidth = (int) $moduleSettings->get('Slideshows', 'slide_width', null);
        $this->slideHeight = (int) $moduleSettings->get('Slideshows', 'slide_height', null);
    }

    protected function parse(): void
    {
        // call parent
        parent::parse();

        // assign the active record and additional variables
        $this->template->assign('item', $this->record);

        // help text
        $helpImageDimensions = '';
        if ($this->slideWidth !== 0 && $this->slideHeight !== 0) {
            $helpImageDimensions = sprintf(Language::msg('HelpImageDimensions'), $this->slideWidth, $this->slideHeight);
        } elseif ($this->slideWidth !== 0) {
            $helpImageDimensions = sprintf(Language::msg('HelpImageDimensionsWidth'), $this->slideWidth);
        } elseif ($this->slideHeight !== 0) {
            $helpImageDimensions = sprintf(Language::msg('HelpImageDimensionsHeight'), $this->slideHeight);
        }
        $this->template->assign('helpImageDimensions', $helpImageDimensions);
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
        $fileImage = $this->form->addImage('image');
        $txtLink = $this->form->addText('link', $this->record['link']);
        $txtText = $this->form->addEditor('text', $this->record['text']);

        // is the form submitted?
        if ($this->form->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->form->cleanupFields();

            // validate fields
            $txtTitle->isFilled(Language::err('TitleIsRequired'));
            if ($fileImage->isFilled()) {
                // check dimensions
                if ($this->slideWidth !== 0 && $this->slideHeight !== 0) {
                    if ($fileImage->getWidth() != $this->slideWidth && $fileImage->getHeight() != $this->slideHeight) {
                        $fileImage->addError(
                            sprintf(Language::err('WrongDimensions'), $this->slideWidth, $this->slideHeight)
                        );
                    }
                } elseif ($this->slideWidth !== 0) {
                    if ($fileImage->getWidth() != $this->slideWidth) {
                        $fileImage->addError(sprintf(Language::err('WrongWidth'), $this->slideWidth));
                    }
                } elseif ($this->slideHeight !== 0) {
                    if ($fileImage->getHeight() != $this->slideHeight) {
                        $fileImage->addError(sprintf(Language::err('WrongHeight'), $this->slideHeight));
                    }
                }
            }

            // no errors?
            if ($this->form->isCorrect()) {
                // build item
                $item['id'] = $this->id;
                $item['title'] = $txtTitle->getValue();
                $item['link'] = $txtLink->getValue();
                $item['text'] = $txtText->getValue();

                if ($fileImage->isFilled()) {
                    $filename = $this->id . '_' . time() . '.' . $fileImage->getExtension();
                    $fileImage->generateThumbnails(
                        FRONTEND_FILES_PATH . Model::IMAGE_FOLDER,
                        $filename
                    );
                    $item['image'] = $filename;
                }

                Model::updateSlide($item);

                // everything is saved, so redirect to the overview
                $redirectURL = BackendModel::createURLForAction(
                    'Edit',
                    null,
                    null,
                    [
                        'report' => 'edited',
                        'var' => urldecode($item['title']),
                        'id' => $this->record['slideshow_id'],
                        'highlight' => 'row-' . $item['id'],
                    ]
                );
                $this->redirect($redirectURL);
            }
        }
    }
}
