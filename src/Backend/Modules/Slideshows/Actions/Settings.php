<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Form;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language;

/**
 * This is the settings-action, it will display a form to set general slideshow settings
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Settings extends ActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $this->handleForm();
        $this->parse();
        $this->display();
    }

    private function handleForm(): void
    {
        // create form
        $this->form = new Form('settings');

        // fields
        $moduleSettings = $this->get('fork.settings');
        $txtSlideWidth = $this->form->addText(
            'slide_width',
            $moduleSettings->get('Slideshows', 'slide_width', null)
        );
        $txtSlideHeight = $this->form->addText(
            'slide_height',
            $moduleSettings->get('Slideshows', 'slide_height', null)
        );

        // submitted?
        if ($this->form->isSubmitted()) {
            // validation
            if ($txtSlideWidth->isFilled()) {
                $txtSlideWidth->isInteger(Language::err('InvalidInteger'));
            }
            if ($txtSlideHeight->isFilled()) {
                $txtSlideHeight->isInteger(Language::err('InvalidInteger'));
            }

            // correct?
            if ($this->form->isCorrect()) {
                // save
                $moduleSettings->set('Slideshows', 'slide_width', $txtSlideWidth->getValue());
                $moduleSettings->set('Slideshows', 'slide_height', $txtSlideHeight->getValue());

                // redirect
                $redirectURL = BackendModel::createURLForAction(
                    null,
                    null,
                    null,
                    [
                        'report' => 'saved',
                    ]
                );
                $this->redirect($redirectURL);
            }
        }
    }
}
