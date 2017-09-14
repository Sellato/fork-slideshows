<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionAdd;
use Backend\Core\Engine\Form;
use Backend\Core\Language\Language;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model;
use Common\ModuleExtraType;

/**
 * This is the add-action, it will display a form to add a new slideshow
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Add extends ActionAdd
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
        $this->form = new Form('add');

        // create elements
        $txtTitle = $this->form->addText('title', null, null, 'form-control title', 'form-control danger title');

        $possibleTemplates = Model::getPossibleTemplates();
        if (count($possibleTemplates) > 1) {
            $template = $this->form->addDropdown(
                'template',
                $possibleTemplates
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
                $item['language'] = Language::getWorkingLanguage();
                $item['title'] = $txtTitle->getValue();

                $item['template'] = reset($possibleTemplates);
                if (count($possibleTemplates) > 1) {
                    $item['template'] = $template->getValue();
                }

                $item['created_on'] = BackendModel::getUTCDate();

                // save data
                $item['extra_id'] = BackendModel::insertExtra(
                    ModuleExtraType::widget(),
                    $this->getModule(),
                    'Detail',
                    $item['title']
                );
                $item['id'] = Model::insert($item);

                // update extra
                $extraData = [
                    'id' => $item['id'],
                    'extra_label' => $item['title'],
                    'language' => $item['language'],
                ];
                BackendModel::updateExtra($item['extra_id'], 'data', serialize($extraData));

                $redirectURL = BackendModel::createUrlForAction(
                    'Index',
                    null,
                    null,
                    [
                        'report' => 'added',
                        'var' => urlencode($item['title']),
                        'id' => $item['id'],
                        'highlight' => 'row' . $item['id'],
                    ]
                );
                $this->redirect($redirectURL);
            }
        }
    }
}
