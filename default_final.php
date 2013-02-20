<?php
/**
 * $Id: default.php 113 2010-05-10 17:02:22Z kapsl $
 * @copyright (C) 2007 - 2009 Manuel Kaspar
 * @license GNU/GPL, see LICENSE.php in the installation package
 * This file is part of Event Table Edit
 *
 * Event Table Edit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * Event Table Edit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Event Table Edit. If not, see <http://www.gnu.org/licenses/>.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
global $compath, $Itemid, $number;

header("Expires: Mon, 10 Jan 1970 01:01:01 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Content-Type: text/html; charset=utf-8");
header("Pragma: no-cache");
?>

<!--Variable color of the rows-->
<style type="text/css">
.linecolor1 {
  background-color: #<?php echo $this->mySettings['linecolor1']; ?>;
}
.linecolor2 {
	background-color: #<?php echo $this->mySettings['linecolor2']; ?>;
}
.whoplaystable td, .whoplaystable th {
	padding: <?php echo $this->mySettings['cellpadding']; ?>px !important;
}
</style>

<div class="headline">
	<?php echo $this->mySettings['headline']; ?>
	
	<?php
	echo $this->mySettings['popHTML'];
	?>
</div>

<div class="pretext">
	<?php
	//Pretext
	echo $this->myHTML['pretext'];
	?>
</div>
<?php

/********************************* 
 * FILTER FORM 
 *********************************/
 
if ($this->mySettings['filter'] == 0) {
?>
	<form method="post" name="filterform" action="<?php echo $compath; ?>&amp;Itemid=<?php echo $Itemid; ?>&amp;choose=<?php echo $number; ?>&amp;filter=1">
	
		<br>
		<b><?php echo JText::_('Date'); ?></b>
		<input name="filternach" type="text" size="20" maxlength="100" value="<?php if($this->mySettings['filternach'] != '') { echo $this->mySettings['filternach']; } ?>" />
		
		<?php
		//if ($this->mySettings['fstring'] == 1) {
		//	echo JText::_('FILTERING_AFTER_1');
		//	echo '<b>"<span class="lightsearch">'. $this->mySettings['filternach'] . '</span>"</b>';
		//	echo JText::_('FILTERING_AFTER_2');
		//}
		//echo '&nbsp;';
		//echo JHTML::tooltip(JText::_('FILTER_TOOL_TIP'), JText::_('FILTER'), 'tooltip.png', '', '', false);
		?>
		
		<br>
		<b><?php echo JText::_('Event Name'); ?></b>
		<input name="event_filter" type="text" size="20" maxlength="100" value="<?php if($this->mySettings['event_filter'] != '') { echo $this->mySettings['event_filter']; } ?>" />
		
		<?php
		//if ($this->mySettings['fstring'] == 2) {
		//	echo JText::_('FILTERING_AFTER_1');
		//	echo '<b>"<span class="lightsearch">'. $this->mySettings['event_filter'] . '</span>"</b>';
		//	echo JText::_('FILTERING_AFTER_2');
		//}
		//echo '&nbsp;';
		//echo JHTML::tooltip(JText::_('FILTER_TOOL_TIP'), JText::_('FILTER'), 'tooltip.png', '', '', false);
		?>
		
		<br>
		<b><?php echo JText::_('Location'); ?></b>
		<input name="location_filter" type="text" size="20" maxlength="100" value="<?php if($this->mySettings['location_filter'] != '') { echo $this->mySettings['location_filter']; } ?>" />
		
		<?php
		//if ($this->mySettings['fstring'] == 4) {
		//	echo JText::_('FILTERING_AFTER_1');
		//	echo '<b>"<span class="lightsearch">'. $this->mySettings['city_filter'] . '</span>"</b>';
		//	echo JText::_('FILTERING_AFTER_2');
		//}
		//echo '&nbsp;';
		//echo JHTML::tooltip(JText::_('FILTER_TOOL_TIP'), JText::_('FILTER'), 'tooltip.png', '', '', false);
		?>
		
		<br>
		<b><?php echo JText::_('Key Word'); ?></b>
		<input name="keyword_filter" type="text" size="20" maxlength="100" value="<?php if($this->mySettings['keyword_filter'] != '') { echo $this->mySettings['keyword_filter']; } ?>" />
		
		<?php
		//if ($this->mySettings['fstring'] == 3) {
		//	echo JText::_('FILTERING_AFTER_1');
		//	echo '<b>"<span class="lightsearch">'. $this->mySettings['keyword_filter'] . '</span>"</b>';
		//	echo JText::_('FILTERING_AFTER_2');
		//}
		//echo '&nbsp;';
		//echo JHTML::tooltip(JText::_('FILTER_TOOL_TIP'), JText::_('FILTER'), 'tooltip.png', '', '', false);
		?>
		
		<div id="tablebutton">
			<!-- SEARCH BUTTON -->
			&nbsp; &nbsp; &nbsp;
			<a href="javascript:document.filterform.submit();" ><?php echo JText::_('Search'); ?></a>
			<!-- RESET BUTTON -->
			&nbsp;
			<a href="<?php echo $compath; ?>&amp;Itemid=<?php echo $Itemid; ?>&amp;choose=<?php echo $number; ?>&amp;resetyesno=yes"><?php echo JText::_('Reset Filters'); ?></a>
		</div>
		
	</form>
	
	<br>

<?php
}
?>

