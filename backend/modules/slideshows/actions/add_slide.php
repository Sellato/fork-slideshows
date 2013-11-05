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
class BackendSlideshowsAddSlide extends BackendBaseActionAdd
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

        $slideshowId = SpoonFilter::getGetValue('slideshow', null, null);
        if($slideshowId == null || !BackendSlideshowsModel::exists($slideshowId)) $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');

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
        $this->frm->addImage('image');
        $this->frm->addText('link');
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
			$this->frm->getField('image')->isFilled(BL::err('FieldIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
                $item = array();
				// build item
				$item['title'] = $this->frm->getField('title')->getValue();
                $slideshowId = SpoonFilter::getGetValue('slideshow', null, null);
				$item['slideshow_id'] = $slideshowId;
				$item['created_on'] = BackendModel::getUTCDate();
				$item['link'] = $this->frm->getField('link')->getValue();
                $filename = $slideshowId . '_' . time() . '.' . $this->frm->getField('image')->getExtension();
                $this->frm->getField('image')->generateThumbnails(FRONTEND_FILES_PATH . '/slideshows/', $filename);

				$item['image'] = $filename;
                $lastSequence = BackendModel::getContainer()->get('database')->getVar('SELECT MAX(sequence) FROM slideshows_slides WHERE slideshow_id = ?', $slideshowId);
				$item['sequence'] = ($lastSequence == null)? 0 : ($lastSequence + 1);

				$id = BackendSlideshowsModel::insertSlide($item);
                // everything is saved, so redirect to the overview
                $this->redirect(BackendModel::createURLForAction('edit') . '&id=' . $slideshowId . 'report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
            }
		}
	}
}
