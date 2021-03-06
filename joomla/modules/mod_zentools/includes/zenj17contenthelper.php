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
require_once(JPATH_SITE . '/components/com_content/helpers/route.php');

jimport('joomla.application.component.model');

$modelPath = JPATH_SITE . '/components/com_content/models';
if (version_compare(JVERSION, '3.0', '<'))
{
	JModel::addIncludePath($modelPath, 'ContentModel');
}
else
{
	JModelLegacy::addIncludePath($modelPath, 'ContentModel');
}

abstract class ModZentoolsHelper
{
	public static function getList(&$params)
	{
		// Get the dbo
		$db = JFactory::getDbo();
		// Word Count
		$wordCount	= $params->get( 'wordCount','');
		$titlewordCount	= $params->get( 'titlewordCount','');
		$strip_tags = $params->get('strip_tags',0);
		$titleSuffix = $params->get('titleSuffix','');
		$tags	= $params->get( 'allowed_tags','');

		// Image Size and container, remove px if user entered
		$responsiveimages = $params->get('responsiveimages');
		$resizeImage = $params->get('resizeImage',1);
		$option = $params->get( 'option', 'crop');
		$img_width = str_replace('px', '', $params->get( 'image_width','170'));
		$img_height = str_replace('px', '', $params->get( 'image_height','85'));

		$thumb_width = str_replace('px', '', $params->get( 'thumb_width','20'));
		$thumb_height = str_replace('px', '', $params->get( 'thumb_height','20'));

		// Other Params
		$dateFormat		= $params->get('dateFormat', 'j M, y');
		$dateString		= $params->get('dateString', 'DATE_FORMAT_LC3');
		$translateDate		= $params->get('translateDate', '0');
		$showCategory = $params->get('showCategory',1);
		$link	= $params->get( 'link','');
		$textsuffix	= $params->get( 'textsuffix','');

		// Lightbox
		$modalVideo = $params->get('modalVideo');
		$modalText = $params->get('modalText');
		$modalTitle = $params->get('modalTitle');
		$modalMore = $params->get('modalMore');


		// J2.5 and altlink
		if (version_compare(JVERSION, '2.5', '<'))
		{
			$joomla25 = 0;
			$altlink = 0;
		}
		else
		{
			$joomla25 = 1;
			$altlink = $params->get('altlink');
		}

		// Get an instance of the generic articles model
		if (version_compare(JVERSION, '3.0', '<'))
		{
			$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		}
		else
		{
			$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		}

		// Set application parameters in model
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));
		$model->setState('filter.published', 1);

		// Access filter
		$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		if($params->get( 'joomlaContentSource') =="items") {
			$model->setState('filter.article_id', $params->get('artids', array()));
			$model->setState('filter.article_id.include', $params->get('article_filtering_type', 1));
		}
		else {
			$model->setState('filter.category_id', $params->get('catid', array()));
		}

		//  Featured switch
		switch ($params->get('show_front'))
		{
			case '2':
				$model->setState('filter.featured', 'only');
				break;
			case '0':
				$model->setState('filter.featured', 'hide');
				break;
			case '1':
				$model->setState('filter.featured', 'show');
				break;
		}


		// Filter by language
		$model->setState('filter.language',$app->getLanguageFilter());

		// Ordering
		$model->setState('list.ordering', $params->get('ordering', 'a.ordering'));
		$model->setState('list.direction', $params->get('ordering_direction', 'ASC'));

		$items = $model->getItems();

		foreach ($items as $item) {
			$item->slug = $item->id.':'.$item->alias;
			$item->catslug = $item->catid.':'.$item->category_alias;
			$item->text = $item->introtext;
			$item->modaltext = $item->text;




			/**
			*
			* Joomla 1.7 & 2.5 Image Logic
			*
			**/

			// Grab the intro_image in Joomla 2.5 or otherwise use the introtext image.
			if(version_compare( JVERSION, '2.5', '>=' ))
			{
				$images = json_decode($item->images);
			}
			else
			{
				$images = "";
			}

			if(isset($images->image_intro) and !empty($images->image_intro)) {
				$item->image = $images->image_intro;
			}

			else {
				$imghtml= $item->introtext;
				$imghtml .= "alt='...' title='...' />";
				$pattern = '/<img[^>]+src[\\s=\'"]';
				$pattern .= '+([^"\'>\\s]+)/is';
				if(preg_match(
				$pattern,
				$imghtml,
				$match))
				$item->image = "$match[1]";
				else $item->image = "";
			}



			/**
			*
			* Joomla 1.7 & Joomla 2.5 Resize Images
			*
			**/
			$item->thumb="";
			$item->modalimage="";
			if($item->image !=="") {

				// Sets the modal image
				$item->modalimage = $item->image;

				if ($resizeImage) {
						$item->image =  resizeImageHelper::getResizedImage($item->image, $img_width, $img_height, $option);
						
						if($responsiveimages) {		
							$item->imageTiny = resizeImageHelper::getResizedImage($item->image, ($img_width /5), ($img_height / 5), $option);
							$item->imageXSmall = resizeImageHelper::getResizedImage($item->image, ($img_width /3), ($img_height / 3), $option);
							$item->imageSmall = resizeImageHelper::getResizedImage($item->image, ($img_width /2), ($img_height / 2), $option);
							$item->imageMedium = resizeImageHelper::getResizedImage($item->image, ($img_width /1.25), ($img_height / 1.25), $option);
							$item->imageDefault = resizeImageHelper::getResizedImage($item->image, ($img_width), ($img_height), $option);
							$item->imageLarge = resizeImageHelper::getResizedImage($item->image, ($img_width * 1.25), ($img_height * 1.25), $option);
							if($item->imageLarge == $image) { 
								$item->imageLarge = $item->imageDefault;
							}
							$item->imageXLarge = resizeImageHelper::getResizedImage($item->image, ($img_width *1.75), ($img_height * 1.75), $option);

							if($item->imageXLarge == $image) { 
								$item->imageXLarge = $item->imageDefault;
							}
						}
				}
				else {
					$item->image = $item->image;
					
					if($responsiveimages) {	
						list($width, $height) = getimagesize($item->image);
						$item->imageTiny = resizeImageHelper::getResizedImage($item->image, ($width /5), ($height / 5), 'exact');
						$item->imageXSmall = resizeImageHelper::getResizedImage($item->image, ($width /3), ($height / 3), 'exact');
						$item->imageSmall = resizeImageHelper::getResizedImage($item->image, ($width /2), ($height / 2), 'exact');
						$item->imageMedium = resizeImageHelper::getResizedImage($item->image, ($width /1.5), ($height / 1.25), 'exact');
						$item->imageDefault = resizeImageHelper::getResizedImage($item->image, ($width), ($height), 'exact');
						$item->imageLarge = resizeImageHelper::getResizedImage($item->image, ($width * 1.25), ($height * 1.25), $option);
						if($item->imageLarge == $image) { 
								$item->imageLarge = $item->imageDefault;
						}
						
						$item->imageXLarge = resizeImageHelper::getResizedImage($item->image, ($width *1.75), ($height * 1.75), $option);

						if($item->imageXLarge == $image) { 
							$item->imageXLarge = $item->imageDefault;
						}
					}
				}

				$item->thumb = resizeImageHelper::getResizedImage($item->image, $thumb_width, $thumb_height,  $option);
			}


			/**
			*
			* Joomla 1.7 & Joomla 2.5 Title
			*
			**/
			$titletext = htmlspecialchars( $item->title );
			$item->modaltitle = htmlspecialchars( $item->title );
			$item->title = $titlewordCount ? ZenHelper::truncate($titletext, $titlewordCount, $titleSuffix) : $titletext;


			/**
			*
			* Joomla Links
			*
			**/
			if ($access || in_array($item->access, $authorised)) {
				// We know that user has the privilege to view the article
				if($link == 0) {
					$item->link = '';
					$item->closelink = '';
				}
				elseif($link == 1) {
					if($modalMore or $modalTitle or $modalText) {
						$item->link = 'href="#data'.$item->id.'"';
					}
					else {
						$item->link = 'href="'.$item->modalimage.'" title="'.$item->modaltitle.'"';
					}
					$item->closelink = '</a>';
					$item->lightboxmore = 'href="'.JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug)).'"';
				}
				else {
					$item->link = 'href="'.JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug)).'"';
					$item->closelink = '</a>';
				}


			/**
			*
			* Joomla 2.5  Alternate Links
			*
			**/

			$item->newlink = 0;

			if($joomla25) {
				$links = json_decode($item->urls);

				if(isset($links->urla) and !empty($links->urla)) {
					$item->altlink = 'href="'.$links->urla.'"';
					$item->newlink = 1;
				}
				else {
					$item->altlink = $item->link;
					$item->newlink = 0;
				}
			}

			/**
			*
			* Joomla 1.7 & Joomla 2.5 Category Name and Link
			*
			**/

			if ($showCategory) {
					$item->catlink = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug).'&layout=blog').'">';

			} else {
				$item->link = '<a href="'.JRoute::_('index.php?option=com_user&view=login').'">';
			}

			$item->category = $item->category_title;


			}

			else {
				$item->link = JRoute::_('index.php?option=com_users&view=login');

			}



			/**
			*
			* Joomla 1.7 & Joomla 2.5 Metakeywords
			*
			**/

			$item->metakey = $item->metakey;




			/**
			*
			* Joomla 1.7 & Joomla 2.5 Intro Text
			*
			**/

			if($strip_tags) {
				$introtext = $strip_tags ? ZenHelper::_cleanIntrotext($item->introtext,$tags) : $item->introtext;
			}
			else {
				$introtext = $item->introtext;
			}

			if($wordCount !=="-1") {
				$tempintro = false;
				$item->text = $wordCount ? ZenHelper::truncate($introtext, $wordCount, $textsuffix) : $tempintro;
			}
			else {
				$item->text = $item->introtext;
				$item->text = $item->text.$textsuffix;
			}



			/**
			*
			* Joomla 1.7 & Joomla 2.5 Full Text
			*
			**/

			//$item->fulltext = $item->fulltext;




			/**
			*
			* Joomla 1.7 & Joomla 2.5 Date
			*
			**/

			if (!$translateDate) {
				$item->date = date($dateFormat,  (strtotime($item->created)));
			}
			else {
				$item->date = JHTML::_('date', $item->created, JText::_(''.$dateString.''));
			}


			/**
			*
			* Joomla 1.7 & Joomla 2.5 IDs
			*
			**/

			$item->id = $item->id;

			/**
			*
			* null
			*
			**/

			//$item->featured = 0;


		}

		return $items;
	}
}
