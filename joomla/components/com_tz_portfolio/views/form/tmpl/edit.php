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

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
jimport('joomla.html.pane');

$doc    = &JFactory::getDocument();
$doc -> addStyleSheet(JURI::base().'components/com_tz_portfolio/css/tz_edit.css');
// Create shortcut to parameters.
$params = $this->state->get('params');
//$images = json_decode($this->item->images);
//$urls = json_decode($this->item->urls);

// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
if (!$editoroptions):
	$params->show_urls_images_frontend = '0';
endif;
$list       = $this -> listEdit;
if($list){
    $type   = $list -> type;
}
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'article.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('articletext')->save(); ?>
			Joomla.submitform(task);
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
<?php if ($params->get('show_page_heading', 1)) : ?>
<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_tz_portfolio&a_id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
	<fieldset>
		<legend><?php echo JText::_('JEDITOR'); ?></legend>

			<div class="formelm">
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
			</div>

		<?php if (is_null($this->item->id)):?>
			<div class="formelm">
			<?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?>
			</div>
		<?php endif; ?>

			<div class="formelm-buttons">
			<button type="button" onclick="Joomla.submitbutton('article.save')">
				<?php echo JText::_('JSAVE') ?>
			</button>
			<button type="button" onclick="Joomla.submitbutton('article.cancel')">
				<?php echo JText::_('JCANCEL') ?>
			</button>
			</div>
        <div class="clr"></div>
        <?php
            $tabs       =       &JPane::getInstance('tabs');
            echo $tabs -> startPane('tztabs');
                echo $tabs -> startPanel('Content','tztabs0');
        ?>
			<?php echo $this->form->getInput('articletext'); ?>
        <?php
            echo $tabs -> endPanel();
            echo $tabs -> startPanel('Image', 'tztabs1');
        ?>
            <div id="tz_images">
                <table class="admintable" style="width: 100%">
                    <tr>
                        <td style="background: #F6F6F6; min-width:100px;" align="right" rowspan="2" valign="top">
                            <strong><?php echo JText::_('Image');?></strong>
                        </td>
                        <td>
                            <input type="file" name="tz_img" id="tz_img" value="">
                        </td>
                    </tr>
                    <tr>
                        <td id="tz_img_server">
                        </td>
                    </tr>
                    <tr>
                        <td style="background: #F6F6F6; min-width:100px;" align="right">
                            <strong><?php echo JText::_('Image title');?></strong>
                        </td>
                        <td>
                            <input type="text" name="tz_image_title" id="tz_image_title"
                                   value="<?php //echo $list -> imagetitle;?>">
                            <input type="hidden" name="tz_img_image" value="image">
                        </td>
                    </tr>
                    <tr>
                        <td style="background: #F6F6F6; min-width:100px;" align="right" rowspan="2" valign="top">
                            <strong><?php echo JText::_('Image Hover');?></strong>
                        </td>
                        <td>
                            <input type="file" name="tz_img_hover" id="tz_img_hover" value="">
                        </td>
                    </tr>
                    <tr>
                        <td id="tz_img_hover_server">
                        </td>
                    </tr>
                </table>
            </div>
        <?php
            echo $tabs -> endPanel();
            echo $tabs -> startPanel('Image gallery', 'tztabs2');
        ?>
            <div>
                <table  id="tz_image_gallery">
                    <tr>
                        <td id="tz_img_gallery"><!--<input type="text" class="inputbox image-select" name="img_old[]" id="image-select-1" value="" style="width:283px;" />--></td>
                    </tr>
                </table>
            </div>
        <?php
            echo $tabs -> endPanel();
            echo $tabs -> startPanel('Media', 'tztabs3');
        ?>
        <div id="tz_media">
                    <table>
                        <tr>
                            <td style="background: #F6F6F6; min-width:100px;" align="right" valign="top">
                                <strong><?php echo JText::_('Media type')?></strong>
                            </td>
                            <td>
                                <select name="tz_media_type" id="tz_media_type">
                                    <option value="default"<?php echo ($list -> video -> type =='default')?' selected="selected"':''?>>Default</option>
                                    <option value="youtube"<?php echo ($list -> video -> type =='youtube')?' selected="selected"':''?>>Youtube</option>
                                    <option value="vimeo"<?php echo ($list -> video -> type =='vimeo')?' selected="selected"':''?>>Vimeo</option>

                                </select>
                            </td>
                        </tr>
                        <tr id="tz_media_code_outer">
                            <td style="background: #F6F6F6; min-width:100px;" align="right" valign="top">
                                <strong><?php echo JText::_('Media source')?></strong>
                            </td>
                            <td id="tz_media_code">
                                <?php echo JText::_('Paste HTML embed code below ');?><br/>
                                <textarea rows="10" cols="20" name="tz_media_code">
                                    <?php echo $list -> video -> code;?>
                                </textarea>
                            </td>
                        </tr>
                        <tr id="tz_thumb">
                            <td id="tz_thumb_inner" valign="top" align="right" style="background: #F6F6F6; min-width:100px;">
                            </td>
                            <td id="tz_thumb_preview">

                            </td>
                        </tr>
                        <tr>
                            <td style="background: #F6F6F6; min-width:100px;" align="right" valign="top">
                                <strong><?php echo JText::_('Media title');?></strong>
                            </td>
                            <td id="tz_media_title">
                                <input type="text"
                                       name="tz_media_title"
                                       value="<?php echo trim($list -> video -> title);?>">
                            </td>

                        </tr>

                    </table>
                </div>
        <?php
            echo $tabs -> endPanel();
            echo $tabs -> startPanel('Fields','tztabs4');
        ?>
            <div id="tz_fields"><table></table></div>
        <?php
            echo $tabs -> endPanel();
            echo $tabs -> startPanel('Attachments','tztabs5')
        ?>
            <div id="tz_attachments">
                <?php
                if($this -> listAttach):
                ?>
                <table class="adminlist" id="tz_attachments_show">
                    <thead style="font-weight: bold;">
                        <tr>
                            <td>Filename</td>
                            <td>Title</td>
                            <td width="15%">Status</td>
                        </tr>
                    </thead>
                    <tbody id="tz_attachments_body"></tbody>
                </table>
                <?php endif; ?>

                <table id="tz_attachments_table"></table>
            </div>
            <div class="clr"></div>
        <?php
            echo $tabs -> endPanel();
        echo $tabs -> endPane();
        ?>
	</fieldset>
	<?php if ($params->get('show_urls_images_frontend')  ): ?>
	<fieldset>
		<legend><?php echo JText::_('COM_CONTENT_IMAGES_AND_URLS'); ?></legend>
			<div class="formelm">
			<?php echo $this->form->getLabel('image_intro', 'images'); ?>
			<?php echo $this->form->getInput('image_intro', 'images'); ?>
			</div>
			<div style="clear:both"></div>
			<div class="formelm">
			<?php echo $this->form->getLabel('image_intro_alt', 'images'); ?>
			<?php echo $this->form->getInput('image_intro_alt', 'images'); ?>
			</div>
			<div class="formelm">
			<?php echo $this->form->getLabel('image_intro_caption', 'images'); ?>
			<?php echo $this->form->getInput('image_intro_caption', 'images'); ?>
			</div>
			<div class="formelm">
			<?php echo $this->form->getLabel('float_intro', 'images'); ?>
			<?php echo $this->form->getInput('float_intro', 'images'); ?>
			</div>

			<div class="formelm">
			<?php echo $this->form->getLabel('image_fulltext', 'images'); ?>
			<?php echo $this->form->getInput('image_fulltext', 'images'); ?>
			</div>
			<div style="clear:both"></div>
			<div class="formelm">
			<?php echo $this->form->getLabel('image_fulltext_alt', 'images'); ?>
			<?php echo $this->form->getInput('image_fulltext_alt', 'images'); ?>
			</div>
			<div class="formelm">
			<?php echo $this->form->getLabel('image_fulltext_caption', 'images'); ?>
			<?php echo $this->form->getInput('image_fulltext_caption', 'images'); ?>
			</div>
			<div class="formelm">
			<?php echo $this->form->getLabel('float_fulltext', 'images'); ?>
			<?php echo $this->form->getInput('float_fulltext', 'images'); ?>
			</div>

			<div  class="formelm">
			<?php echo $this->form->getLabel('urla', 'urls'); ?>
			<?php echo $this->form->getInput('urla', 'urls'); ?>
			</div>
			<div  class="formelm">
			<?php echo $this->form->getLabel('urlatext', 'urls'); ?>
			<?php echo $this->form->getInput('urlatext', 'urls'); ?>
			</div>
			<?php echo $this->form->getInput('targeta', 'urls'); ?>
			<div  class="formelm">
			<?php echo $this->form->getLabel('urlb', 'urls'); ?>
			<?php echo $this->form->getInput('urlb', 'urls'); ?>
			</div>
			<div  class="formelm">
			<?php echo $this->form->getLabel('urlbtext', 'urls'); ?>
			<?php echo $this->form->getInput('urlbtext', 'urls'); ?>
			</div>
			<?php echo $this->form->getInput('targetb', 'urls'); ?>
			<div  class="formelm">
			<?php echo $this->form->getLabel('urlc', 'urls'); ?>
			<?php echo $this->form->getInput('urlc', 'urls'); ?>
			</div>
			<div  class="formelm">
			<?php echo $this->form->getLabel('urlctext', 'urls'); ?>
			<?php echo $this->form->getInput('urlctext', 'urls'); ?>
			</div>
			<?php echo $this->form->getInput('targetc', 'urls'); ?>
	</fieldset>
	<?php endif; ?>

	<fieldset>
		<legend><?php echo JText::_('COM_CONTENT_PUBLISHING'); ?></legend>
		<div class="formelm">
		<?php echo $this->form->getLabel('catid'); ?>
		<?php if($this->params->get('enable_category', 0) == 1) : ?>
		<span class="category">
		<?php echo $this->category_title; ?>
		</span>
		<?php else : ?>
		<?php echo $this->form->getInput('catid', null, $this->item->catid); ?>
		<?php endif;?>
		</div>
        <script type="text/javascript">
            // extra fields
            window.addEvent('domready', function() {

                var tz_portfolio_extraFields = function(){

                    var jSonRequest = new Request.JSON({url: "index.php?option=com_tz_portfolio&task=form.listsfields",
                        onComplete: function(item) {
                            $('tz_fields').empty();
                            var myFields = new Element('table',{
                                width:'100%',
                                class:'admintable',
                                id:'fields'
                                //html:item
                            });

                            myFields.inject($('tz_fields'));
                            myFields.innerHTML = item.data;

                        },
                        data: {
                            json: JSON.encode({
                                'groupid':$('groupid').value,
                                'id':'<?php echo $this -> item -> id;?>',
                                'catid':$('jform_catid').value
                            })
                        }
                    }).send();

                }

                var jSonRequest2 = new Request.JSON({url: "index.php?option=com_tz_portfolio&task=form.selectgroup",
                    onComplete: function(item){
                        tz_portfolio_extraFields();
                    },
                    data:{
                        json2: JSON.encode({
                            'catid':$('jform_catid').value,
                            'id':'<?php echo $this -> item -> id;?>',
                            'groupid':$('groupid').value
                        })
                    }
                }).send();

                $('jform_catid').addEvent('change',function(e){
                    e.stop();
                    
                    var jSonRequest2 = new Request.JSON({url: "index.php?option=com_tz_portfolio&task=form.selectgroup",
                        onComplete: function(){
                            tz_portfolio_extraFields();
                        },
                        data:{
                            json2: JSON.encode({
                                'catid':$('jform_catid').value,
                                'id':'<?php echo $this -> item -> id;?>',
                                'groupid':$('groupid').value
                            })
                        }
                    }).send();
                });

                var tz_portfolio_groupChange = function(){
                    $('groupid').addEvent('change',function(e){
                        e.stop();
                        tz_portfolio_extraFields();
                    });
                }
                tz_portfolio_groupChange();

            });

            //Media
            window.addEvent('domready',function(){
                function tz_thumb(){
                    <?php //if(!empty($list -> video -> thumb)):?>
                    if($('tz_thumb'))
                        $('tz_thumb').dispose();
                    <?php //endif;?>
                    var myTr = new Element('tr', {id: 'tz_thumb'})
                            .inject($('tz_media_code_outer'),'after');
                    var myThumbInner    = new Element('td',{
                        id: 'tz_thumb_inner',
                        valign: "top",
                        align: "right",
                        style: "background: #F6F6F6; min-width:100px;"
                    }).inject(myTr);
                    var myElement  = new Element('strong');
                    myElement.appendText('<?php echo JText::_('Thumbnail');?>');
                    myThumbInner.adopt(myElement);

                    var myThumbPre  = new Element('td',{id: 'tz_thumb_preview'}).inject(myThumbInner,'after');

                    var tz_e = location.href.match(/^(.+)\/index\.php.*/i)[1];

                    var tz_a = new Element('input',{
                        type:"text",
                        class:"inputbox image-select",
                        name:"tz_thumb",
                        id:"image-thumb",
                        readonly:'true',
                        style:"width:200px;"
                    });
                    tz_a.inject($('tz_thumb_preview'));
                    var tz_d = "image-thumb",
                        tz_b = (new Element("button", {
                            type: "button",
                            "id": "tz_thumb_button"
                        })).set('text', "Browse server").injectAfter(tz_a),
                        tz_f = (new Element("button", {
                            "name": "tz_thumb_cancel",
                            "id"  : "tz_thumb_cancel",
                            html:'Reset'
                        })).inject(tz_b,'after'),
                        tz_g = (new Element("div", {
                            "class": "tz-image-preview",
                            "style": "clear:both;"
                        })).inject(tz_f,'after');

                    if(tz_g)
                        tz_g.empty();

                    tz_a.setProperty("id", tz_d);
                    <?php
                        if($list -> video -> type == 'default' AND !empty($list -> video -> thumb)):
                            $src    = JURI::root().str_replace('.'.JFile::getExt($list -> video -> thumb)
                                ,'_S.'.JFile::getExt($list -> video -> thumb),$list -> video -> thumb);
                            $src2   = JURI::root().str_replace('.'.JFile::getExt($list -> video -> thumb)
                                ,'_L.'.JFile::getExt($list -> video -> thumb),$list -> video -> thumb);
                    ?>
                        var tz_hidden   = new Element('input',{
                           type: 'hidden',
                            name: 'tz_thumb_hidden',
                            value: '<?php echo $list -> video -> thumb;?>'
                        }).inject(tz_g);
                        var tz_img = new Element("img", {
                            src: '<?php echo $src;?>',
                            style: 'cursor:pointer; max-width: 200px;'
                        }).inject(tz_g);
                        tz_img.addEvent('click',function(){
                            SqueezeBox.fromElement(this, {
                                handler: "image",
                                url: '<?php echo $src2;?>'
                            });
                        });
                        var tz_checkbox = new Element('input',{
                            type: 'checkbox',
                            style:'clear:both;',
                            name: 'tz_thumb_del',
                            id: 'tz_thumb_del'
                        }).inject(tz_img,'after');
                        var tz_label = new Element('label',{
                            'for': 'tz_thumb_del',
                            style: 'clear: none; margin: 2px 3px;',
                            html: '<?php echo JText::_('Check this box to delete current image or just upload a new image to replace the existing one');?>'
                        }).inject(tz_checkbox,'after');


                    <?php endif;?>

                    tz_f.addEvent("click", function (e) {
                        e.stop();
                        $('image-thumb').value='';
                        tz_a.setProperty("value", "");
                    });

                    tz_b.addEvent("click", function (h) { (new Event(h)).stop();
                        SqueezeBox.fromElement(this, {
                            handler: "iframe",
                            url: "index.php?option=com_media&view=images&tmpl=component&asset=<?php echo JFactory::getUser() -> id;?>&author=<?php echo JFactory::getUser() -> id;?>&e_name=" + tz_d,
                            size: {
                                x: 800,
                                y: 500
                            }
                        });

                        window.jInsertEditorText = function (text, editor) {
                            if (editor.match(/^image-thumb/)) {

                                var d = $(editor);
                                var src = text.match(/src=\".*?\"/i);
                                src = String.from(src);
                                src = src.replace(/^src=\"/g,'');
                                src = src.replace(/\"$/g,'');
                                d.setProperty("value", src);
                            } else tinyMCE.execInstanceCommand(editor, 'mceInsertContent',false,text);
                        };

                    });

                }
                switch ($('tz_media_type').value){
                        case 'youtube':
                            if($('tz_media_code_youtube')){
                                <?php if($list -> video -> type == 'youtube' AND empty($list -> video -> code)):?>
                                    $('tz_media_code_youtube').value = '';
                                <?php endif;?>
                            }

                            if($('tz_thumb'))
                                $('tz_thumb').dispose();
                            $('tz_media_code').empty();
                            var myCode = new Element('input',{
                                type  : 'text',
                                name  : 'tz_media_code_youtube',
                                size  : '30',
                                value : '<?php if($list -> video -> type == 'youtube')
                                    echo $list -> video -> code;?>'
                            }).inject($('tz_media_code'));
                            var myLabel = new Element('i',{
                                html : 'Paste code '+$('tz_media_type').value
                            }).inject($('tz_media_code'));
                            $('tz_media_title').empty();
                            var myTitle = new Element('input',{
                                type:'text',
                                name:'tz_media_title_youtube',
                                value:'<?php if($list -> video -> type == 'youtube')
                                    echo $list -> video -> title;?>'
                            }).inject($('tz_media_title'));

                            if($('tz_media_type').value == 'youtube'){
                                if($('tz_thumb_preview_youtube'))
                                    $('tz_thumb_preview_youtube').empty();

                                if(myCode.value.trim().length != 0){
                                    var video   = new Element('div',{id:'tz_thumb_preview_youtube'}).inject(myLabel,'after');
                                    var iframe  = new Element('img',{
                                        style: 'margin-top:10px; cursor:pointer; max-width: 200px;',
                                        src:'http://img.youtube.com/vi/'+ myCode.value+'/hqdefault.jpg'
                                    }).inject(video);
                                    iframe.addEvent('click',function(){
                                       SqueezeBox.fromElement(this, {
                                            handler: "image",
                                            url: 'http://img.youtube.com/vi/'+ myCode.value+'/hqdefault.jpg'
                                       });
                                    });
                                }
                            }
                            break;
                        case 'vimeo':
                            if($('tz_thumb'))
                                $('tz_thumb').dispose();
                            $('tz_media_code').empty();
                            var myCode = new Element('input',{
                                type  : 'text',
                                name  : 'tz_media_code_vimeo',
                                size  : '30',
                                value : '<?php if($list -> video -> type =='vimeo')
                                    echo $list -> video -> code;?>'
                            }).inject($('tz_media_code'));
                            var myLabel = new Element('i',{
                                html : 'Paste code '+$('tz_media_type').value
                            }).inject($('tz_media_code'));
                            $('tz_media_title').empty();
                            var myTitle = new Element('input',{
                                type:'text',
                                name:'tz_media_title_vimeo',
                                value:'<?php if($list -> video -> type =='vimeo')
                                    echo $list -> video -> title;?>'
                            }).inject($('tz_media_title'));

                            if($('tz_thumb_preview_vimeo'))
                                $('tz_thumb_preview_vimeo').empty();
                            <?php
                                if($list -> video -> type == 'vimeo' AND !empty($list -> video -> thumb)):
                                    $src    = JURI::root().str_replace('.'.JFile::getExt($list -> video -> thumb)
                                        ,'_S.'.JFile::getExt($list -> video -> thumb),$list -> video -> thumb);
                                    $src2   = JURI::root().str_replace('.'.JFile::getExt($list -> video -> thumb)
                                        ,'_L.'.JFile::getExt($list -> video -> thumb),$list -> video -> thumb);
                            ?>
                                var video   = new Element('div',{id:'tz_thumb_preview_vimeo'}).inject(myLabel,'after');
                                var iframe  = new Element('img',{
                                    style: 'margin-top:10px; max-width:200px; cursor:pointer;',
                                    src: '<?php echo $src;?>'
                                }).inject(video);
                                iframe.addEvent('click',function(){
                                   SqueezeBox.fromElement(this, {
                                        handler: "image",
                                        url: '<?php echo $src2;?>'
                                   });
                                });
                            <?php endif;?>
                            break;
                        default:
                            tz_thumb();
                            $('tz_media_code').empty();
                            var myLabel = new Element('label',{
                                html : 'Paste HTML embed code below '
                            }).inject($('tz_media_code'));
                            new Element('div',{
                                style:'clear:both'
                            }).injectAfter(myLabel);
                            var myCode = new Element('textarea',{
                                name  : 'tz_media_code',
                                size  : '30',
                                rows  : '10',
                                cols  : '20',
                                value : '<?php if($list -> video -> type == 'default') echo $list -> video -> code;?>'
                            }).inject($('tz_media_code'));
                            $('tz_media_title').empty();
                            var myTitle = new Element('input',{
                                type:'text',
                                name:'tz_media_title',
                                value:'<?php if($list -> video -> type == 'default') echo $list -> video -> title;?>'
                            }).inject($('tz_media_title'));
                        break;
                    }
                $('tz_media_type').addEvent('change',function(){

                    switch ($('tz_media_type').value){
                        case 'youtube':
                            if($('tz_media_code_youtube')){
                                <?php if($list -> video -> type != 'youtube'):?>
                                    $('tz_media_code_youtube').value = '';
                                <?php endif;?>
                            }
                            if($('tz_thumb'))
                                $('tz_thumb').dispose();
                            $('tz_media_code').empty();
                            var myCode = new Element('input',{
                                type  : 'text',
                                name  : 'tz_media_code_youtube',
                                id  : 'tz_media_code_youtube',
                                size  : '30',
                                value : '<?php if($list -> video -> type == 'youtube')
                                    echo $list -> video -> code;?>'
                            }).inject($('tz_media_code'));
                            var myLabel = new Element('i',{
                                html : 'Paste code '+$('tz_media_type').value
                            }).inject($('tz_media_code'));
                            $('tz_media_title').empty();
                            var myTitle = new Element('input',{
                                type:'text',
                                name:'tz_media_title_youtube',
                                value:'<?php if($list -> video -> type == 'youtube')
                                    echo $list -> video -> title;?>'
                            }).inject($('tz_media_title'));

                            if($('tz_media_type').value == 'youtube'){
                                if($('tz_thumb_preview_youtube'))
                                    $('tz_thumb_preview_youtube').empty();

                                if(myCode.value.trim().length != 0){
                                    var video   = new Element('div',{id:'tz_thumb_preview_youtube'}).inject(myLabel,'after');
                                    var iframe  = new Element('img',{
                                        style: 'margin-top:10px; cursor:pointer; max-width: 200px;',
                                        src:'http://img.youtube.com/vi/'+ myCode.value+'/hqdefault.jpg'
                                    }).inject(video);
                                    iframe.addEvent('click',function(){
                                       SqueezeBox.fromElement(this, {
                                            handler: "image",
                                            url: 'http://img.youtube.com/vi/'+ myCode.value+'/hqdefault.jpg'
                                       });
                                    });
                                }
                            }

                            myCode.addEvent('change',function(){
                                if($('tz_media_type').value == 'youtube'){
                                    if($('tz_thumb_preview_youtube'))
                                        $('tz_thumb_preview_youtube').empty();

                                    if(myCode.value.trim().length != 0){
                                        var video   = new Element('div',{id:'tz_thumb_preview_youtube'}).inject(myLabel,'after');
                                        var iframe  = new Element('img',{
                                            style: 'margin-top:10px; cursor:pointer; max-width: 200px;',
                                            src:'http://img.youtube.com/vi/'+ myCode.value+'/hqdefault.jpg'
                                        }).inject(video);
                                        iframe.addEvent('click',function(){
                                           SqueezeBox.fromElement(this, {
                                                handler: "image",
                                                url: 'http://img.youtube.com/vi/'+ myCode.value+'/hqdefault.jpg'
                                           });
                                        });
                                    }
                                }
                            });
                            break;
                        case 'vimeo':
                            if($('tz_media_code_vimeo')){
                                <?php if($list -> video -> type != 'vimeo'):?>
                                    $('tz_media_code_vimeo').value = '';
                                <?php endif;?>
                            }
                            if($('tz_thumb'))
                                $('tz_thumb').dispose();
                            $('tz_media_code').empty();
                            var myCode = new Element('input',{
                                type  : 'text',
                                name  : 'tz_media_code_vimeo',
                                id  : 'tz_media_code_vimeo',
                                size  : '30',
                                value : '<?php if($list -> video -> type =='vimeo')
                                    echo $list -> video -> code;?>'
                            }).inject($('tz_media_code'));
                            var myLabel = new Element('i',{
                                html : 'Paste code '+$('tz_media_type').value
                            }).inject($('tz_media_code'));
                            $('tz_media_title').empty();
                            var myTitle = new Element('input',{
                                type:'text',
                                name:'tz_media_title_vimeo',
                                value:'<?php if($list -> video -> type =='vimeo')
                                    echo $list -> video -> title;?>'
                            }).inject($('tz_media_title'));

                            if($('tz_thumb_preview_vimeo'))
                                $('tz_thumb_preview_vimeo').empty();
                            <?php
                                if($list -> video -> type == 'vimeo' AND !empty($list -> video -> thumb)):
                                    $src    = JURI::root().str_replace('.'.JFile::getExt($list -> video -> thumb)
                                        ,'_S.'.JFile::getExt($list -> video -> thumb),$list -> video -> thumb);
                                    $src2   = JURI::root().str_replace('.'.JFile::getExt($list -> video -> thumb)
                                        ,'_L.'.JFile::getExt($list -> video -> thumb),$list -> video -> thumb);
                            ?>
                                var video   = new Element('div',{id:'tz_thumb_preview_vimeo'}).inject(myLabel,'after');
                                var iframe  = new Element('img',{
                                    style: 'margin-top:10px; max-width:200px; cursor:pointer;',
                                    src: '<?php echo $src;?>'
                                }).inject(video);
                                iframe.addEvent('click',function(){
                                   SqueezeBox.fromElement(this, {
                                        handler: "image",
                                        url: '<?php echo $src2;?>'
                                   });
                                });
                            <?php endif;?>

                            myCode.addEvent('change',function(){

                                if($('tz_media_type').value == 'vimeo'){
                                    var vimeoVideoID = myCode.value;

                                   var ajaxreg = new Request.JSON({
                                       url: 'index.php?option=com_tz_portfolio&task=article.getThumb',
                                       onComplete: function(data){
                                            if($('tz_thumb_preview_2'))
                                                $('tz_thumb_preview_2').empty();
                                            if(data && data.length !=0){
                                                var video   = new Element('div',{id:'tz_thumb_preview_vimeo'}).inject(myLabel,'after');
                                                var iframe  = new Element('img',{
                                                    style: 'margin-top:10px; max-width:200px; cursor:pointer;',
                                                    src: data
                                                }).inject(video);
                                                iframe.addEvent('click',function(){
                                                   SqueezeBox.fromElement(this, {
                                                        handler: "image",
                                                        url: data
                                                   });
                                                });
                                            }
                                       },
                                       data: {
                                           json: JSON.encode({
                                              'videocode': myCode.value
                                           })
                                       }
                                   }).send();

                                }
                            });

                            break;
                        default:
                            tz_thumb();
                            $('tz_media_code').empty();
                            var myLabel = new Element('label',{
                                html : 'Paste HTML embed code below '
                            }).inject($('tz_media_code'));
                            new Element('div',{
                                style:'clear:both'
                            }).injectAfter(myLabel);
                            var myCode = new Element('textarea',{
                                name  : 'tz_media_code',
                                size  : '30',
                                rows  : '10',
                                cols  : '20',
                                value : '<?php if($list -> video -> type == 'default') echo $list -> video -> code;?>'
                            }).inject($('tz_media_code'));
                            $('tz_media_title').empty();
                            var myTitle = new Element('input',{
                                type:'text',
                                name:'tz_media_title',
                                value:'<?php if($list -> video -> type == 'default') echo $list -> video -> title;?>'
                            }).inject($('tz_media_title'));
                        break;
                    }
                });
            });

            // Image, Image gallery
            window.addEvent("domready", function () {
                var tz_count=0;
                var tz_portfolio_image = function(id,name,value,title,i){

                    var tz_e = location.href.match(/^(.+)\/index\.php.*/i)[1];

                    var tz_a = new Element('input',{
                        type:"text",
                        class:"inputbox image-select",
                        name:name,
                        id:"image-select-"+tz_count,
                        readonly:'true',
                        style:"width:200px;"
                    });
                    tz_a.inject($(id));
                    var tz_d = "image-select-" + tz_count,
                        tz_b = (new Element("button", {
                            type: "button",
                            "id": "tz_img_button"+tz_count
                        })).set('text', "Browse server").injectAfter(tz_a),
                        tz_f = (new Element("button", {
                            "name": "tz_img_cancel_"+i,
                            html:'Reset'
                        })).injectAfter(tz_b),
                        tz_g = (new Element("div", {
                            "class": "tz-image-preview",
                            "style": "clear:both;"
                        })).injectAfter(tz_f);

                    tz_a.setProperty("id", tz_d);
                    if(value){
                         var tz_h = (new Element("img", {
                            src: value,
                            style:'max-width:300px; cursor:pointer;',
                            title:title
                        })).injectInside(tz_g);
                        tz_h.addEvent('click',function(){
                           SqueezeBox.fromElement(this, {
                                handler: "image",
                                url: String.from(value.replace(/_S/,'_L'))
                            });
                        });
                    }



                    tz_f.addEvent("click", function (e) {
                        e.stop();
                        $('tz_img').value='';
                        $('tz_image_title').value='';
                        $('tz_image_gallery_title_'+i).value='';

                        $('tz_img_client_'+i).value='';
                        tz_a.setProperty("value", "");
                    });

                    tz_b.addEvent("click", function (h) { (new Event(h)).stop();
                        SqueezeBox.fromElement(this, {
                            handler: "iframe",
                            url: "index.php?option=com_media&view=images&tmpl=component&asset=<?php echo JFactory::getUser() -> id;?>&author=<?php echo JFactory::getUser() -> id;?>&e_name=" + tz_d,
                            size: {
                                x: 800,
                                y: 500
                            }
                        });

                        window.jInsertEditorText = function (text, editor) {
                            if (editor.match(/^image-select-/)) {

                                var d = $(editor);
                                var src = text.match(/src=\".*?\"/i);
                                src = String.from(src);
                                src = src.replace(/^src=\"/g,'');
                                src = src.replace(/\"$/g,'');
                                d.setProperty("value", src);
                            } else tinyMCE.execInstanceCommand(editor, 'mceInsertContent',false,text);
                        };

                    });
                    tz_count++;
                }

            $('tz_img_server').empty();
            <?php
            if(!empty($list -> images)){
                $src    = null;
                if($pos = strpos($list -> images,'.')){
                    $ext    = substr($list -> images,$pos,strlen($list -> images));
                    $src    = JURI::root().str_replace($ext,'_S'.$ext,$list -> images);
                }

            ?>
            tz_portfolio_image('tz_img_server','tz_img_gallery_server[]','<?php echo $src;?>','<?php echo $list -> imagetitle?>',0);
            var tz_hidden = new Element('input',{
                'type': 'hidden',
                'name': 'tz_image_current',
                'value': '<?php echo $list -> images; ?>'
            }).inject($('tz_img_server'));
            var tz_checkbox = new Element("input",{
                type: 'checkbox',
                id: 'tz_del_image',
                'name': 'tz_delete_image',
                'value': 0,
                style: 'clear: both'
            }).inject($('tz_img_server'));
            var tz_label = new Element('label',{
                'for': 'tz_del_image',
                style: 'clear: none; margin: 2px 3px;',
                html: '<?php echo JText::_('Check this box to delete current image or just upload a new image to replace the existing one');?>'
            }).inject($('tz_img_server'));

            <?php
            }
            else{
            ?>
                tz_portfolio_image('tz_img_server','tz_img_gallery_server[]','','',0);
            <?php
            }
            ?>

            <?php if(!empty($list -> images_hover)){?>
                <?php
                    $src    = null;
                    if($pos = strpos($list -> images_hover,'.')){
                        $ext    = substr($list -> images_hover,$pos,strlen($list -> images_hover));
                        $src    = JURI::root().str_replace($ext,'_S'.$ext,$list -> images_hover);
                    }
                ?>
                tz_portfolio_image('tz_img_hover_server','tz_img_hover_server','<?php echo $src;?>','<?php echo $list -> imagetitle?>',0);
                var tz_hidden = new Element('input',{
                    'type': 'hidden',
                    'name': 'tz_imgHover_current',
                    'value': '<?php echo $list -> images_hover; ?>'
                }).inject($('tz_img_hover_server'));
                var tz_checkbox = new Element("input",{
                    type: 'checkbox',
                    id: 'tz_del_imgHover',
                    'name': 'tz_delete_imgHover',
                    'value': 0,
                    style: 'clear: both'
                }).inject($('tz_img_hover_server'));
                var tz_label = new Element('label',{
                    'for': 'tz_del_imgHover',
                    style: 'clear: none; margin: 2px 3px;',
                    html: '<?php echo JText::_('Check this box to delete current image or just upload a new image to replace the existing one');?>'
                }).inject($('tz_img_hover_server'));
            <?php
            }
            else{
            ?>
                tz_portfolio_image('tz_img_hover_server','tz_img_hover_server','','',0);
            <?php } ?>
            $('tz_image_gallery').empty();

            var _tz_portfolio_myGallery = function(name,title,i){
                var myTr = new Element('tr',{});
                myTr.inject($('tz_image_gallery'));
                var myTd = new Element('td',{
                    html:'<strong>Image</strong>',
                    valign:'top',
                    align:'right',
                    rowspan:'2',
                    styles:{
                        background: '#F6F6F6',
                        'min-width':'100px'
                    }
                });
                myTd.inject(myTr);
                var myTd = new Element('td',{});
                myTd.inject(myTr);
                var myFile = new Element('input',{
                    type:'file',
                    id:'tz_img_client_'+i,
                    name:'tz_img_client[]',
                    size:'50px'
                });
                myFile.inject(myTd);

                //row 2
                var myTr2 = new Element('tr',{});
                myTr2.inject($('tz_image_gallery'));
                var myTd = new Element('td',{
                });
                myTd.inject(myTr2);

                var myField = tz_portfolio_image(myTd,'tz_img_gallery_server[]',name,title,i);

                if(name.length >0){
                    var tz_hidden = new Element('input',{
                        'type': 'hidden',
                        'name': 'tz_image_gallery_current[]',
                        'value': name
                    }).inject(myTd);
                    var tz_checkbox = new Element("input",{
                        type: 'checkbox',
                        id: 'tz_del_gallery_'+i,
                        'name': 'tz_delete_image_gallery[]',
                        'value': i,
                        style: 'clear: both'
                    }).inject(myTd);
                    var tz_label = new Element('label',{
                        'for': 'tz_del_gallery_'+i,
                        style: 'clear: none; margin: 2px 3px;',
                        html: '<?php echo JText::_('Check this box to delete current image or just upload a new image to replace the existing one');?>'
                    }).inject(myTd);
                }

                //row 3
                var myTr3 = new Element('tr',{});
                myTr3.inject($('tz_image_gallery'));
                var myTd = new Element('td',{
                    html:'<strong>Image title</strong>',
                    align:'right',
                    valign:'top',
                    styles:{
                        background: '#F6F6F6',
                        'min-width':'100px'
                    }
                });
                myTd.inject(myTr3);
                var myTd = new Element('td',{});
                myTd.inject(myTr3);

                var myInput = new Element('input',{
                    type:'text',
                    id:'tz_image_gallery_title_'+i,
                    name:'tz_image_gallery_title[]',
                    size:'50',
                    value:title
                });
                myInput.inject(myTd);

                //row 4
                var myTr4 = new Element('tr',{});
                myTr4.inject($('tz_image_gallery'));
                var myTd = new Element('td',{
                });
                myTd.inject(myTr4);
                var myTd = new Element('td',{
                    align:'right',
                    styles:{
                        //float:'right'
                    }
                });
                myTd.inject(myTr4);

                if(tz_count>2){
                    var myRemove = new Element('input',{
                        type:'button',
                        name:'tz_remove_image_'+i,
                        value:'Remove',
                       events:{
                           click:function(e){
                               e.stop();
                               myTr.dispose();
                               myTr2.dispose();
                               myTr3.dispose();
                               myTr4.dispose();
                           }
                       }
                    });
                    myRemove.inject(myTd);
                }
            };

            var hidden = new Element('input',{
                type:'hidden',
                name:'tz_img_type',
                value:'imagegallery'
            });
            hidden.injectAfter($('tz_image_gallery'));
            var k=1;
            var myGallery = new Element('button',{
                html:'Add new',
                events:{
                    click: function(e){
                        e.stop();
                        _tz_portfolio_myGallery('','',k);
                        k++;
                    }
                }
            });
            myGallery.inject($('tz_image_gallery'));
            var myDiv = (new Element("div", {
                "style": "clear:both;"
            })).injectAfter(myGallery);

             <?php
            if(count($list -> gallery -> images)>1){
                $galleryTitle   = $list -> gallery -> title;
                foreach($list -> gallery -> images as $i => $item):
                    $src    = null;
                    if($pos = strpos($item,'.')){
                        $ext    = substr($item,$pos,strlen($item));
                        $src    = JURI::root().str_replace($ext,'_S'.$ext,$item);
                    }

            ?>

                _tz_portfolio_myGallery('<?php echo $src;?>','<?php echo $galleryTitle[$i];?>',<?php echo $i;?>);
            <?php
                endforeach;
            }
            else{
                $src    = null;
                if($pos = strpos($list -> gallery -> images,'.')){
                    $ext    = substr($list -> gallery -> images,$pos,strlen($list -> gallery -> images));
                    $src    = JURI::root().str_replace($ext,'_S'.$ext,$list -> gallery -> images);
                }

            ?>
                _tz_portfolio_myGallery('<?php echo $src;?>','<?php echo $list -> gallery -> title;?>',0);
            <?php

            }

            ?>

            //tz_image('tz_image_gallery');

            // Attachments

            <?php
                if($this -> listAttach):
            ?>
                var _tz_portfolio_showAttachments = function(){
                    <?php
                        $i=0;
                        foreach($this -> listAttach as $row):
                    ?>
                        var myTr = new Element('tr',{
                        }).inject($('tz_attachments_body'));
                        var myTd = new Element('td',{
                            html:'<?php echo $row -> attachfiles;?>'
                        }).inject(myTr);
                        var myHidden = new Element('input',{
                            type:'hidden',
                            name:'tz_attachments_hidden_file[]',
                            value:'<?php echo $row -> attachfiles;?>'
                        }).inject(myTd);
                        var myTd = new Element('td',{
                            html:'<?php echo $row -> attachtitle;?>'
                        }).inject(myTr);
                        var myHidden = new Element('input',{
                            type:'hidden',
                            name:'tz_attachments_hidden_title[]',
                            value:'<?php if($row -> attachfiles != $row -> attachtitle) echo $row -> attachtitle;?>'
                        }).inject(myTd);
                        var myTd = new Element('td',{
                        }).inject(myTr);
                        var myInput = new Element('input',{
                            type:'button',
                            id:'tz_attachments_delete_<?php echo $i;?>',
                            value:'Delete'
                        }).inject(myTd);
                        $('tz_attachments_delete_<?php echo $i;?>').addEvent('click',function(){
                            var jSonRequest = new Request.JSON({url: "index.php?option=com_tz_portfolio&task=article.deleteAttachment",
                                onComplete: function(){
                                    window.location.reload();
                                },
                                data:{
                                    json: JSON.encode({
                                        'attachmentsFile':'<?php echo $row -> attachfiles;?>',
                                        'id':$('contentid').value,
                                        'attachmentsTitle':'<?php echo $row -> attachtitle;?>'
                                    })
                                }
                            }).send();
                        });
                    <?php
                            $i++;
                        endforeach;
                    ?>
                };
            _tz_portfolio_showAttachments();
            <?php
                endif;
            ?>


            var _tz_portfolio_addAttachments = function(){
        //        $('tz_attachments').empty();
                var myTable = new Element('table',{
                   id:'tz_attachments_table',
                    styles:{
                        width:'100%'
                    }
                }).inject($('tz_attachments'));

                var myTr0 = new Element('tr',{
                }).inject($('tz_attachments_table'));
                var myTd = new Element('td',{
                }).inject(myTr0);

                var myButton = new Element('button',{
                    html:'Add attachment field'
                }).inject(myTd);
                var myI = new Element('i',{
                   html:'(Max upload size: 100MB)'
                }).inject(myTd);

                myButton.addEvent('click',function(e){
                    e.stop();
                    var myTr1 = new Element('tr',{
                        styles:{
                            'margin-top':'10px'
                        }
                    }).inject($('tz_attachments_table'));

                    var myTd = new Element('td',{
                        html:'File attachments',
                        align:'right',
                        valign:'center',
                        styles:{
                            background: '#F6F6F6',
                            'min-width':'100px'
                        }
                    }).inject(myTr1);
                    var myTd = new Element('td',{
                        styles:{

                        }
                    }).inject(myTr1);

                    var myFile = new Element('input',{
                       type:'file',
                        name:'tz_attachments_file[]',
                        size:'60%'
                    }).inject(myTd);

                    var myTr2 = new Element('tr',{
                    }).inject($('tz_attachments_table'));
                    var myTd = new Element('td',{
                        align:'right',
                        valign:'center',
                        html:'Link title',
                        styles:{
                            background: '#F6F6F6',
                            'min-width':'100px'
                        }
                    }).inject(myTr2);
                    var myTd = new Element('td',{

                    }).inject(myTr2);

                    var myInput = new Element('input',{
                        type:'text',
                        name:'tz_attachments_title[]',
                        value:'',
                        size:'70%'
                    }).inject(myTd);
                    var myTr3 = new Element('tr',{
                    }).inject($('tz_attachments_table'));

                    var myTd = new Element('td',{
                       colspan:'2',
                        align:'right'
                    }).inject(myTr3);
                    var myRemove = new Element('input',{
                        type:'button',
                        value:'Remove',
                        events:{
                            click: function(e){
                                e.stop();
                                myTr1.dispose();
                                myTr2.dispose();
                                myTr3.dispose();
                            }
                        }
                    }).inject(myTd)


                });
            }
            _tz_portfolio_addAttachments();

        });
        </script>
        <div class="formelm" id="tz_fields_group">
            <label title="" class="hasTip required" for="jform_groupid" id="jform_groupid-lbl" aria-invalid="false">
                <?php echo JText::_('Fields Group')?>
            </label>
            <?php echo $this -> listsGroup;?>
        </div>
        <div class="formelm">
            <label title="" class="hasTip required" for="jform_type_of_media" id="jform_type_of_media-lbl" aria-invalid="false">
                <?php echo JText::_('Meida Type')?>
            </label>
            <select name="type_of_media">
                <option value="image"<?php if($type == 'image') echo ' selected="selected"';?>><?php echo JText::_('COM_TZ_PORTFOLIO_OPTION_IMAGE');?></option>
                <option value="imageGallery"<?php if($type == 'imagegallery') echo ' selected="selected"';?>>
                    <?php echo JText::_('COM_TZ_PORTFOLIO_OPTION_IMAGE_GALLERY');?>
                </option>
                <option value="video"<?php if($type == 'video') echo ' selected="selected"';?>>
                    <?php echo JText::_('COM_TZ_PORTFOLIO_OPTION_VIDEO');?>
                </option>
            </select>
        </div>
		<div class="formelm">
		<?php echo $this->form->getLabel('created_by_alias'); ?>
		<?php echo $this->form->getInput('created_by_alias'); ?>
		</div>

	<?php if ($this->item->params->get('access-change')): ?>
		<div class="formelm">
		<?php echo $this->form->getLabel('state'); ?>
		<?php echo $this->form->getInput('state'); ?>
		</div>

		<div class="formelm">
		<?php echo $this->form->getLabel('featured'); ?>
		<?php echo $this->form->getInput('featured'); ?>
		</div>

		<div class="formelm">
		<?php echo $this->form->getLabel('publish_up'); ?>
		<?php echo $this->form->getInput('publish_up'); ?>
		</div>
		<div class="formelm">
		<?php echo $this->form->getLabel('publish_down'); ?>
		<?php echo $this->form->getInput('publish_down'); ?>
		</div>

	<?php endif; ?>
		<div class="formelm">
		<?php echo $this->form->getLabel('access'); ?>
		<?php echo $this->form->getInput('access'); ?>
		</div>
		<?php if (is_null($this->item->id)):?>
			<div class="form-note">
			<p><?php echo JText::_('COM_CONTENT_ORDERING'); ?></p>
			</div>
		<?php endif; ?>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('JFIELD_LANGUAGE_LABEL'); ?></legend>
		<div class="formelm-area">
		<?php echo $this->form->getLabel('language'); ?>
		<?php echo $this->form->getInput('language'); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_CONTENT_METADATA'); ?></legend>
		<div class="formelm-area">
		<?php echo $this->form->getLabel('metadesc'); ?>
		<?php echo $this->form->getInput('metadesc'); ?>
		</div>
		<div class="formelm-area">
		<?php echo $this->form->getLabel('metakey'); ?>
		<?php echo $this->form->getInput('metakey'); ?>
		</div>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
		<?php if($this->params->get('enable_category', 0) == 1) :?>
		<input type="hidden" id="jform_catid" name="jform[catid]" value="<?php echo $this->params->get('catid', 1);?>"/>
		<?php endif;?>
		<?php echo JHtml::_( 'form.token' ); ?>
	</fieldset>
</form>
</div>