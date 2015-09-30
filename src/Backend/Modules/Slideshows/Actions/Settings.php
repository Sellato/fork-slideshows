<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Form;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language;

/**
 * This is the settings-action, it will display a form to set general slideshow settings
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Settings extends ActionEdit
{
    public function execute()
    {
        parent::execute();

        $this->handleForm();
        $this->parse();
        $this->display();
    }

    private function handleForm()
    {
        // create form
        $this->frm = new Form('settings');

        // fields
        $txtSlideWidth = $this->frm->addText(
            'slide_width',
            BackendModel::getModuleSetting('Slideshows', 'slide_width', null)
        );
        $txtSlideHeight = $this->frm->addText(
            'slide_height',
            BackendModel::getModuleSetting('Slideshows', 'slide_height', null)
        );

        // submitted?
        if ($this->frm->isSubmitted()) {
            // validation
            if ($txtSlideWidth->isFilled()) {
                $txtSlideWidth->isInteger(Language::err('InvalidInteger'));
            }
            if ($txtSlideHeight->isFilled()) {
                $txtSlideHeight->isInteger(Language::err('InvalidInteger'));
            }

            // correct?
            if ($this->frm->isCorrect()) {
                // save
                BackendModel::setModuleSetting('Slideshows', 'slide_width', $txtSlideWidth->getValue());
                BackendModel::setModuleSetting('Slideshows', 'slide_height', $txtSlideHeight->getValue());

                // redirect
                $redirectURL = BackendModel::createURLForAction(
                    null,
                    null,
                    null,
                    array(
                        'report' => 'saved',
                    )
                );
                $this->redirect($redirectURL);
            }
        }
    }
}
