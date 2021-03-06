<?php
/**
 * @package Xpert Accordion
 * @version 1.0
 * @author ThemeXpert http://www.themexpert.com
 * @copyright Copyright (C) 2009 - 2011 ThemeXpert
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

abstract class modXpertAccordionHelper{
    
    public static function getLists(&$params)
    {
        $total_items = (int) $params->get('count', 4) ;
        $content_source = $params->get('content_source','joomla');

        switch($content_source){
            case 'joomla':
                $lists = self::_getJoomlaItems($params,$total_items);
                break;
            case 'k2':
                $lists = self::_getK2Items($params, $total_items);
                break;
            case 'easyblog':
                $lists = self::getEasyBlogItems($params, $total_items);
                break;
            case 'mods':
                $lists = self::_getModuleItems($params);
                break;
        }
        //echo "<pre>";print_r($lists);echo "</pre>";

        return $lists;
    }

    private static function _getModuleItems($params)
    {
        //module specific
        $mods = $params->get('modules');
        $options    = array('style' => 'none');
        $items = array();

        for ($i=0;$i<count($mods);$i++) {
            $items[$i]->order   = modXpertAccordionHelper::getModule($mods[$i])->ordering;
            $items[$i]->title   = modXpertAccordionHelper::getModule($mods[$i])->title;
            $items[$i]->content = $items[$i]->introtext = JModuleHelper::renderModule(  modXpertAccordionHelper::getModule($mods[$i]), $options);
        }

        return $items;
    }

    //fetch module by id
    public static function getModule( $id ){

        $db     =& JFactory::getDBO();
        $where = ' AND ( m.id='.$id.' ) ';

        $query = 'SELECT *'.
            ' FROM #__modules AS m'.
            ' WHERE m.client_id = 0'.
            $where.
            ' ORDER BY ordering'.
            ' LIMIT 1';

        $db->setQuery( $query );
        $module = $db->loadObject();

        if (!$module) return null;

        $file               = $module->module;
        $custom             = substr($file, 0, 4) == 'mod_' ?  0 : 1;
        $module->user       = $custom;
        $module->name       = $custom ? $module->title : substr($file, 4);
        $module->style      = null;
        $module->position   = strtolower($module->position);
        $clean[$module->id] = $module;

        return $module;
    }

    private static function _getJoomlaItems($params,$total_items)
    {
        require_once JPATH_SITE.'/components/com_content/helpers/route.php';
        jimport('joomla.application.component.model');
        JModel::addIncludePath(JPATH_SITE.'/components/com_content/models');

        // Get the dbo
        $db = JFactory::getDbo();

        // Get an instance of the generic articles model
        $model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

        // Set application parameters in model
        $app = JFactory::getApplication();
        $appParams = $app->getParams();
        $model->setState('params', $appParams);

        // Set the filters based on the module params
        $model->setState('list.start', 0);
        $model->setState('list.limit', $total_items);
        $model->setState('filter.published', 1);

        // Access filter
        $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $model->setState('filter.access', $access);

        // Category filter
        $model->setState('filter.category_id', $params->get('catid', array()));

        // User filter
        $userId = JFactory::getUser()->get('id');
        switch ($params->get('user_id'))
        {
                case 'by_me':
                        $model->setState('filter.author_id', (int) $userId);
                        break;
                case 'not_me':
                        $model->setState('filter.author_id', $userId);
                        $model->setState('filter.author_id.include', false);
                        break;

                case '0':
                        break;

                default:
                        $model->setState('filter.author_id', (int) $params->get('user_id'));
                        break;
        }
        // Filter by language
        $model->setState('filter.language',$app->getLanguageFilter());

        //  Featured switch
        switch ($params->get('show_featured'))
        {
                case '1':
                        $model->setState('filter.featured', 'only');
                        break;
                case '0':
                        $model->setState('filter.featured', 'hide');
                        break;
                default:
                        $model->setState('filter.featured', 'show');
                        break;
        }

        // Set ordering
        $order_map = array(
                'm_dsc' => 'a.modified DESC, a.created',
                'mc_dsc' => 'CASE WHEN (a.modified = '.$db->quote($db->getNullDate()).') THEN a.created ELSE a.modified END',
                'c_dsc' => 'a.created',
                'p_dsc' => 'a.publish_up',
        );
        $ordering = JArrayHelper::getValue($order_map, $params->get('ordering'), 'a.publish_up');
        $dir = 'DESC';

        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $dir);
        $items = $model->getItems();

       foreach ($items as &$item) {
            $item->slug = $item->id.':'.$item->alias;
            $item->catslug = $item->catid.':'.$item->category_alias;

            if ($access || in_array($item->access, $authorised))
            {
                    // We know that user has the privilege to view the article
                    $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
            }
            else {
                    $item->link = JRoute::_('index.php?option=com_user&view=login');
            }

            $item->introtext = JHtml::_('content.prepare', $item->introtext);
            $item->introtext = self::prepareIntroText($item->introtext, $params->get('intro_limit'));
            $item->image = self::getImage($item->introtext);

        }

        return $items;
        }

    private static function _getK2Items($params,$total_items)
    {
        require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');

        jimport('joomla.filesystem.file');
        $mainframe = &JFactory::getApplication();
        $limit = $total_items;
        $cid = $params->get('category_id', NULL);
        $ordering = $params->get('itemsOrdering','');
        $componentParams = &JComponentHelper::getParams('com_k2');
        $limitstart = JRequest::getInt('limitstart');

        $user = &JFactory::getUser();
        $aid = $user->get('aid');
        $db = &JFactory::getDBO();

        $jnow = &JFactory::getDate();
        $now = $jnow->toMySQL();
        $nullDate = $db->getNullDate();

        $query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

        if ($ordering == 'best')
        $query .= ", (r.rating_sum/r.rating_count) AS rating";

        if ($ordering == 'comments')
        $query .= ", COUNT(comments.id) AS numOfComments";

        $query .= " FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";

        if ($ordering == 'best')
        $query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";

        if ($ordering == 'comments')
        $query .= " LEFT JOIN #__k2_comments comments ON comments.itemID = i.id";

        if(K2_JVERSION=='16'){
            $query .= " WHERE i.published = 1 AND i.access IN(".implode(',', $user->authorisedLevels()).") AND i.trash = 0 AND c.published = 1 AND c.access IN(".implode(',', $user->authorisedLevels()).")  AND c.trash = 0";
        }
        else {
            $query .= " WHERE i.published = 1 AND i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";
        }

        $query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
        $query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";


        if ($params->get('catfilter')) {
            if (!is_null($cid)) {
                if (is_array($cid)) {
                    if ($params->get('getChildren')) {
                        require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');
                        $categories = K2ModelItemlist::getCategoryTree($cid);
                        $sql = @implode(',', $categories);
                        $query .= " AND i.catid IN ({$sql})";

                    } else {
                        JArrayHelper::toInteger($cid);
                        $query .= " AND i.catid IN(".implode(',', $cid).")";
                    }

                } else {
                    if ($params->get('getChildren')) {
                        require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');
                        $categories = K2ModelItemlist::getCategoryTree($cid);
                        $sql = @implode(',', $categories);
                        $query .= " AND i.catid IN ({$sql})";
                    } else {
                        $query .= " AND i.catid=".(int)$cid;
                    }

                }
            }
        }

        if ($params->get('FeaturedItems') == '0')
        $query .= " AND i.featured != 1";

        if ($params->get('FeaturedItems') == '2')
        $query .= " AND i.featured = 1";

        if ($ordering == 'comments')
        $query .= " AND comments.published = 1";

        if(K2_JVERSION=='16'){
            if($mainframe->getLanguageFilter()) {
                $languageTag = JFactory::getLanguage()->getTag();
                $query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
            }
        }

        switch ($ordering) {

            case 'date':
                $orderby = 'i.created ASC';
                break;

            case 'rdate':
                $orderby = 'i.created DESC';
                break;

            case 'alpha':
                $orderby = 'i.title';
                break;

            case 'ralpha':
                $orderby = 'i.title DESC';
                break;

            case 'order':
                if ($params->get('FeaturedItems') == '2')
                $orderby = 'i.featured_ordering';
                else
                $orderby = 'i.ordering';
                break;

            case 'rorder':
                if ($params->get('FeaturedItems') == '2')
                $orderby = 'i.featured_ordering DESC';
                else
                $orderby = 'i.ordering DESC';
                break;

            case 'hits':
                if ($params->get('popularityRange')){
                    $datenow = &JFactory::getDate();
                    $date = $datenow->toMySQL();
                    $query.=" AND i.created > DATE_SUB('{$date}',INTERVAL ".$params->get('popularityRange')." DAY) ";
                }
                $orderby = 'i.hits DESC';
                break;

            case 'rand':
                $orderby = 'RAND()';
                break;

            case 'best':
                $orderby = 'rating DESC';
                break;

            case 'comments':
                if ($params->get('popularityRange')){
                    $datenow = &JFactory::getDate();
                    $date = $datenow->toMySQL();
                    $query.=" AND i.created > DATE_SUB('{$date}',INTERVAL ".$params->get('popularityRange')." DAY) ";
                }
                $query.=" GROUP BY i.id ";
                $orderby = 'numOfComments DESC';
                break;

            case 'modified':
                $orderby = 'i.modified DESC';
                break;

            default:
                $orderby = 'i.id DESC';
                break;
        }

        $query .= " ORDER BY ".$orderby;
        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList();

        require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php');
        $model = new K2ModelItem;

        if (count($items)) {

            foreach ($items as $item) {

                //Clean title
                $item->title = JFilterOutput::ampReplace($item->title);

                //Read more link
                $item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->categoryalias))));
                $item->introtext = self::prepareIntroText($item->introtext, $params->get('intro_limit'));

                //Item Image
                $item->image = self::getK2Images($item->id,$item->title,$item->introtext);


            }
        }

        return $items;
        }

    public static function getEasyBlogItems($params, $total_items)
   {

       $helper = JPATH_ROOT.DS.'components'.DS.'com_easyblog'.DS.'helpers'.DS.'helper.php';

       jimport( 'joomla.filesystem.file' );

       if( !JFile::exists( $helper ) ) return;

       require_once ($helper);
       require_once (JPATH_ROOT.DS.'components'.DS.'com_easyblog'.DS.'helpers'.DS.'date.php');
       require_once (JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_easyblog'.DS.'tables'.DS.'profile.php');

       $catid      = ($params->get('ezb_catfilter')) ? $params->get('ezb_catid',NULL) : '';
       $ordering   = $params->get('ezb_ordering','latest');
       $featured   = $params->get('ezb_featured', 0);

       $dimensions = array(
           'width'  => $params->get('thumbnails_width',100),
           'height' => $params->get('thumbnails_height',100)
       );

       $user 			=& JFactory::getUser();
       $category		=& JTable::getInstance( 'ECategory', 'Table' );
       $category->load($catid);

       if($category->private && $user->id == 0){
           echo JText::_('MOD_XPERTSLIDER_IS_CURRENTLY_SET_TO_PRIVATE');
           return;
       }

       if( !class_exists( 'EasyBlogModelBlog' ) ){
           jimport( 'joomla.application.component.model' );
           JLoader::import( 'blog' , EBLOG_ROOT . DS . 'models' );
       }

       $model = JModel::getInstance( 'Blog' , 'EasyBlogModel' );

       if( $params->get( 'ezfeatured') )
       {
           $posts = $model->getFeaturedBlog( $catid , $total_items );
       }
       else
       {
           $posts = $model->getBlogsBy('category', $catid, $ordering , $total_items , EBLOG_FILTER_PUBLISHED, null, false );
       }

       $config =& EasyBlogHelper::getConfig();

       if(! empty($posts)){
           for($i = 0; $i < count($posts); $i++){
               $row    	=& $posts[$i];
               $author 	=& JTable::getInstance( 'Profile', 'Table' );
               $row->author		= $author->load( $row->created_by );
               $row->commentCount 	= EasyBlogHelper::getCommentCount($row->id);
               $row->date			= EasyBlogDateHelper::toFormat( JFactory::getDate( $row->created ) , $config->get('layout_dateformat', '%A, %d %B %Y') );

               $requireVerification = false;
               if($config->get('main_password_protect', true) && !empty($row->blogpassword))
               {
                   $row->title	= JText::sprintf('COM_EASYBLOG_PASSWORD_PROTECTED_BLOG_TITLE', $row->title);
                   $requireVerification = true;
               }

               if($requireVerification && !EasyBlogHelper::verifyBlogPassword($row->blogpassword, $row->id))
               {
                   $theme = new CodeThemes();
                   $theme->set('id', $row->id);
                   $theme->set('return', base64_encode(EasyBlogRouter::_('index.php?option=com_easyblog&view=entry&id='.$row->id)));
                   $row->intro			= $theme->fetch( 'blog.protected.php' );
                   $row->content		= $row->intro;
                   $row->showRating	= false;
                   $row->protect		= true;
               }
               else
               {
                   $row->introtext		= EasyBlogHelper::getHelper( 'Videos' )->strip( $row->content );
                   $row->introtext      = self::prepareIntroText($row->introtext, $params->get('intro_limit'));
                   $row->image         = self::getImage($row->content);
                   $row->showRating	= true;
                   $row->protect		= false;
                   $row->link = EasyBlogRouter::_('index.php?option=com_easyblog&view=entry&id=' . $row->id );
               }
           }//end foreach
       }

       return $posts;
   }
    public static function loadScripts($params, $module_id)
    {
        $doc =& JFactory::getDocument();

        //load jquery first
        modXpertAccordionHelper::loadJquery($params);

        $js = "jQuery(#$module_id).collapse();";

        //$doc->addScriptDeclaration($js);

        if(!defined('XPERT_ACCORDION')){
            //add tab engine js file
            $doc->addScript(JURI::root(true).'/modules/mod_xpertaccordion/assets/js/xpertaccordion.min.js');
            define('XPERT_ACCORDION',1);
        }
    }

    public static function loadJquery($params)
    {
        $doc =& JFactory::getDocument();    //document object
        $app =& JFactory::getApplication(); //application object

        static $jqLoaded;

        if ($jqLoaded) {
            return;
        }

        if($params->get('load_jquery') AND !$app->get('jQuery')){
            //get the cdn
            $cdn = $params->get('jquery_source');
            switch ($cdn){
                case 'google_cdn':
                    $file = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js';
                    break;
                case 'local':
                    $file = JURI::root(true).'/modules/mod_xpertaccordion/assets/js/jquery-1.7.2.min.js';
                    break;
            }
            $app->set('jQuery','1.6');
            $doc->addScript($file);
            //$doc->addScriptDeclaration("jQuery.noConflict();");
            $jqLoaded = TRUE;
        }

    }

    public static function loadStyles($params,$module_id)
    {
        $app                = &JApplication::getInstance('site', array(), 'J');
        $template           = $app->getTemplate();
        $doc                = & JFactory::getDocument();
        $css                = '';
        $anchor             = $params->get('anchor','left');
        $anchor_position    = (int) $params->get('anchor_position',-100) . 'px';
        $width              = (int)$params->get('width',250).'px';
        $height             = (int)$params->get('height',250).'px';

        static $isStyleLoaded;

        $css .= "
            #$module_id .xc-block, #$module_id img{width:$width; height:$height;}
            #$module_id .xc-overlay{{$anchor}:{$anchor_position};}
        ";
        
        $doc->addStyleDeclaration($css);

        if($isStyleLoaded) return;

        $doc->addStyleSheet(JURI::root(true).'/modules/mod_xpertaccordion/assets/css/xpertaccordion.css');
        $isStyleLoaded = TRUE;
        
    }

    /***
     *
     * Get only large image from k2 image source, if failed then search for introtext.
     *
     * @params $id
     * @params $title
     * @params $text
     * @return $image_path
     *
     **/
    public static function getK2Images($id, $title, $text)
    {
        if (file_exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$id).'_L.jpg')) {
            $image_path = 'media/k2/items/cache/'.md5("Image".$id).'_L.jpg';
            $image_path = JURI::Root(true).'/'.$image_path;
            return $image_path;
        }
        elseif($text != NULL){

            return self::getImage($text);
        }
        else{
            echo "Image not found for article $title \n";
        }

    }

    /***
     *
     * Get image from given text.
     *
     * @params $text
     * @return $image path
     *
     */
    public static function getImage($text)
    {

        if(preg_match("/\<img.+?src=\"(.+?)\".+?\/>/", $text, $matches)){
            $image_path='';

            $paths = array();

            if (isset($matches[1])) {
                $image_path = $matches[1];
                //$image_path = JURI::Root(True)."/".$image_path;
            }
            return $image_path;
        }
        return false;

    }

    /***
     *
     * Stripe unnecessary html tags from given text and trim according to given limit.
     *
     * @params $text
     * @params $num_character
     * @return $text
     *
     */
    public static function prepareIntroText ($text, $num_character){
        $text = strip_tags($text,"</strong></em></a></span></p>");

        if(strlen($text)>$num_character && $num_character!=0){
            $text1 = substr ($text, 0, $num_character) . "..";
            return $text1;
        }
        else return $text;
    }
}