<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionAdd;
use Backend\Core\Engine\Form;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model;

/**
 * This is the add-action, it will display a form to add a new slide
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 * @author Jelmer Prins <jelmer@sumocoders.be>
 */
class AddSlide extends ActionAdd
{
    /**
     * @var int
     */
    private $slideshowId;

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

        $this->slideshowId = \SpoonFilter::getGetValue('slideshow', null, null);
        if ($this->slideshowId == null || !Model::exists($this->slideshowId)) {
            $this->redirect(BackendModel::createURLForAction('Index') . '&error=non-existing');
        }

        $this->getData();
        $this->handleForm();
        $this->parse();
        $this->display();
    }

    public function getData()
    {
        // get dimensions
        $moduleSettings = $this->get('fork.settings');
        $this->slideWidth = (int) $moduleSettings->get('Slideshows', 'slide_width', null);
        $this->slideHeight = (int) $moduleSettings->get('Slideshows', 'slide_height', null);
    }

    protected function parse()
    {
        parent::parse();

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
        $this->frm = new Form('add');

        // create elements
        $txtTitle = $this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
        $fileImage = $this->frm->addImage('image');
        $txtLink = $this->frm->addText('link');

        // is the form submitted?
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $txtTitle->isFilled(Language::err('TitleIsRequired'));
            if ($fileImage->isFilled(Language::err('FieldIsRequired'))) {
                // check dimensions
                if ($this->slideWidth !== 0 && $this->slideHeight !== 0) {
                    if (
                        $fileImage->getWidth() != $this->slideWidth
                        && $fileImage->getHeight() != $this->slideHeight
                    ) {
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
                $item = array();
                // build item
                $item['title'] = $txtTitle->getValue();
                $item['slideshow_id'] = $this->slideshowId;
                $item['created_on'] = BackendModel::getUTCDate();
                $item['link'] = $txtLink->getValue();
                $filename = $this->slideshowId . '_' . time() . '.' . $fileImage->getExtension();
                $fileImage->generateThumbnails(
                    FRONTEND_FILES_PATH . Model::IMAGE_FOLDER,
                    $filename
                );

                $item['image'] = $filename;
                $item['sequence'] = Model::getNextSlideSequence($this->slideshowId);

                $item['id'] = Model::insertSlide($item);

                // everything is saved, so redirect to the overview
                $redirectURL = BackendModel::createURLForAction(
                    'Edit',
                    null,
                    null,
                    array(
                        'report' => 'added',
                        'var' => urlencode($item['title']),
                        'id' => $this->slideshowId,
                        'highlight' => 'row-' . $item['id'],
                    )
                );
                $this->redirect($redirectURL);
            }
        }
    }
}
