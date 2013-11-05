<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendSlideshowsAdd extends BackendBaseActionAdd
{
	/**
	 * Execute the action
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
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('add');

		// create elements
		$this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['language'] = BL::getWorkingLanguage();
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['created_on'] = BackendModel::getUTCDate();

                // build extra
                $extra = array(
                    'module' => 'slideshows',
                    'type' => 'widget',
                    'label' => $item['title'],
                    'action' => 'detail',
                    'data' => null,
                    'hidden' => 'N',
                    'sequence' => BackendModel::getContainer()->get('database')->getVar(
                        'SELECT MAX(i.sequence) + 1
                         FROM modules_extras AS i
                         WHERE i.module = ?',
                        array('slideshows')
                    )
                );

                if(is_null($extra['sequence'])) $extra['sequence'] = BackendModel::getContainer()->get('database')->getVar(
                    'SELECT CEILING(MAX(i.sequence) / 1000) * 1000
                     FROM modules_extras AS i'
                );

                // insert extra
                $item['extra_id'] = BackendModel::getContainer()->get('database')->insert('modules_extras', $extra);

				$id = BackendSlideshowsModel::insert($item);

                BackendModel::updateExtra($item['extra_id'], 'data', serialize(array('id' => $id, 'extra_label' => $item['title'], 'language' => $item['language'])));

				$this->redirect(BackendModel::createURLForAction('index') . '&report=added&var=' . urlencode($item['title']) . '&id=' . $this->id . '&highlight=row-' . $id);
			}
		}
	}
}
