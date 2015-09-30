<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\FormImage;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model as BackendSlideshowsModel;

/**
 * This is the add-action, it will display a form to add a new slide
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 * @author Jelmer Prins <jelmer@sumocoders.be>
 */
class AddSlide extends BackendBaseActionAdd
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

        $slideshowId = \SpoonFilter::getGetValue('slideshow', null, null);
        if ($slideshowId == null || !BackendSlideshowsModel::exists($slideshowId)) {
            $this->redirect(BackendModel::createURLForAction('Index') . '&error=non-existing');
        }

        $this->getData();
        $this->loadForm();
        $this->validateForm();
        $this->parse();
        $this->display();
    }

    public function getData()
    {
        // get dimensions
        $this->slideWidth = (int) BackendModel::getModuleSetting('Slideshows', 'slide_width', null);
        $this->slideHeight = (int) BackendModel::getModuleSetting('Slideshows', 'slide_height', null);
    }

    private function loadForm()
    {
        // create form
        $this->frm = new BackendForm('add');

        // create elements
        $this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
        $this->frm->addImage('image');
        $this->frm->addText('link');
    }

    protected function parse()
    {
        parent::parse();

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
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            /** @var FormImage $fileImage */
            $fileImage = $this->frm->getField('image');

            // validate fields
            $this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));
            if ($fileImage->isFilled(BL::err('FieldIsRequired'))) {
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
                $item = array();
                // build item
                $item['title'] = $this->frm->getField('title')->getValue();
                $slideshowId = \SpoonFilter::getGetValue('slideshow', null, null);
                $item['slideshow_id'] = $slideshowId;
                $item['created_on'] = BackendModel::getUTCDate();
                $item['link'] = $this->frm->getField('link')->getValue();
                $filename = $slideshowId . '_' . time() . '.' . $fileImage->getExtension();
                $fileImage->generateThumbnails(FRONTEND_FILES_PATH . BackendSlideshowsModel::IMAGE_FOLDER, $filename);

                $item['image'] = $filename;
                $lastSequence = BackendModel::getContainer()->get('database')->getVar(
                    'SELECT MAX(sequence)
                     FROM slideshows_slides
                     WHERE slideshow_id = ?',
                    array($slideshowId)
                );
                $item['sequence'] = ($lastSequence == null) ? 0 : ($lastSequence + 1);

                $item['id'] = BackendSlideshowsModel::insertSlide($item);
                // everything is saved, so redirect to the overview
                $redirectURL = BackendModel::createURLForAction('Edit');
                $redirectURL .= '&id=' . $slideshowId . '&report=added&var=' . urlencode($item['title']);
                $redirectURL .= '&highlight=row-' . $item['id'];
                $this->redirect($redirectURL);
            }
        }
    }
}
