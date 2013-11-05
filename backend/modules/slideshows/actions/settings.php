<?php

/**
 * This is the settings-action, it will show a form to edit the settings
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 */
class BackendSlideshowsSettings extends BackendBaseActionEdit
{
    /**
     * Execute the action
     *
     * @return void
     */
    public function execute()
    {
        parent::execute();

        $this->loadForm();
        $this->validateForm();
        $this->parse();
        $this->display();
    }

    /**
     * Load form
     *
     * @return void
     */
    private function loadForm()
    {
        // create form
        $this->frm = new BackendForm('settings');

        // fields
        $this->frm->addText('slide_width', BackendModel::getModuleSetting('slideshows', 'slide_width', null));
        $this->frm->addText('slide_height', BackendModel::getModuleSetting('slideshows', 'slide_height', null));
    }

    /**
     * Validate form
     *
     * @return void
     */
    private function validateForm()
    {
        // submitted?
        if ($this->frm->isSubmitted()) {
            // validation
            if ($this->frm->getField('slide_width')->isFilled()) {
                $this->frm->getField('slide_width')->isInteger(BL::err('InvalidInteger'));
            }
            if ($this->frm->getField('slide_height')->isFilled()) {
                $this->frm->getField('slide_height')->isInteger(BL::err('InvalidInteger'));
            }

            // correct?
            if ($this->frm->isCorrect()) {
                // save
                BackendModel::setModuleSetting('slideshows', 'slide_width',
                    $this->frm->getField('slide_width')->getValue());
                BackendModel::setModuleSetting('slideshows', 'slide_height',
                    $this->frm->getField('slide_height')->getValue());

                // rediredt
                $this->redirect(BackendModel::createURLForAction(null, null, null, array('report' => 'saved')));
            }
        }
    }
}