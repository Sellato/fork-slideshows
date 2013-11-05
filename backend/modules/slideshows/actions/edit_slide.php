<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit-action, it will display a form to edit an existing item
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendSlideshowsEditSlide extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendSlideshowsModel::existsSlide($this->id))
		{
			parent::execute();
			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}
		// no item found, throw an exception, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the data
	 * If a revision-id was specified in the URL we load the revision and not the actual data.
	 */
	private function getData()
	{
		$this->record = (array) BackendSlideshowsModel::getSlide($this->id);

		// no item found, throw an exceptions, because somebody is fucking with our URL
		if(empty($this->record)) $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('edit');

		// create elements
		$this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
		$this->frm->addImage('image');
		$this->frm->addText('link', $this->record['link']);
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		// call parent
		parent::parse();
		// assign the active record and additional variables
		$this->tpl->assign('item', $this->record);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// get the status
			$status = SpoonFilter::getPostValue('status', array('active', 'draft'), 'active');

			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));


			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();
                $item['link'] = $this->frm->getField('link')->getValue();

                if($this->frm->getField('image')->isFilled())
                {
                    $filename = $this->id . '_' . time() . '.' . $this->frm->getField('image')->getExtension();
                    $this->frm->getField('image')->generateThumbnails(FRONTEND_FILES_PATH . '/slideshows/', $filename);
                    $item['image'] = $filename;
                }

                BackendSlideshowsModel::updateSlide($item);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('edit') . '&id=' . $this->record['slideshow_id'] . 'report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);

			}
		}
	}
}
