<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2012 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JHTML::_('behavior.framework');
JHTML::_('behavior.calendar');

// Filesize formatting function by eregon at msn dot com
// Published at: http://www.php.net/manual/en/function.number-format.php
function format_filesize($number, $decimals = 2, $force_unit = false, $dec_char = '.', $thousands_char = '')
{
	if($number <= 0) return '-';

	$units = array('b', 'Kb', 'Mb', 'Gb', 'Tb');
	if($force_unit === false)
	$unit = floor(log($number, 2) / 10);
	else
	$unit = $force_unit;
	if($unit == 0)
	$decimals = 0;
	return number_format($number / pow(1024, $unit), $decimals, $dec_char, $thousands_char).' '.$units[$unit];
}

// Load a mapping of backup types to textual representation
$scripting = AEUtilScripting::loadScripting();
$backup_types = array();
foreach($scripting['scripts'] as $key => $data)
{
	$backup_types[$key] = JText::_($data['text']);
}

?>

<div class="alert alert-info">
	<button class="close" data-dismiss="alert">×</button>
	<h4 class="alert-heading"><?php echo JText::_('BUADMIN_LABEL_HOWDOIRESTORE_LEGEND')?></h4>
	<p><?php echo JText::sprintf('BUADMIN_LABEL_HOWDOIRESTORE_TEXT','https://www.akeebabackup.com/documentation/quick-start-guide/restoring-backups.html','https://www.akeebabackup.com/documentation/video-tutorials/item/1024-ab04.html') ?></p>
