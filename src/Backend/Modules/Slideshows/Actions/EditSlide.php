<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Form;
use Backend\Core\Engine\Language;
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

    public function execute()
    {
        parent::execute();

        // get parameters
        $this->id = $this->getParameter('id', 'int');

        $this->getData();
        $this->handleForm();
        $this->parse();
        $this->display();
    }

    private function getData()
    {
        $this->record = Model::getSlide($this->id);

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

        // get dimensions
        $this->slideWidth = (int) BackendModel::getModuleSetting('Slideshows', 'slide_width', null);
        $this->slideHeight = (int) BackendModel::getModuleSetting('Slideshows', 'slide_height', null);
    }

    protected function parse()
    {
        // call parent
        parent::parse();

        // assign the active record and additional variables
        $this->tpl->assign('item', $this->record);

        // help text
        $helpImageDimensions = '';
        if ($this->slideWidth !== 0 && $this->slideHeight !== 0) {
            $helpImageDimensions = sprintf(Language::msg('HelpImageDimensions'), $this->slideWidth, $this->slideHeight);
        } elseif ($this->slideWidth !== 0) {
            $helpImageDimensions = sprintf(Language::msg('HelpImageDimensionsWidth'), $this->slideWidth);
        } elseif ($this->slideHeight !== 0) {
            $helpImageDimensions = sprintf(Language::msg('HelpImageDimensionsHeight'), $this->slideHeight);
        }
        $this->tpl->assign('helpImageDimensions', $helpImageDimensions);
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
        $fileImage = $this->frm->addImage('image');
        $txtLink = $this->frm->addText('link', $this->record['link']);

        // is the form submitted?
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

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
            if ($this->frm->isCorrect()) {
                // build item
                $item['id'] = $this->id;
                $item['title'] = $txtTitle->getValue();
                $item['link'] = $txtLink->getValue();

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
                $redirectURL = BackendModel::createURLForAction('Edit');
                $redirectURL .= '&id=' . $this->record['slideshow_id'];
                $redirectURL .= '&report=edited&var=' . urlencode($item['title']);
                $redirectURL .= '&highlight=row-' . $item['id'];
                $this->redirect($redirectURL);
            }
        }
    }
}
