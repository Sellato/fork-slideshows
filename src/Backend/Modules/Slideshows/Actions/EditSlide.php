<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\FormImage;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model as BackendSlideshowsModel;

/**
 * This is the edit-action, it will display a form to edit an existing slide
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 * @author Jelmer Prins <jelmer@sumocoders.be>
 */
class EditSlide extends BackendBaseActionEdit
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
        // get parameters
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id !== null && BackendSlideshowsModel::existsSlide($this->id)) {
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

    private function getData()
    {
        $this->record = (array) BackendSlideshowsModel::getSlide($this->id);

        // no item found, throw an exceptions, because somebody is fucking with our URL
        if (empty($this->record)) {
            $this->redirect(BackendModel::createURLForAction('Index') . '&error=non-existing');
        }

        // get dimensions
        $this->slideWidth = (int) BackendModel::getModuleSetting('Slideshows', 'slide_width', null);
        $this->slideHeight = (int) BackendModel::getModuleSetting('Slideshows', 'slide_height', null);
    }

    private function loadForm()
    {
        // create form
        $this->frm = new BackendForm('edit');

        // create elements
        $this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
        $this->frm->addImage('image');
        $this->frm->addText('link', $this->record['link']);
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
            $helpImageDimensions = sprintf(BL::msg('HelpImageDimensions'), $this->slideWidth, $this->slideHeight);
        } elseif ($this->slideWidth !== 0) {
            $helpImageDimensions = sprintf(BL::msg('HelpImageDimensionsWidth'), $this->slideWidth);
        } elseif ($this->slideHeight !== 0) {
            $helpImageDimensions = sprintf(BL::msg('HelpImageDimensionsHeight'), $this->slideHeight);
        }
        $this->tpl->assign('helpImageDimensions', $helpImageDimensions);
    }

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

            /** @var FormImage $fileImage */
            $fileImage = $this->frm->getField('image');

            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));
            if ($fileImage->isFilled()) {
                // check dimensions
                if ($this->slideWidth !== 0 && $this->slideHeight !== 0) {
                    if ($fileImage->getWidth() != $this->slideWidth && $fileImage->getHeight() != $this->slideHeight) {
                        $fileImage->addError(
                            sprintf(BL::err('WrongDimensions'), $this->slideWidth, $this->slideHeight)
                        );
                    }
                } elseif ($this->slideWidth !== 0) {
                    if ($fileImage->getWidth() != $this->slideWidth) {
                        $fileImage->addError(sprintf(BL::err('WrongWidth'), $this->slideWidth));
                    }
                } elseif ($this->slideHeight !== 0) {
                    if ($fileImage->getHeight() != $this->slideHeight) {
                        $fileImage->addError(sprintf(BL::err('WrongHeight'), $this->slideHeight));
                    }
                }
            }


            // no errors?
            if ($this->frm->isCorrect()) {
                // build item
                $item['id'] = $this->id;
                $item['title'] = $this->frm->getField('title')->getValue();
                $item['link'] = $this->frm->getField('link')->getValue();

                if ($this->frm->getField('image')->isFilled()) {
                    $filename = $this->id . '_' . time() . '.' . $this->frm->getField('image')->getExtension();
                    $this->frm->getField('image')->generateThumbnails(
                        FRONTEND_FILES_PATH . BackendSlideshowsModel::IMAGE_FOLDER,
                        $filename
                    );
                    $item['image'] = $filename;
                }

                BackendSlideshowsModel::updateSlide($item);

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