</div>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" id="option" value="com_akeeba" />
	<input type="hidden" name="view" id="view" value="buadmin" />
	<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
	<input type="hidden" name="task" id="task" value="default" />
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />	
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken()?>" value="1" />
<table class="table table-striped">
	<thead>
		<tr>
			<th width="20"><input type="checkbox" name="toggle" value=""
				onclick="Joomla.checkAll(this);" /></th>
			<th width="20">
				<?php echo JHTML::_('grid.sort', 'STATS_LABEL_ID', 'id', $this->lists->order_Dir, $this->lists->order, 'default'); ?>
			</th>
			<th width="240">
				<?php echo JHTML::_('grid.sort', 'STATS_LABEL_DESCRIPTION', 'description', $this->lists->order_Dir, $this->lists->order, 'default'); ?>
			</th>
			<th width="80">
				<?php echo JHTML::_('grid.sort', 'STATS_LABEL_START', 'backupstart', $this->lists->order_Dir, $this->lists->order, 'default'); ?>
			</th>
			<th width="80">
				<?php echo JText::_('STATS_LABEL_DURATION'); ?>
			</th>
			<th width="80">
				<?php echo JText::_('STATS_LABEL_STATUS'); ?>
			</th>
			<th width="80">
				<?php echo JHTML::_('grid.sort', 'STATS_LABEL_ORIGIN', 'origin', $this->lists->order_Dir, $this->lists->order, 'default'); ?>
			</th>
			<th width="80">
				<?php echo JHTML::_('grid.sort', 'STATS_LABEL_TYPE', 'type', $this->lists->order_Dir, $this->lists->order, 'default'); ?>
			</th>
			<th width="20">
				<?php echo JHTML::_('grid.sort', 'STATS_LABEL_PROFILEID', 'profile_id', $this->lists->order_Dir, $this->lists->order, 'default'); ?>
			</th>
			<th width="80">
				<?php echo JText::_('STATS_LABEL_SIZE'); ?>
			</th>
			<th><?php echo JText::_('STATS_LABEL_MANAGEANDDL'); ?></th>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td class="form-inline">
				<input type="text" name="description" id="description"
					value="<?php echo $this->escape($this->lists->fltDescription) ?>"
					class="text_area input-medium" onchange="document.adminForm.submit();" />
				<button class="btn btn-mini" onclick="this.form.submit(); return false;"><?php echo JText::_('Go'); ?></button>
				<button class="btn btn-mini" onclick="document.adminForm.description.value='';this.form.submit(); return;"><?php echo JText::_('Reset'); ?></button>
			</td>
			<td colspan="2">
				<?php echo JHTML::_('calendar', $this->lists->fltFrom, 'from', 'from', '%Y-%m-%d', array('class' => 'input-mini')); ?> &mdash;
				<?php echo JHTML::_('calendar', $this->lists->fltTo, 'to', 'to', '%Y-%m-%d', array('class' => 'input-mini')); ?>
				<button class="btn btn-mini " onclick="this.form.submit(); return false;"><?php echo JText::_('Go'); ?></button>
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td colspan="2"></td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
	</tfoot>
	<tbody>
	<?php if(!empty($this->list)): ?>
	<?php $id = 1; $i = 0;?>
	<?php foreach($this->list as $record): ?>
	<?php
	$id = 1 - $id;
	$check = JHTML::_('grid.id', ++$i, $record['id']);

	$origin_lbl = 'STATS_LABEL_ORIGIN_'.strtoupper($record['origin']);
	$origin = JText::_($origin_lbl);
	/*
	if($origin == $origin_lbl)
	{
		$origin = '&ndash;';
	}
	/**/

	if( array_key_exists($record['type'], $backup_types) )
	{
		$type = $backup_types[$record['type']];
	}
	else
	{
		$type = '&ndash;';
	}

	jimport('joomla.utilities.date');
	$startTime = new JDate($record['backupstart']);
	$endTime = new JDate($record['backupend']);

	$duration = $endTime->toUnix() - $startTime->toUnix();
	if($duration > 0)
	{
		$seconds = $duration % 60;
		$duration = $duration - $seconds;

		$minutes = ($duration % 3600) / 60;
		$duration = $duration - $minutes * 60;

		$hours = $duration / 3600;
		$duration = sprintf('%02d',$hours).':'.sprintf('%02d',$minutes).':'.sprintf('%02d',$seconds);
	}
	else
	{
		$duration = '-';
	}
	/*
	$user = JFactory::getUser();
	$userTZ = $user->getParam('timezone',0);
	$startTime->setOffset($userTZ);
	*/

	$filename_col = '';
	
	if($record['remote_filename'] && (AKEEBA_PRO == 1) ) {
		// If we have a remote filename we allow for remote file management in the Pro release
		$remotemgmttext = JText::_('STATS_LABEL_REMOTEFILEMGMT');
		$filename_col = <<<ENDHTML
<a
	class="modal akeeba_remote_management_link btn btn-mini"
	href="index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id={$record['id']}";
	rel="{handler: 'iframe', size: {x: 450, y: 280}, onClose: function(){window.location='index.php?option=com_akeeba&view=buadmin'}}"
>&raquo; $remotemgmttext &laquo;</a>
ENDHTML;
		if($record['meta'] != 'obsolete') {
			$filename_col .= '<hr/>'.JText::_('REMOTEFILES_LBL_LOCALFILEHEADER');
		}
	} elseif(empty($record['remote_filename']) && ($this->enginesPerProfile[$record['profile_id']] != 'none') && ($record['meta'] != 'obsolete') ) {
		$postProcEngine = $this->enginesPerProfile[$record['profile_id']];
		if(!empty($postProcEngine)) {
			$filename_col .= '<a '
				.'class="modal akeeba_upload" '
				.'href="index.php?option=com_akeeba&view=upload&tmpl=component&task=start&id='.$record['id'].'" '
				.'rel="{handler: \'iframe\', size: {x: 350, y: 200}, onClose: function(){window.location=\'index.php?option=com_akeeba&view=buadmin\'}}" '
				.'title="'.JText::sprintf('AKEEBA_TRANSFER_DESC', JText::_("ENGINE_POSTPROC_{$postProcEngine}_TITLE")).'">'.
				JText::_('AKEEBA_TRANSFER_TITLE').' (<em>'.$postProcEngine.'</em>)'.
				'</a>';
			$filename_col .= '<hr/>'.JText::_('REMOTEFILES_LBL_LOCALFILEHEADER');
		}
	}
	
	if($record['meta'] == 'ok')
	{
		// Get the download links for downloads for completed, valid backups
		$thisPart = '';
		$thisID = urlencode($record['id']);
		$filename_col .= $record['archivename']."<br/>";
		if($record['multipart'] == 0)
		{
			// Single part file -- Create a simple link
			$filename_col .= "<a class=\"btn btn-mini\" href=\"javascript:confirmDownload('$thisID', '$thisPart');\"><i class=\"icon-download-alt\"></i>".JText::_('STATS_LOG_DOWNLOAD')."</a>";
		}
		else
		{
			for($count = 0; $count < $record['multipart']; $count++)
			{
				$thisPart = urlencode($count);
				$label = JText::sprintf('STATS_LABEL_PART', $count);
				$filename_col .= ($count > 0) ? ' &bull; ' : '';
				$filename_col .= "<a class=\"btn btn-mini\" href=\"javascript:confirmDownload('$thisID', '$thisPart');\"><i class=\"icon-download-alt\"></i>$label</a>";
			}
		}
	}
	else
	{
		// If the backup is not complete, just show dashes
		if(empty($filename_col)) {
			$filename_col .= '&mdash;';
		}
	}

	// Link for Show Comments lightbox
	$info_link = "";
	if(!empty($record['comment']))
	{
		$info_link = JHTML::_('tooltip', strip_tags($this->escape($record['comment'])) ) . '&ensp;';
	}
	
	// Label class based on status
	$status = JText::_('STATS_LABEL_STATUS_'.$record['meta']);
	$statusClass = '';
	switch($record['meta']) {
		case 'ok':
			$statusClass = 'label-success';
			break;
		case 'pending':
			$statusClass = 'label-warning';
			break;
		case 'fail':
			$statusClass = 'label-important';
			break;
		case 'remote':
			$statusClass = 'label-info';
			break;
	}

	$edit_link = JURI::base() . 'index.php?option=com_akeeba&view=buadmin&task=showcomment&id='.$record['id'];

	if(empty($record['description'])) $record['description'] = JText::_('STATS_LABEL_NODESCRIPTION');
	?>
		<tr class="row<?php echo $id; ?>">
			<td><?php echo $check; ?></td>
			<td>
				<?php echo $record['id']; ?>
			</td>
			<td>
				<?php echo $info_link ?>
				<a href="<?php echo $edit_link; ?>"><?php echo $this->escape($record['description']) ?></a>
			</td>
			<td>
				<?php echo $startTime->format(JText::_('DATE_FORMAT_LC4'), true); ?>
			</td>
			<td><?php echo $duration; ?></td>
			<td>
				<span class="label <?php echo $statusClass; ?>">
					<?php echo $status ?>
				</span>
			</td>
			<td><?php echo $origin ?></td>
			<td><?php echo $type ?></td>
			<td><?php echo $record['profile_id'] ?></td>
			<td><?php echo ($record['meta'] == 'ok') ? format_filesize($record['size']) : ($record['total_size'] > 0 ? "(<i>".format_filesize($record['total_size'])."</i>)" : '&mdash;') ?></td>
			<td><?php echo $filename_col; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
</form>