<?php
/**
 * $Id: controller.php 113 2010-05-10 17:02:22Z kapsl $
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

jimport('joomla.application.component.controller');

global $number, $filter, $compath, $mainframe, $Itemid;

$compath =  'index.php?option=com_eventtableedit';

$number = JRequest::getVar('choose', '-1', 'get');
$filter = JRequest::getVar('filter', '-1', 'get');

if ($number == -1) {
  $params = $mainframe->getParams('com_eventtableedit');
	$number = $params->get('choose', 1);
}

class eventController extends JController
{
	
    function __construct() {
		parent::__construct();
		
		$this->registerTask('create', 'create');
		$this->registerTask('saveTable', 'saveTable');
		$this->registerTask('deleteExe', 'deleteExe');
		$this->registerTask('saveOrder', 'saveOrder');
		$this->registerTask('changeTable', 'changeTable');
		$this->registerTask('cTadd', 'cTadd');
		$this->registerTask('cTdelete', 'cTdelete');
		$this->registerTask('cTrename', 'cTrename');
		$this->registerTask('cTchangeType', 'cTchangeType');
		$this->registerTask('cTordering', 'cTordering');
		$this->registerTask('cTautoSort', 'cTautoSort');
		$this->registerTask('cTaddExe', 'cTaddExe');
		$this->registerTask('cTdeleteExe', 'cTdeleteExe');
		$this->registerTask('cTrenameExe', 'cTrenameExe');
		$this->registerTask('cTchangeTypeExe', 'cTchangeTypeExe');
		$this->registerTask('cTorderingExe', 'cTorderingExe');
		$this->registerTask('cTautoSortExe', 'cTautoSortExe');
		$this->registerTask('ajaxsave', 'ajaxsave');
		$this->registerTask('ajaxgetfield', 'ajaxgetfield');
		$this->registerTask('ajaxnewrow', 'ajaxnewrow');
	}
	
	function display() {
		global $number, $filter, $Itemid;
		$mySettings = array();
		
		JRequest::setVar( 'view', 'default' );
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		
		//Check View ACL
		$this->aclExe('view');
		
		$mySettings['menge'] = $model->getColNumb('order'); 
		$mySettings['rows2'] = $model->getHeads();
		
		//Things for the pagebreak
		$mySettings['page'] = JRequest::getVar('page', '1', 'get');
		$mySettings['limit'] = $model->getConfig('sitebreak', $number);
		
		//Get url for print function
		$mySettings['popup'] = JRequest::getVar('popup', 0, 'get');
		if ($mySettings['popup'] == 1) {
				$mySettings['limit'] = '';
		}
		
		$table = getTableName();
		$table2 = getTableOrder();
		
		checkTable($model);
		
		$ordering = $model->getConfig('sort', $number);
		$mySettings['reorderflag'] = 1;
		if ($ordering == '') {
			$ordering = 'ordering';
			$mySettings['reorderflag'] = 0;
		}
		
		if (($tord = $this->dynamicSorting($table2, $model, $mySettings)) != false) {
			$ordering = $tord;
		}
		
		$mySettings['filter'] = (int) $model->getConfig('filter', $number); //gets some value from configuration table- the filter?
			//we hand it the phrase 'filter' + $number. Model looks at configname=filter, confignumber=$number
			//result is cast and (int) and placed in array['filter']
				
		$query = '';
		if ($mySettings['filter'] == 0) {
				$query = filter($mySettings['rows2'], $mySettings['menge'], $model); //sets up query, uses filter function in helper
		}
				
		if ($query == '') {
			$query = 'SELECT * FROM ' . $table . ' ORDER BY ' . $ordering; //if no previous set query return all results
		}
	   
	    $rows = $model->dbExe($query);
	    $maxtotal = count($rows);

	    $queryext = '';
	    $mySettings['max'] = NULL;

		//Make the maximum number of lines 300
		define('_MAX_LINE_NUMBER', 300);

		if ($maxtotal > _MAX_LINE_NUMBER && $mySettings['limit'] == '') {
			$mySettings['limit'] = _MAX_LINE_NUMBER;
		}

		if ($mySettings['limit'] != '') {
			$firstlimit = ($mySettings['page'] - 1) * $mySettings['limit'];
	
			$mySettings['max'] = ceil($maxtotal / $mySettings['limit']);
			$queryext = ' LIMIT ' . $firstlimit . ', ' . $mySettings['limit'];
		}
	
		$query .= $queryext;
		$mySettings['rows'] = $model->dbExe($query);
		//echo $query;
	   
	   $counter = 0;
	   $mySettings['realname'] = NULL;
	   $mySettings['date_switch'] = NULL;
	   $mySettings['time_switch'] = NULL;
	   $mySettings['datatp'] = NULL;
      
	   foreach ($mySettings['rows2'] as $row2) {
			$mySettings['realname'][] = $row2->realname;			
			$mySettings['date_switch'][$counter] = 'no';
			$mySettings['time_switch'][$counter] = 'no';
			$mySettings['datatp'][$counter] = $row2->datatp;
			
			switch ($row2->datatp) {
				case 'DATE':
					$mySettings['date_switch'][$counter] = 'yes';
					break;
				case 'TIME':
					$mySettings['time_switch'][$counter] = 'yes';
					break;
			}
			
			$counter++;
	    } 
		
		  //Dropdown
		  $dropdownindex = explode(';', $model->getConfig('dropdownindex', -999));
		  
		  if (count($dropdownindex) > 0) {
			for ($r = 0; $r < count($dropdownindex); $r++) {
			      $mySettings['drpoints'][] = explode(';', $model->getConfig('dropdown_' . $dropdownindex[$r], -999));
			}
		  }
		
		//Finding out biggest ordering
		$query = "SELECT ordering FROM $table ORDER BY ordering DESC LIMIT 1";
		$mySettings['absbig'] = $model->dbExeRes($query);
				
		//Pre- and Aftertext has to be handled differently
		$myHTML['pretext'] = $model->getConfig('pretext', $number);
		$myHTML['aftertext'] = $model->getConfig('aftertext', $number);
		
		JRequest::setVar('myHTML', $myHTML);
		
		$this->loadConfig($mySettings);
		JRequest::setVar('mySettings', $mySettings);
		
		parent::display();
	}
	
	function loadConfig(&$mySettings) {
		global $number;
		$model =& $this->getModel('eventtableedit');
		
		$mySettings['bbcode'] = (int) $model->getConfig('bbcode', $number);
		$mySettings['bbimg'] = (int) $model->getConfig('bbimg', $number);
		$mySettings['headline'] = $model->getConfig('headline', $number);
		$mySettings['linecolor1'] = $model->getConfig('linecolor1', $number);
		$mySettings['linecolor2'] = $model->getConfig('linecolor2', $number);
		$mySettings['cellspacing'] = $model->getConfig('cellspacing', $number);
		$mySettings['cellpadding'] = $model->getConfig('cellpadding', $number);
		$mySettings['print'] = (int) $model->getConfig('print', $number);
		$mySettings['upcount'] = (int) $model->getConfig('upcount', $number);
		$mySettings['acl_view'] = $this->acl('view');
		$mySettings['acl_edit'] = $this->acl('edit');
		$mySettings['acl_delete_reorder'] = $this->acl('delete_reorder');
		$mySettings['acl_new_row'] = $this->acl('new_row');
		$mySettings['acl_edit_table'] = $this->acl('edit_table');
		$mySettings['date_format'] = $model->getConfig('date_format', $number);
		$mySettings['time_format'] = $model->getConfig('time_format', $number);
	}
	
	/**
	 * Dynamic ordering
	 */
	function dynamicSorting($table2, $model, &$mySettings) {
		$mySettings['sortingDirection'] = JRequest::getInt('sortingDirection', 0, 'get');
		$mySettings['sortedAfter'] = JRequest::getInt('sortedAfter', -1, 'get');
		
		$dir = "DESC";
		if ($mySettings['sortingDirection'] == 1) {
			$dir = "ASC";
		}
		
		if ($mySettings['sortedAfter'] == -1) {
			return false;
		}
		
		// Get name of sorting line
		$query = "SELECT realname FROM $table2 " .
				"ORDER BY ordering ASC " .
				"LIMIT " . $mySettings['sortedAfter'] . ", 1";
		$nameQuery = $model->dbExeRes($query);
				
		return $nameQuery . " " . $dir;
	}
	
	//User Interface to create a new table
	function create() {
		global $number, $Itemid, $mainframe, $compath;
		$model =& $this->getModel('eventtableedit');
		JRequest::setVar('view', 'create');
		include_once ('includes/helper.php');
						
		$this->aclExe('edit_table');
		
		//Prepare the dropdowns
		$this->getDropdowns($model);
				
		parent::display();
	}
	
	//Creates the new table
	function saveTable() {
		global $number, $Itemid, $mainframe, $compath;
		include_once ('includes/helper.php');
		$model =& $this->getModel('eventtableedit');
				
		$this->aclExe('edit_table');
		
		$table = getTableName();
		$table2 = getTableOrder();
				
		$tablehead = JRequest::getVar('tablehead0', '', 'post');
		$datatype = JRequest::getVar('datatype0', '', 'post');
		
		$tps = array('TEXT', 'INT', 'FLOAT', 'DATE', 'TIME');
		
		$a = 0;
		while ($tablehead != NULL){ 
			//Creating a unique name
			$rows2 = $model->getHeads();
			$nametablehead = getNextRL($rows2);
			
			//For the Dropdowns
			$myflag = false;
			for ($g = 0; $g < count($tps); $g++) {
				if ($datatype == $tps[$g]) {
					$myflag = true;
					break;
				}
			}
			$datatype2 = $datatype;
			if (!$myflag) {
				$datatype = 'TEXT';
			}
			
			$query = 'ALTER TABLE ' . $table . ' ADD ' . $nametablehead . ' ' . $datatype;
			
			$model->dbFExe($query);
			
			$tablehead = mysql_escape_string($tablehead);
			
			$query = 'INSERT INTO ' . $table2 . '(thname, ordering, realname, datatp) VALUES (\'' . $tablehead . '\', \'' . $a . '\', \''
					 . $nametablehead . '\', \'' . $datatype2 . '\')';
			$model->dbFExe($query);

			$a++;
			$tablehead = JRequest::getVar('tablehead' . $a, '', 'post');
			$datatype = JRequest::getVar('datatype' . $a, '', 'post');
		}
				
		$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('ACTION_SUCCESSFUL'));
	}
	
	//Deleting a row
	function deleteExe() {
		global $number, $Itemid, $mainframe, $compath;
		include_once ('includes/helper.php');
		$model =& $this->getModel('eventtableedit');
		$number = JRequest::getVar('number', '', 'post');
		$this->aclExe('delete_reorder');
		
		$dbprefix = $mainframe->getCfg('dbprefix');
		
		$id = (int) JRequest::getVar('id', '', 'get');
		$number = JRequest::getVar('number', 0, 'get');
		
		$query = "DELETE FROM " . $dbprefix . "event_table_edit_$number WHERE id = $id";
		$model->dbFExe($query);
		
		//Finding out biggest ordering
		$query = "SELECT ordering FROM " . $dbprefix . "event_table_edit_$number ORDER BY ordering DESC LIMIT 1";
		$res = $model->dbExeRes($query);
		
		if ($res == '') {
			$res = 0;
		}
		echo $res;
		
		exit;
	}
	
	//Saving the order as it is defined in the inputfields behind the rows
	function saveOrder() {
		global $number, $Itemid, $mainframe, $compath;
		include_once ('includes/helper.php');
		$model =& $this->getModel('eventtableedit');
				
		$this->aclExe('delete_reorder');
		
		$a = 0;
		do {
			$sortieren = JRequest::getVar('sortieren_' . $a, '', 'post');
			$rowedit = JRequest::getVar('rowedit_' . $a, '', 'post');
				
			$query = 'UPDATE ' . getTableName() . ' SET ordering = \'' . $sortieren . '\' WHERE id = ' . $rowedit;
			$model->dbFExe($query);
			$a++;
		} while ($rowedit != NULL);
		
		$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('SUCCESSFUL_REORDER'));
	}
	
	//Displays overview over functions that change the table structure
	function changeTable() {
		global $number, $Itemid, $mainframe, $compath;
		$model =& $this->getModel('eventtableedit');
		JRequest::setVar('view', 'changeTable');
		include_once ('includes/helper.php');
		
		$this->aclExe('edit_table');
		
		parent::display();
	}
	
	function cTadd() {
		global $number, $Itemid, $mainframe, $compath;
		JRequest::setVar('view', 'changeTable');
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$rows2 = $model->getHeads();
		$menge = $model->getColNumb('order');
		
		
		//Dropdowns
		$dropdownindex = $model->getConfig('dropdownindex', -999);
		$ddiarray = explode(';', $dropdownindex);
		
		$drconfig = array();
		$dcount = count($ddiarray);
		if ($dcount == 1 && $ddiarray[0] == '') {
			$dcount = 0;
		}
		
		for ($a = 0; $a < $dcount; $a++) {
			$head = explode(';', $model->getConfig('dropdown_' . $ddiarray[$a], -999));
			$drconfig[$a] = $head[0];
		}
				
		foreach ($rows2 as $row2) {
			$thname[] = $row2->thname;
			$ordering[] = $row2->ordering;
		}
				
		JRequest::setVar('menge', $menge);
		JRequest::setVar('thname', $thname);
		JRequest::setVar('ordering', $ordering);
		JRequest::setVar('cTtask', 'add');
		JRequest::setVar('drconfig', $drconfig);
		JRequest::setVar('ddiarray', $ddiarray);
		JRequest::setVar('dcount', $dcount);
				
		parent::display();
	}
	
	function cTaddExe() {
		global $number, $Itemid, $mainframe, $compath;
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$table = getTableName();
		$table2 = getTableOrder();
		
		$menge = $model->getColNumb('order');
		$coledit = JRequest::getVar('ordering', '', 'post');
		$colnmb = $coledit;
		$add = JRequest::getVar('add', '', 'post');
		$datatype = JRequest::getVar('datatype', '', 'post');
		$max = (int) JRequest::getVar('max', '', 'get');
		$mengeEdit = $model->getColNumb('edit');
		$rows2 = $model->getHeads();
		
		$nametablehead = getNextRL($rows2);
		
		if ($coledit == '') {
			$colnmb = $max;
		}
		
		//For the Dropdowns
		$tps = array('TEXT', 'INT', 'FLOAT', 'DATE', 'TIME');
		$myflag = false;
		for ($g = 0; $g < count($tps); $g++) {
			if ($datatype == $tps[$g]) {
				$myflag = true;
				break;
			}
		}
		$datatype2 = $datatype;
		if (!$myflag) {
			$datatype = 'TEXT';
		}
		
		$query = 'ALTER TABLE ' . $table . ' ADD ' . $nametablehead . ' ' . $datatype . ' default NULL';
		$model->dbFExe($query);
			
		for ($a = $menge; $a >= $colnmb + 1; $a--) {
			$b = $a + 1;
			
			$query = 'UPDATE ' . $table2 . ' SET ordering = \'' . $b . '\' WHERE ordering = \'' . $a . '\'';
			$model->dbFExe($query);
		}
		
		$colnmb = $colnmb + 1;
		$add = mysql_escape_string($add);
		$query = 'INSERT INTO ' . $table2 . ' (thname, ordering, realname, datatp) VALUES (\'' . $add . '\', \'' . $colnmb . '\', \'' . $nametablehead .
				 '\', \'' . $datatype2 . '\')';	
		$model->dbFExe($query);
		
		$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('ACTION_SUCCESSFUL'));
	}
	
	function cTdelete() {
		global $number, $Itemid, $mainframe, $compath;
		JRequest::setVar('view', 'changeTable');
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$rows2 = $model->getHeads();
		
		foreach ($rows2 as $row2) {
			$thname[] = $row2->thname;
			$realname[] = $row2->realname;
		}
		
		$menge = $model->getColNumb('order');
		JRequest::setVar('cTtask', 'delete');
		JRequest::setVar('menge', $menge);
		JRequest::setVar('thname', $thname);
		JRequest::setVar('realname', $realname);
		
		parent::display();
	}
	
	function cTdeleteExe() {
		global $number, $mainframe, $compath, $Itemid;
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$table = getTableName();
		$table2 = getTableOrder();
		
		$coledit = JRequest::getVar('coledit', '', 'get');
	
		$query = 'ALTER TABLE ' . $table . ' DROP COLUMN ' . $coledit;
		$model->dbFExe($query);
		
		$query = 'DELETE FROM ' . $table2 . ' WHERE realname = \'' . $coledit . '\'';
		$model->dbFExe($query);
		
		//If the col, that will be deleted is used at autosort
		$configsort = $model->getConfig('sort', $number);
		$poscol = strpos($configsort, $coledit);
		if ($poscol !== false) {
			$beforecol = '';
			
			$firstflag = 0;
			if ($poscol != 0) {
				$beforecol = substr($configsort, 0, $poscol-2);
				$firstflag = 1;
			}
			$afterwithcol = substr($configsort, $poscol);
			$kommapos = strpos($afterwithcol, ',');
			
			if ($kommapos != false && $firstflag == 0) {
				$aftercol = substr($afterwithcol, $kommapos+2);
			}
			else if ($kommapos != false) {
				$aftercol = substr($afterwithcol, $kommapos);
			}
			
			$mysort = $beforecol . $aftercol;
			$model->updateConfig($mysort, 'sort', $number);	
			
			JError::raiseNotice( 1000, JText::_('WARNING_AUTOSORT'));
		}
		$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('ACTION_SUCCESSFUL'));
	}
	
	function cTrename() {
		global $number, $Itemid, $mainframe, $compath;
		JRequest::setVar('view', 'changeTable');
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$rows2 = $model->getHeads();
		
		foreach ($rows2 as $row2) {
			$thname[] = $row2->thname;
			$realname[] = $row2->realname;
		}
		
		$menge = $model->getColNumb('order');
		JRequest::setVar('cTtask', 'rename');
		JRequest::setVar('menge', $menge);
		JRequest::setVar('thname', $thname);
		JRequest::setVar('realname', $realname);
		
		parent::display();
	}
	
	function cTrenameExe() {
		global $number, $mainframe, $compath, $Itemid;
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$table2 = getTableOrder();
		
		$menge = $model->getColNumb('order');
		$rows2 = $model->getHeads();
		
		foreach ($rows2 as $row2) {
			$thname[] = $row2->thname;
			$realname[] = $row2->realname;
		}
		
		for ($a = 0; $a < $menge; $a++) {
			$name = JRequest::getVar($realname[$a], '', 'post');
										
			if ($thname[$a] != $name) {	
				$name = mysql_escape_string($name);	
				$query = 'UPDATE ' . $table2 . ' SET thname = \'' . $name . '\' WHERE realname = \'' . $realname[$a] . '\'';
				$model->dbFExe($query);
			}
		}
		$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('ACTION_SUCCESSFUL'));
	}
	
	function cTordering() {
		global $number, $Itemid, $mainframe, $compath;
		JRequest::setVar('view', 'changeTable');
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$rows2 = $model->getHeads();
		$menge = $model->getColNumb('order');
		
		foreach ($rows2 as $row2) {
			$thname[] = $row2->thname;
			$realname[] = $row2->realname;
			$ordering[] = $row2->ordering;
		}
		
		
		$menge = $model->getColNumb('order');
		JRequest::setVar('cTtask', 'ordering');
		JRequest::setVar('menge', $menge);
		JRequest::setVar('thname', $thname);
		JRequest::setVar('realname', $realname);
		JRequest::setVar('ordering', $ordering);
		
		parent::display();
	}
	
	function cTorderingExe() {
		global $number, $Itemid, $mainframe, $compath;
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$table = getTableName();
		$table2 = getTableOrder();
		
		$rows2 = $model->getHeads();
				
		foreach ($rows2 as $row2) {
			$check[] = JRequest::getVar($row2->realname, '', 'post');
			sort($check);
			
			for ($k = 0; $k < count($check); $k++) {
				if ($check[$k] == $check[$k+1]) {
					JError::raiseWarning( 1000, JText::_("ERR_ORDERING"));
					$mainframe->redirect("$compath&task=cTordering&Itemid=$Itemid&choose=$number");
				}
			}
		}
		
		foreach ($rows2 as $row2) {
			$thorder = JRequest::getVar($row2->realname, '', 'post');
			
			$query = 'UPDATE ' . $table2 . ' SET ordering = \'' . $thorder . '\' WHERE realname = \'' . $row2->realname . '\'';
			$model->dbFExe($query);
		}
		$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('ACTION_SUCCESSFUL'));
	}
	
	function cTautoSort() {
		global $number, $Itemid, $mainframe, $compath;
		JRequest::setVar('view', 'changeTable');
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$sortnumber = JRequest::getVar('sortnumber', 'not_defined', 'post');
		$menge = $model->getColNumb('order');
		$rows2 = $model->getHeads();
		
		foreach ($rows2 as $row2) {
			$thname[] = $row2->thname;
			$realname[] = $row2->realname;	
		}

		$sortstring = $model->getConfig('sort', $number);
		$singles = explode(', ', $sortstring);
		$kommacount = count($singles);
		
		for ($r = 0; $r <= $kommacount; $r++) {
			if (count($singles) > $r) {
				$sortcol[$r] = explode(' ', $singles[$r]);
			}
		}
		
		if ($sortnumber == 'not_defined') {
			if ($kommacount != 1) {
				$sortnumber = $kommacount;
			}
			else {
				$sortnumber = JRequest::getVar('sortnumber', '1', 'get');
			}
		}
		//echo $sortnumber;
			
		JRequest::setVar('cTtask', 'autoSort');
		JRequest::setVar('menge', $menge);
		JRequest::setVar('thname', $thname);
		JRequest::setVar('realname', $realname);
		JRequest::setVar('sortnumber', $sortnumber);
		JRequest::setVar('sortcol', $sortcol);
		
		parent::display();
	}
	
	function cTautoSortExe() {
		global $number, $Itemid, $mainframe, $compath;
		JRequest::setVar('view', 'changeTable');
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$gpost = $_POST;
		$deleteSort = JRequest::getVar('deleteSort', '', 'post');
		$sortnumber = JRequest::getVar('sortnumber', '1', 'get');
		
		//Autosort delete
		if ($deleteSort == 'delete') {
			$model->updateConfig('', 'sort', $number);
			$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('ACTION_SUCCESSFUL'));
		}		
		
		$noentryflag = 1;
		for ($x = 1; $x <= $sortnumber; $x++) {
			//Check Entry
			//If one col is selected several times
			for ($g = $x - 1; $g > 0; $g--) {
				if (($gpost['sort_'.$x] == $gpost['sort_'.$g]) && ($gpost['sort_'.$g] != NULL)) {
					
					JError::raiseWarning( 1000, JText::_('ERR_SAME_COL'));
					$mainframe->redirect("$compath&task=cTautoSort&Itemid=$Itemid&choose=$number&sortnumber=$sortnumber");
				}
			}
			
			//Check if a user selected a col but not asc or desc or otherwise round
			if (($gpost['sort_'.$x] == NULL && $gpost['ascdesc_'.$x] != NULL) || ($gpost['sort_'.$x] != NULL && $gpost['ascdesc_'.$x] == NULL)) {
				JError::raiseWarning( 1000, JText::_('ERR_NO_COL'));
				$mainframe->redirect("$compath&task=cTautoSort&Itemid=$Itemid&choose=$number&sortnumber=$sortnumber");
			}
			//If a user selected nothing
			else if ($gpost['sort_'.$x] == NULL && $gpost['ascdesc_'.$x] == NULL){
				continue;
			}
			//Everything's alright
			else {
				$mypost['cfg_sort'] .= $gpost['sort_'.$x] . ' ' . $gpost['ascdesc_'.$x]; 
				$noentryflag = 0;
			}
			
			if ($x != $sortnumber && $gpost['sort_'.$x] != NULL) {
				$emptyflag = 0;
				for ($i = $x + 1; $i <= $sortnumber; $i++) {
					if ($gpost['sort_'.$i] != NULL) {
						$emptyflag = 1;
					}
				}
				
				if ($emptyflag == 1) {
					$mypost['cfg_sort'] .= ', ';
				}
			}
		}
		
		if ($noentryflag == 1) {
			JError::raiseWarning( 1000, JText::_('ERR_CHOOSE_COL'));
			$mainframe->redirect("$compath&task=cTautoSort&Itemid=$Itemid&choose=$number&sortnumber=$sortnumber");
		}
		
		
		$choose = JRequest::getVar('choose', '', 'post');
	
		foreach ($mypost as $k => $v) {
			if (strpos($k, 'cfg_') === 0) {
				if (!get_magic_quotes_gpc()) {
					$v=addslashes($v);
				}
							
				$evVar = substr($k, 4);
				$model->updateConfig($v, $evVar, $number);
			}
		}
		
		$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('CONFIG_SAVE'));
	}
	
	function cTchangeType() {
		global $number, $Itemid, $mainframe, $compath;
		JRequest::setVar('view', 'changeTable');
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$menge = $model->getColNumb('order');
		$rows2 = $model->getHeads();
		
		//Prepare the dropdowns
		$this->getDropdowns($model);
		
		foreach ($rows2 as $row2) {
			$thname[] = $row2->thname;
			$datatp[] = $row2->datatp;		
		}
		
		JRequest::setVar('cTtask', 'changeType');
		JRequest::setVar('menge', $menge);
		JRequest::setVar('thname', $thname);
		JRequest::setVar('datatp', $datatp);
		
		parent::display();
	}
	
	function getDropdowns($model) {
	        $dropdownindex = $model->getConfig('dropdownindex', -999);
		$ddiarray = explode(';', $dropdownindex);
		
		$drconfig = array();
		$dnames = array();
		$dcount = count($ddiarray);
		if ($dcount == 1 && $ddiarray[0] == '') {
			$dcount = 0;
		}
		
		for ($a = 0; $a < $dcount; $a++) {
			$head = explode(';', $model->getConfig('dropdown_' . $ddiarray[$a], -999));
			$drconfig[$a] = $head[0];
			$dnames[$a] = 'dropdown_' . $ddiarray[$a];
		}

		JRequest::setVar('drconfig', $drconfig);
		JRequest::setVar('ddiarray', $ddiarray);
		JRequest::setVar('dcount', $dcount);
		JRequest::setVar('dnames', $dnames);
        }
	
	function cTchangeTypeExe() {
		global $number, $Itemid, $mainframe, $compath;
		JRequest::setVar('view', 'changeTable');
		$model =& $this->getModel('eventtableedit');
		include_once ('includes/helper.php');
		$this->aclExe('edit_table');
		
		$table = getTableName();
		$table2 = getTableOrder();
		
		$menge = $model->getColNumb('order');
		
		$rows2 = $model->getHeads();
		
		foreach ($rows2 as $row2) {
			$realname[] = $row2->realname;
		}
		
		for ($a = 0; $a < $menge; $a++) {
			$type = JRequest::getVar('datatype_' . $a, '', 'post');
			
			$query = 'ALTER TABLE ' . $table . ' MODIFY ' . $realname[$a] . ' ' . $type;
			$model->dbFExe($query);
			
			$query = 'UPDATE ' . $table2 . ' SET datatp = \'' . $type . '\' WHERE realname = \'' . $realname[$a] . '\'';
			$model->dbFExe($query);
		}
		$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('ACTION_SUCCESSFUL'));
	}
	
	function acl($level) {
		global $number, $Itemid;
		$model =& $this->getModel('eventtableedit');
		
		$app  = & JFactory::getApplication();
		$my  = & $app->getUser();
								
		$ulevel = strtolower($my->usertype);
		$uname = strtolower($my->username);
		$ulevelInt = 0;
		
		$userrights = '';
		$ulevelInt = array();
		for ($a = 0; $a <= 5; $a++) {
			switch ($a) {
				case '0': 
					$userrights = $ulevel;
					break;
				case '1':
					$userrights = $model->getConfig('acl_view', $number);
					break; 
				case '2':
					$userrights = $model->getConfig('acl_edit', $number);
					break; 
				case '3':
					$userrights = $model->getConfig('acl_delete_reorder', $number);
					break; 
				case '4':
					$userrights = $model->getConfig('acl_new_row', $number);
					break; 
				case '5':
					$userrights = $model->getConfig('acl_edit_table', $number);
					break; 
			}
			
			//echo $userrights;
			switch ($userrights) {
				case 'all':
					$ulevelInt[$a] = 0;
					break;
				case 'registered':
					$ulevelInt[$a] = 1;
					break;
				case 'author':
					$ulevelInt[$a] = 2;
					break;
				case 'editor':
					$ulevelInt[$a] = 3;
					break;
				case 'publisher':
					$ulevelInt[$a] = 4;
					break;
				case 'manager':
					$ulevelInt[$a] = 5;
					break;
				case 'administrator':
					$ulevelInt[$a] = 6;
					break;
				case 'super administrator':
					$ulevelInt[$a] = 7;
					break;
				default:
					$ulevelInt[$a] = 0;
				
			}
		}
		
		$enhancedur = strtolower($model->getConfig('Uacl_' . $level, $number));
		
		if ($enhancedur != '') {
			$enhancedur = explode(',', $enhancedur);
			$kflag = 0;
						
			for ($g = 0; $g < count($enhancedur); $g++) {
				if ($uname == trim($enhancedur[$g])) {
					$kflag = 1;
				}
			}
			
			if ($kflag == 1 && $uname != '') {
				return true;
			}
			//Super Administrator has full acl
			else if ($ulevelInt[0] == 7) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
		
			switch ($level) {
				case 'view':
					if ($ulevelInt[0] >= $ulevelInt[1]) {
						return true;
					}
					else {
						return false;
					}
					break;
				case 'edit':
					if ($ulevelInt[0] >= $ulevelInt[2]) {
						return true;
					}
					else {
						return false;
					}
					break;
				case 'delete_reorder':
					
					if ($ulevelInt[0] >= $ulevelInt[3]) {
						return true;
						
					}
					else {
					
						return false;
					}
					break;
				case 'new_row':
					if ($ulevelInt[0] >= $ulevelInt[4]) {
						return true;
					}
					else {
						return false;
					}
					break;
				case 'edit_table':
					if ($ulevelInt[0] >= $ulevelInt[5]) {
						return true;
					}
					else {
						return false;
					}
					break;
				default:
					echo JText::_('ERR_ACL');
					return false;
			}
		}
		
	}

	function aclExe($level) {
		global $compath, $number, $Itemid;
		if (!$this->acl($level)) {
			JError::raiseError( 403, JText::_("ERR_PERMISSION"));
		}
	}
	
	//Save cellcontent
	function ajaxsave() {
		global $number, $Itemid, $mainframe, $compath, $parsebbcode;
		$model =& $this->getModel('eventtableedit');
		$number = JRequest::getVar('number', '', 'post');
		$this->aclExe('edit');
		include_once ('includes/helper.php');
		
		$dbprefix = $mainframe->getCfg('dbprefix');
		
		$inhalt = JRequest::getVar('openfield', '', 'post');
		//$inhalt = nl2br($inhalt);
		
		$inhalt = str_replace("//()||", "&", $inhalt);
		$inhalt = str_replace("\n", " ", $inhalt);
		$inhalt = str_replace('\n', "", $inhalt);
		$inhalt = str_replace("\r", " ", $inhalt);
		$inhalt = str_replace('\r', "", $inhalt);
		$inhalt = str_replace("\t", "", $inhalt);
		$inhalt = str_replace('\t', "", $inhalt);
		$inhalt = trim($inhalt);
		$inhalt = htmlentities($inhalt, ENT_QUOTES, 'utf-8');
		
		$ic = (int) JRequest::getVar('ic', '', 'post');
		$oc = (int) JRequest::getVar('oc', '', 'post');
		$number = JRequest::getVar('number', '1', 'post');
		$id = (int) JRequest::getVar('id', '', 'post');
		$date_switch = JRequest::getVar('date_switch', 0, 'post');
		
		//Change the date format
		$outinhalt = $inhalt;
		if ($date_switch == 'DATE' && $inhalt != "") {
			$outinhalt = date_mysql_to_german($inhalt, $model->getConfig('date_format', $number));
		}
		else if ($date_switch == 'TIME' && $inhalt != "") {
			$outinhalt = format_time($inhalt, $model->getConfig('time_format', $number));
		}
		
		//Eliminate Zero
		$slash = '\'';
		if ($inhalt == "") {
			$inhalt = 'NULL';
			$slash = '';
		}
		
		$rows2 = $model->getHeads();
		$menge = $model->getColNumb('order');
			
		foreach ($rows2 as $row2) {
			//$thname[] = $row2->thname;
			$realname[] = $row2->realname;
		}
		
		$inhalt = mysql_escape_string($inhalt);
						
		$query = 'UPDATE ' . $dbprefix . 'event_table_edit_' . $number . ' SET ' . $realname[$ic] . ' = ' . $slash . $inhalt . $slash . ', locktab = 0 WHERE id = ' . $id;
		$model->dbFExe($query);
		
		
		//BBCODE Parsing
		$bbcode = (int) $model->getConfig('bbcode', $number);
		$bbimg = (int) $model->getConfig('bbimg', $number);
		addbbcode($bbimg);
		if ($bbcode == 0) {
			$outinhalt = $parsebbcode->parse ($outinhalt);
			$outinhalt = str_replace('[br]','<br>', $outinhalt);
		}
				
		echo htmlspecialchars_decode($outinhalt, ENT_NOQUOTES);
		exit;
	}
	
	//Get cellcontent
	function ajaxgetfield() {
		global $number, $Itemid, $mainframe, $compath, $parsebbcode;
		
		$number = JRequest::getVar('number', '1', 'get');
		
		$model =& $this->getModel('eventtableedit');
		
		$this->aclExe('edit');
		include_once ('includes/helper.php');
		
		$dbprefix = $mainframe->getCfg('dbprefix');
		
		$rows2 = $model->getHeads('realname');
		$menge = $model->getColNumb('order');
			
		foreach ($rows2 as $row2) {
			$realname[] = $row2->realname;
		}
		
		$ic = (int) JRequest::getVar('ic', '', 'get');
		$oc = (int) JRequest::getVar('oc', '', 'get');
		$number = JRequest::getVar('number', '1', 'get');
		$id = (int) JRequest::getVar('id', '', 'get');
		$date_switch = JRequest::getVar('date_switch', '', 'get');
		
		$query = "SELECT " . $realname[$ic] . " FROM " . $dbprefix . "event_table_edit_$number WHERE id = $id";
		$inhalt = $model->dbExeRes($query);
		
		echo $inhalt;
		exit;
	}
	
	//Create a new row
	function ajaxnewrow() {
		global $number, $Itemid, $mainframe, $compath;
		$model =& $this->getModel('eventtableedit');
		$number = JRequest::getVar('number', '', 'post');
		$this->aclExe('new_row');
		include_once ('includes/helper.php');
		
		$dbprefix = $mainframe->getCfg('dbprefix');
		$biggestOrdering = (int) JRequest::getVar('biggestOrdering', '0', 'get');
		$number = JRequest::getVar('number', '1', 'get');
				
		$rows2 = $model->getHeads();
		$menge = $model->getColNumb('order');
			
		foreach ($rows2 as $row2) {
			$thname[] = $row2->thname;
			$realname[] = $row2->realname;
		}
		
		$myquery = '';
		$myquery2 = '';
		for ($a = 0; $a < $menge; $a++) {
			$myquery = $myquery . ' , ' . $realname[$a];
			$myquery2 = $myquery2 . ' , NULL';
		}
		
		$query = "INSERT INTO " . $dbprefix . "event_table_edit_$number (locktab, ordering $myquery) VALUES (0, " . $biggestOrdering . "$myquery2)";
		$model->dbFExe($query);
		$query = "SELECT LAST_INSERT_ID() as lid FROM " . $dbprefix . "event_table_edit_$number LIMIT 1";
		$res = $model->dbExeRes($query);
		echo $res;
		
		exit;
	}
}
?>
