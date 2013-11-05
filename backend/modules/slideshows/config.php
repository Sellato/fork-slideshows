<?php

/**
 * This is the configuration-object for the slideshows module
 *
 * @author Sam Tubbax <sam@sumocoders.be>
 */
class BackendSlideshowsConfig extends BackendBaseConfig
{
	/**
	 * The default action
	 *
	 * @var	string
	 */
	protected $defaultAction = 'index';

	/**
	 * The disabled actions
	 *
	 * @var	array
	 */
	protected $disabledActions = array();
}
