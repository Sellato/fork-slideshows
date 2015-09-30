<?php

namespace Backend\Modules\Slideshows\Actions;

use Backend\Core\Engine\Base\ActionAdd;
use Backend\Core\Engine\Form;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Slideshows\Engine\Model;

/**
 * This is the add-action, it will display a form to add a new slideshow
 *
 * @author Jonas De Keukelaere <jonas@sumocoders.be>
 * @author Mathias Helin <mathias@sumocoders.be>
 */
class Add extends ActionAdd
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
        $this->frm = new Form('add');

        // create elements
        $txtTitle = $this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');

        // is the form submitted?
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $txtTitle->isFilled(Language::err('TitleIsRequired'));

            // no errors?
            if ($this->frm->isCorrect()) {
                // build item
                $item['language'] = Language::getWorkingLanguage();
                $item['title'] = $txtTitle->getValue();
                $item['created_on'] = BackendModel::getUTCDate();

                // build extra
                $extra = array(
                    'module' => 'Slideshows',
                    'type' => 'widget',
                    'label' => $item['title'],
                    'action' => 'Detail',
                    'data' => null,
                    'hidden' => 'N',
                    'sequence' => BackendModel::getContainer()->get('database')->getVar(
                        'SELECT MAX(i.sequence) + 1
                         FROM modules_extras AS i
                         WHERE i.module = ?',
                        array('slideshows')
                    )
                );

                if (is_null($extra['sequence'])) {
                    $extra['sequence'] = BackendModel::getContainer()->get('database')->getVar(
                        'SELECT CEILING(MAX(i.sequence) / 1000) * 1000
                         FROM modules_extras AS i'
                    );
                }

                // insert extra
                $item['extra_id'] = BackendModel::getContainer()->get('database')->insert('modules_extras', $extra);

                $item['id'] = Model::insert($item);

                BackendModel::updateExtra(
                    $item['extra_id'],
                    'data',
                    serialize(
                        array('id' => $item['id'], 'extra_label' => $item['title'], 'language' => $item['language'])
                    )
                );

                $this->redirect(
                    BackendModel::createURLForAction('Index') . '&report=added&var=' . urlencode(
                        $item['title']
                    ) . '&id=' . $item['id'] . '&highlight=row-' . $item['id']
                );
            }
        }
    }
}
