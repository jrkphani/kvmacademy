<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_tz_portfolio/models/article.php';

/**
 * Content Component Article Model
 */
class TZ_PortfolioModelForm extends TZ_PortfolioModelArticle
{

    private $contentid  = null;
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = JRequest::getInt('a_id');
		$this->setState('article.id', $pk);

		$this->setState('article.catid', JRequest::getInt('catid'));

		$return = JRequest::getVar('return', null, 'default', 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params	= $app->getParams();

		$this->setState('params', $params);

        if($params -> get('tz_image_gallery_xsmall')){
            $size['XS'] = (int) $params -> get('tz_image_gallery_xsmall');
        }
        if($params -> get('tz_image_gallery_small')){
            $size['S'] = (int) $params -> get('tz_image_gallery_small');
        }
        if($params -> get('tz_image_gallery_medium')){
            $size['M'] = (int) $params -> get('tz_image_gallery_medium');
        }
        if($params -> get('tz_image_gallery_large')){
            $size['L'] = (int) $params -> get('tz_image_gallery_large');
        }
        if($params -> get('tz_image_gallery_xsmall')){
            $size['XL'] = (int) $params -> get('tz_image_gallery_xlarge');
        }
        $this -> setState('size',$size);

		$this->setState('layout', JRequest::getCmd('layout'));
	}

	/**
	 * Method to get article data.
	 *
	 * @param	integer	The id of the article.
	 *
	 * @return	mixed	Content item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('article.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return false;
		}

		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');

		// Convert attrib field to Registry.
		$value->params = new JRegistry;
		$value->params->loadString($value->attribs);

		// Compute selected asset permissions.
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$asset	= 'com_tz_portfolio.article.'.$value->id;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset)) {
			$value->params->set('access-edit', true);
		}
		// Now check if edit.own is available.
		elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
			// Check for a valid user and that they are the owner.
			if ($userId == $value->created_by) {
				$value->params->set('access-edit', true);
			}
		}

		// Check edit state permission.
		if ($itemId) {
			// Existing item
			$value->params->set('access-change', $user->authorise('core.edit.state', $asset));
		}
		else {
			// New item.
			$catId = (int) $this->getState('article.catid');

			if ($catId) {
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_tz_portfolio.category.'.$catId));
				$value->catid = $catId;
			}
			else {
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_tz_portfolio'));
			}
		}

		$value->articletext = $value->introtext;
		if (!empty($value->fulltext)) {
			$value->articletext .= '<hr id="system-readmore" />'.$value->fulltext;
		}

		return $value;
	}

	/**
	 * Get the return URL.
	 *
	 * @return	string	The return URL.
	 * @since	1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

    function getFieldsContent(){
        parent::getFieldsContent();
        $this -> contentid  = $this -> getState('article.id');
        $data   = new stdClass();
        $data -> images             = '';
        $data -> imagetitle         = '';
        $data -> images_hover       = '';
        $data -> gallery -> images  = '';
        $data -> gallery -> title   = '';
        $data -> video -> code      = '';
        $data -> video -> type      = '';
        $data -> video -> title     = '';
        $data -> type               = 'image';
        if($this -> contentid){
            $query  = 'SELECT * FROM #__tz_portfolio_xref_content'
                .' WHERE contentid = '.$this -> contentid;
            //.' GROUP BY contentid';

            $db     = &JFactory::getDbo();
            $db -> setQuery($query);
            if(!$db -> query()){
                $this -> setError($db -> getErrorMsg());
                return false;
            }

            if($row = $db -> loadObject()){

                $data -> images = $row -> images;
                $data -> imagetitle = $row -> imagetitle;
                $data -> images_hover       = $row -> images_hover;

                if(preg_match('/.*\/\/\/.*/i',$row -> gallery,$match)){
                    $gallery        = explode('///',$row -> gallery);
                    $gallerytitle   = explode('///',$row -> gallerytitle);
                    if($gallery){
                        foreach($gallery as $i => $item){
                            if(!isset($gallerytitle[$i])){
                                $gallerytitle[$i]   = '';
                            }
                        }
                    }
                }
                else{
                    $gallery        = $row -> gallery;
                    $gallerytitle   = $row -> gallerytitle;
                }
                $data -> gallery -> images      = $gallery;
                $data -> gallery -> title  = $gallerytitle;

                if(preg_match('/.*:.*/i',$row -> video,$match)){
                    for($i = 0; $i<strlen($row -> video); $i ++){
                        if(substr($row -> video,$i,1) == ':'){
                            $pos    = $i;
                            break;
                        }
                    }

                    $data -> video -> code  = substr($row -> video,$pos + 1,strlen($row -> video));
                    $data -> video -> type  = substr($row -> video,0,$pos);
                    $data -> video -> title = $row -> videotitle;
                    $data -> video -> thumb = $row -> videothumb;
                }
                else{
                    $data -> video -> code  = '';
                    $data -> video -> type  = 'default';
                    $data -> video -> title = '';
                    $data -> video -> thumb = '';
                }

                $data   -> type = strtolower($row -> type);
            }
        }

        return $data;
    }

    
}