<form action="<?php echo $compath; ?>&amp;task=saveOrder&amp;Itemid=<?php echo $Itemid; ?>&amp;choose=<?php echo $number; ?>&amp;page=<?php echo $this->mySettings['page']; ?>" name="orderform" id="orderform" method="post">
<?php
if ($this->mySettings['menge'] != 0) {
?>
	<table width="100%" border="0" class="whoplaystable" id="whoplaystable" cellspacing="<?php echo $this->mySettings['cellspacing']; ?>">
		<!-- Tableheads -->
		<thead class="evthead">
		<?php
			//Optional up counting first row
			if ($this->mySettings['upcount'] == 0) {
				echo '<th class="evthupcount">#</th>';
			}
			
			$thcount = 0;
			$datatp = '';
   			foreach ($this->mySettings['rows2'] as $row2) {
				echo '<th class="evth' . $thcount . '"><span id="headSort">' . $this->mySettings['sortingArray'][$thcount] . '</span>' . $row2->thname . '</th>';
				if ($thcount != 0) {
					$datatp .= ';';
				}
				$datatp .= $this->mySettings['datatp'][$thcount];
				$thcount++;
			}
			if ($this->mySettings['acl_delete_reorder']) {
   				echo '<th class="thactions">' . JText::_('TH_ACTIONS') . '</th>';
   			}
		?>
		</thead>
		
		<!-- Content -->
		<?php
		$ic = $this->mySettings['menge'];		
		$oc = 0;
		$idarr = array();
		if (count ($this->mySettings['rows']) > 0) {
			foreach($this->mySettings['rows'] as $row) {
				//Get row ids for later processing
				$idarr[] = $row->id;
			
				$linecolor = 'linecolor';
				if (($oc % 2) == 0) {
					$linecolor = $linecolor . '1';
				}
				else {
					$linecolor = $linecolor . '2';
				}
				?>
				<tr class="<?php echo $linecolor; ?>">
				<?php 
				if ($this->mySettings['upcount'] == 0) {
					echo '<td class="evtdupcount' . $oc . '">' . ((($this->mySettings['page'] - 1) * $this->mySettings['limit']) + $oc + 1) . '</td>';
				}
				
				//See if a table cell is editable and make the cursor: pointer
				if ($this->mySettings['acl_edit'])
				    $cursp = "cursor: pointer; ";
				else
				    $cursp = "cursor: default; ";
				
				$ic = 0;
				$dar = explode(';', $datatp);
				foreach ($this->mySettings['rows2'] as $row2) {
					$cellcontent = $this->mySettings['cellcontent'][$oc][$ic];
					
					$centera = '';
					if ($dar[$ic] == "BOOLEAN")
						$centera = ' text-align: center;';
					?>
					<td style="<?php echo $cursp; ?> <?php echo $centera; ?>" class="evtd<?php echo $oc . $ic; ?>" id="evtd<?php echo $oc . $ic; ?>">
						<?php 
						
						
						if ($dar[$ic] == "BOOLEAN") {
						      if ($cellcontent != '' && $cellcontent != NULL && $cellcontent != -1) {
							    echo '<img src="';
							    
							    if ($cellcontent == 0) {
								  echo 'components/com_eventtableedit/template/images/cross.png';
							    }
							    else if ($cellcontent == 1) {
								  echo 'components/com_eventtableedit/template/images/tick.png';
							    }
							    echo '">';
						      }
						      
						}
						else {
						    echo trim($cellcontent); 
						}
						
						if (trim($cellcontent) == '')
						      echo '&nbsp;';
						?>
					</td>
					<?php
				$ic++;
				}		
				
				if ($this->mySettings['acl_delete_reorder']) {  
				?>
					<td class="evtdedit">
					<?php			 
					if ($this->mySettings['acl_delete_reorder']){
					?>
						<span id="jdelete" name="jdelete">
							<?php 
							echo JHTML::tooltip('', JTEXT::_('DELETE'), '../../../components/com_eventtableedit/template/images/publish_x.png', '', '', false);
							?>
						
						</span>
						
						<?php
						if ($this->mySettings['reorderflag'] == 0) {
						?>
							<input type="text" id="sortieren_<?php echo $oc; ?>" name="sortieren_<?php echo $oc; ?>" size="1" value="<?php echo $row->ordering; ?>" class="ordering">
						<?php
						}
					}
				}	
				?>
				</tr>
			<?php
			$oc++;
			}
		}
		?>
		
	</table>
 	<?php   
 	//Write the hidden fields that contain the id
	?>
    <span id="hidearea">
		<?php
        if (count($this->mySettings['rows']) > 0) {
            for ($j = 0; $j < count($idarr); $j++) {
            ?>
                <input type="hidden" class="hiddenrowe" name="rowedit_<?php echo $j; ?>" id="rowedit_<?php echo $j; ?>" value ="<?php echo $idarr[$j]; ?>">
            <?php
            }
        }
        ?>
    </span>
    <?php
}

