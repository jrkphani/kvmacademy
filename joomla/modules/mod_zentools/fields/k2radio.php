<?php
/**
 * @package		Zen Tools
 * @subpackage	Zen Tools
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2012 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later
 * @version		1.7.2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE . '/modules/mod_zentools/includes/zenhelper.php');

// Create a category selector
class JFormFieldK2radio extends JFormFieldRadio
{
	protected $type = 'k2radio';

	protected function getInput()
	{
		// Is K2 required but not installed?
		if (!ZenHelper::checkK2Requirement($this->element['requirement']))
		{
			return '';
		}

		return parent::getInput();
	}

	protected function getOptions()
	{
		$options = parent::getOptions();

		// Check k2
		if (!ZenHelper::isK2Installed())
		{
			$filtered = array();

			// Remove k2 option
			foreach ($options as $option)
			{
				if (substr_count(strtolower($option->value), 'k2') === 0
					&& substr_count(strtolower($option->text), 'k2') === 0)
				{
					$filtered[] = $option;
				}
			}

			$options = $filtered;
		}

		return $options;
	}
}
