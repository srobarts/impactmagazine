<?php
/**
 * $Id: view.html.php 113 2010-05-10 17:02:22Z kapsl $
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
jimport( 'joomla.application.component.view');

global $mainframe;
$document =& JFactory::getDocument();

$cssHTML = '<link href="components/com_eventtableedit/template/css/eventtablecss.css" rel="stylesheet" type="text/css" />' . "\n";
$adddrag = '<script language="JavaScript" src="components/com_eventtableedit/includes/drag.js" type="text/javascript"></script>' . "\n";

$document->addCustomTag($cssHTML);
$document->addCustomTag($adddrag);

class eventViewdefault extends JView {
  function display($tpl = null) {
		global $number, $compath, $parsebbcode;
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.calendar');
		
		$mySettings = JRequest::getVar('mySettings');
		
		addbbcode($mySettings['bbimg']);
			
		$mySettings['filternach'] = JRequest::getVar('filternach', '');
		//echo "filternach: " . $mySettings['filternach'];
		
		$mySettings['event_filter'] = JRequest::getVar('event_filter', '');
		//echo "event_filter: " . $mySettings['event_filter'];
		
		$mySettings['keyword_filter'] = JRequest::getVar('keyword_filter', '');
		//echo "keyword_filter: " . $mySettings['keyword_filter'];
		
		$mySettings['location_filter'] = JRequest::getVar('location_filter', '');
		//echo "city_filter: " . $mySettings['city_filter'];

		$mySettings['sortingArray'] = $this->dynamicSorting($mySettings['limit'], $mySettings['filternach'], $mySettings['rows2'], $mySettings['popup']);
								
		$fflag = 0;
		if ($mySettings['filter'] == 0) {
			$fflag = 1;
			$mySettings['fstring'] = 0;
			if ($mySettings['filternach'] != '') {
				$mySettings['fstring'] = 1;
			}
			if ($mySettings['event_filter'] != '') {
				$mySettings['fstring'] = 2;
			}
			if ($mySettings['keyword_filter'] != '') {
				$mySettings['fstring'] = 3;
			}
			if ($mySettings['location_filter'] != '') {
				$mySettings['fstring'] = 4;
			}


			
		}	
		
	   //content editing
	   if (count($mySettings['rows']) > 0) {
		   $oc = 0;
		   foreach ($mySettings['rows'] as $row) {
				$ic = 0;
				foreach ($mySettings['rows2'] as $row2) {
					$res = $mySettings['realname'][$ic];
					$cctemp = $row->$res;
					
					//Translating mySQL Date to German Date
					if ($mySettings['date_switch'][$ic] == 'yes') {
						$cctemp = date_mysql_to_german($cctemp, $mySettings['date_format']);
					}
					else if ($mySettings['time_switch'][$ic] == 'yes') {
						$cctemp = format_time($cctemp, $mySettings['time_format']);
					}

					//BBCODE Parsing
					$np = $cctemp;
					if ($mySettings['bbcode'] == 0) {
						$cctemp = $parsebbcode->parse ($cctemp);
						$cctemp = str_replace('[br]','<br>', $cctemp);
					}
					//Marking the founded search strings
					if ($mySettings['filternach'] != '' && $mySettings['popup'] != 1) {
						$cctemp = eregi_replace($mySettings['filternach'], '<span class="lightsearch">' . $mySettings['filternach'] . '</span>', $cctemp);
					}
					$mySettings['cellcontent'][$oc][$ic] = htmlspecialchars_decode($cctemp, ENT_NOQUOTES);
					$mySettings['notparsed'][$oc][$ic] = $np;
					$ic++;
				}
			$oc++;
		   }
	    }
	   	  		
		if ($mySettings['limit'] != '') {
			$mySettings['fillpages'] = $this->fillPages($mySettings);
		}
		
		if ($mySettings['print'] == 0) {
			$mySettings['popHTML'] = $this->printView();
		}
		else {
			$tmp = '';
			$mySettings['popHTML'] = $tmp;
		}
		
		$this->assignRef('myHTML', JRequest::getVar('myHTML', '', '', 'array', JREQUEST_ALLOWRAW));
		$this->assignRef('mySettings', $mySettings);
		
		if ($mySettings['popup'] == 1) {
			parent::display('print');	
		}
		else {
		    parent::display($tpl);
		}
    }
	
	function fillPages($mySettings) {
	global $Itemid, $compath;
	$nav = '';
	$nav .=	'<div class="cpages" align="center">';
			
	
			
		if ($mySettings['page'] != 1) {	
			$nav .= $this->build_a($mySettings, 1, JText::_('START'));
		}
		else {
			$nav .= '<< ' . JText::_('START') . ' ';
		}
		
		if (($mySettings['page'] - 1) > 0) {
			$nav .= $this->build_a($mySettings, $mySettings['page'] - 1, JText::_('EBACK'));
		}
		else {
			$nav .= '< ' . JText::_('EBACK') . ' ';
		}
		
		$nav .= $this->getHTML_String($mySettings);
		
		if (($mySettings['page'] + 1) <= $mySettings['max']) {
			$nav .= $this->build_a($mySettings, $mySettings['page'] + 1, JText::_('ENEXT'));
		}
		else {
			$nav .= JText::_('ENEXT') . ' > ';
		}
		
		if ($mySettings['page'] != $mySettings['max'] && $mySettings['max'] != 0) {
			$nav .= $this->build_a($mySettings, $mySettings['max'], JText::_('END'));
		}
		else {
			$nav .= JText::_('END') . ' >>';
		}
	$nav .= '</div>';
	
	return $nav;	
	}
	
	function build_a($mySettings, $page, $node) {
		global $Itemid, $compath;
		
		$ret = '<a href="' . $compath . '&amp;Itemid=' . $Itemid 
				   . '&amp;limit=' . $mySettings['limit']
				   . '&amp;filternach=' . $mySettings['filternach'] 
				   . '&amp;sortedAfter=' . $mySettings['sortedAfter'] 
				   . '&amp;sortingDirection=' 
				   . $mySettings['sortingDirection'];
		$ret .= '&amp;page=' . $page;
		$ret .= '"> ' . $node . '</a>';		   
				   
		return $ret;
	}
	
	function getHTML_String($mySettings) {
	global $compath, $Itemid;
		//Calculate direct select numbers
		$html_string = '';
		for ($a = $mySettings['page'] - 4; $a <= $mySettings['page'] + 4; $a++) {
			if ($a <= 0) {
				continue;
			}
			else if ($a > $mySettings['max']) {
				break;
			}
			else if ($a == $mySettings['page']) {
				$html_string .= ' ' . $a . ' ';
			}
			else {
				$html_string .= $this->build_a($mySettings, $a, $a);
			}

		}
		return $html_string;
	}
	
	function printView() {
		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
		$output	= '<a href="'. JRoute::_('index.php?popup=1&tmpl=component') .'" onclick="window.open(this.href,\'win2\',\''.$status.'\'); return false;"><img alt="Printview" src="images/M_images/printButton.png" border="0" /></a>';
		
		return $output;
	}
	
	/**
	 * Things for creating the dynamic sorting of the table
	 */
	function dynamicSorting($limit, $filternach, $rows2, $popup) {
		global $compath, $Itemid;
		
		$sortingDirection = JRequest::getInt('sortingDirection', 0);
		$sortedAfter = JRequest::getInt('sortedAfter', -1);
		$nrCols = count($rows2);
		
		$sortingArray = array();
		$sort = 0;
	
		for ($a = 0; $a < $nrCols; $a++) {
			$img = 'sort.png';

	
			if ($a == $sortedAfter) {
				$sort = !$sortingDirection;
	
				if ($sortingDirection == 0) {
					$img = 'sortup.png';
				}
				else {
					$img = 'sortdown.png';
				}
			}
	
			$pop = '';
			if ($popup == 1) {
				$pop = '&amp;popup=1&amp;tmpl=component';
			}
	
			$sortingArray[$a] = "<a href='$compath&amp;Itemid=$Itemid&amp;sortedAfter=$a&amp;sortingDirection=$sort&amp;limit=$limit&amp;page=1&amp;filternach=$filternach$pop'>";
			$sortingArray[$a] .= JHTML::_('image.site', $img, 'components/com_eventtableedit/template/images/', NULL, NULL, JText::_( 'Ordering' ));
			$sortingArray[$a] .= "</a>&nbsp;";
	
			$sort = 0;
		}
		
		return $sortingArray;
	}
}
?>
