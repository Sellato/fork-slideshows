<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language as BL;

/**
 * This is the settings-action, it will display a form to set general slideshow settings
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Settings extends BackendBaseActionEdit
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
        $this->frm->addText('slide_width', BackendModel::getModuleSetting('Slideshows', 'slide_width', null));
        $this->frm->addText('slide_height', BackendModel::getModuleSetting('Slideshows', 'slide_height', null));
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
                BackendModel::setModuleSetting(
                    'Slideshows',
                    'slide_width',
                    $this->frm->getField('slide_width')->getValue()
                );
                BackendModel::setModuleSetting(
                    'Slideshows',
                    'slide_height',
                    $this->frm->getField('slide_height')->getValue()
                );

                // redirect
                $this->redirect(BackendModel::createURLForAction(null, null, null, array('report' => 'saved')));
            }
        }
    }
}
