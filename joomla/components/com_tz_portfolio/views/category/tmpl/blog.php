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

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
?>

<div class="TzBlog blog<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
	<h2 class="TzCategoryTitle">
		<?php echo $this->escape($this->params->get('page_subheading')); ?>
		<?php if ($this->params->get('show_category_title')) : ?>
			<span class="subheading-category"><?php echo $this->category->title;?></span>
		<?php endif; ?>
	</h2>
	<?php endif; ?>

<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="TzCategoryDesc">
	<?php if ($this->params->get('show_description_image') AND isset($this->listImage->images) AND !empty($this -> listImage -> Images)) : ?>
        <?php
            $catParams  = $this -> category -> getParams();
        ?>
		    <img src="<?php echo JURI::root().$this -> listImage -> images; ?>"/>
	<?php endif; ?>
	<?php if ($this->params->get('show_description') && $this->category->description) : ?>
		<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_tz_portfolio.category'); ?>
	<?php endif; ?>
	<div class="clr"></div>
	</div>
<?php endif; ?>

<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
		<div class="TzCatChildren">
		<h3>
<?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?>
</h3>
			<?php echo $this->loadTemplate('children'); ?>
		</div>
	<?php endif; ?>

<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class="TzItemsLeading">
	<?php foreach ($this->lead_items as &$item) : ?>
		<div class="TzLeading leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
			<?php
				$this->item = &$item;
            
                $mediaParams    = $this -> mediaParams;

                if($mediaParams -> get('article_leading_image_size','L')){
                    $mediaParams -> set('article_leading_image_resize',$mediaParams -> get('article_leading_image_size','L'));
                }
                if($mediaParams -> get('article_leading_image_gallery_size','L')){
                    $mediaParams -> set('article_leading_image_gallery_resize',strtolower($mediaParams -> get('article_leading_image_gallery_size','L')));
                }
                $this -> assign('mediaParams',$mediaParams);

				echo $this->loadTemplate('item');
			?>
      <div class="clr"></div>
		</div>
		<?php
			$leadingcount++;
		?>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<?php
	$introcount=(count($this->intro_items));
	$counter=0;
?>
<?php if (!empty($this->intro_items)) : ?>

	<?php foreach ($this->intro_items as $key => &$item) : ?>
	<?php
		$key= ($key-$leadingcount)+1;
		$rowcount=( ((int)$key-1) %	(int) $this->columns) +1;
		$row = $counter / $this->columns ;

		if ($rowcount==1) : ?>
	<div class="TzItemsRow cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row ; ?>">
	<?php endif; ?>
	<div class="TzItem column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
		<?php
			$this->item = &$item;
            
            $mediaParams    = $this -> mediaParams;

            if($mediaParams -> get('article_leading_image_size')){
                $mediaParams -> set('article_leading_image_resize','');
            }
            if($mediaParams -> get('article_leading_image_gallery_size')){
                $mediaParams -> set('article_leading_image_gallery_resize','');
            }
            if($mediaParams -> get('article_secondary_image_size','M')){
                $mediaParams -> set('article_secondary_image_resize',$mediaParams -> get('article_secondary_image_size','M'));
            }
            if($mediaParams -> get('article_secondary_image_gallery_size','M')){
                $mediaParams -> set('article_secondary_image_gallery_resize',$mediaParams -> get('article_secondary_image_gallery_size','M'));
            }
            $this -> assign('mediaParams',$mediaParams);

			echo $this->loadTemplate('item');
		?>
    <div class="clr"></div>
	</div>
	<?php $counter++; ?>
	<?php if (($rowcount == $this->columns) or ($counter ==$introcount)): ?>
				<span class="row-separator"></span>
				</div>

			<?php endif; ?>
	<?php endforeach; ?>


<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>

	<?php echo $this->loadTemplate('links'); ?>

<?php endif; ?>




<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
		<div class="TzPagination">

				<?php echo $this->pagination->getPagesLinks(); ?>

      <?php  if ($this->params->def('show_pagination_results', 1)) : ?>
						<p class="TzCounter">
								<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
				<?php endif; ?>
		</div>
<?php  endif; ?>
<div class="clr"></div>
</div>