//Pagebreak
if ($this->mySettings['limit'] != '') {
	echo $this->mySettings['fillpages'];
}
else {
echo '<br>';
}

//User Panel dependent on acl
$absbig = 0;
if ($this->mySettings['acl_new_row'] && $this->mySettings['menge'] != 0) {
$absbig = $this->mySettings['absbig'];
if ($absbig == '' || $absbig == NULL) {
	$absbig = 0;
}
?>
  	<div id="bnewrow" class="tablebutton2">
		<?php echo JText::_('NEW_ROW'); ?>
	</div>
<?php
}
if ($this->mySettings['acl_delete_reorder'] && $this->mySettings['menge'] != 0 && $this->mySettings['reorderflag'] == 0) {
?>
	&nbsp;
	<div class="tablebutton2">
		<a href="javascript:document.orderform.submit();" ><?php echo JText::_('SAVE_ORDER'); ?></a>
	</div>
<?php
}
if ($this->mySettings['acl_edit_table']) {
	if ($this->mySettings['menge'] == 0) {
?>
  		<br><br>
		<div class="tablebutton2">
			<a href="<?php echo $compath; ?>&amp;task=create&amp;rowedit=0&amp;biggestOrdering=<?php echo $this->mySettings['absbig']; ?>&amp;Itemid=<?php echo $Itemid; ?>&amp;choose=<?php echo $number; ?>"><?php echo JText::_('NEW_TABLE'); ?></a>
		</div>
		<br>
	<?php
  	}
  	else {
	?>
  		<br><br>
		<div class="tablebutton2">
			<a href="<?php echo $compath; ?>&amp;task=changeTable&amp;rowedit=0&amp;biggestOrdering=<?php echo $this->mySettings['absbig']; ?>&amp;Itemid=<?php echo $Itemid; ?>&amp;choose=<?php echo $number; ?>"><?php echo JText::_('EDIT_TABLE'); ?></a>
		</div>
		<br>
	<?php
	}
}

?>
<input type="hidden" name="datatparr" id="datatparr" value="<?php echo $datatp; ?>" />
</form>

<br><br>
<div class="aftertext">
	<?php
	//Aftertext
	echo $this->myHTML['aftertext'];
	?>
</div>
<br>

<?php
include_once('components/com_eventtableedit/includes/functions.php');
?>
