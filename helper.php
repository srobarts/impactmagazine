<?php
/**
 * $Id: helper.php 107 2010-05-02 03:57:23Z kapsl $
 * @copyright (C) 2007 - 2009 Manuel Kaspar
 * @license GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

global $number;

function getTableName() {
  global $number, $mainframe;
	$dbprefix = $mainframe->getCfg('dbprefix');
	$table = $dbprefix . 'event_table_edit_' . $number;
	
	return $table;
}

function getTableOrder() {
	global $number, $mainframe;
	$dbprefix = $mainframe->getCfg('dbprefix');
	$table = $dbprefix . 'event_table_order_' . $number;
	
	return $table;
}

function checkTable($model) {
	global $Itemid, $number, $mainframe, $compath;
	$dbprefix = $mainframe->getCfg('dbprefix');
	$database = &JFactory::getDBO();
		
	$table = $dbprefix . 'event_table_edit_' . $number;
	
	$query = "SHOW TABLE STATUS LIKE '$table'";
	$rows = $model->dbExe($query);
		
	if(count($rows) > 0) {
		//If a table exists	
	}
	else {
		//Create new tables
		$query = 'CREATE TABLE #__event_table_edit_' . $number . ' (id INT NOT NULL AUTO_INCREMENT, ordering INT NOT NULL default 0, locktab VARCHAR(1) NOT NULL default 0, PRIMARY KEY (id) )';
		$model->dbFExe($query);
				
		$query = 'CREATE TABLE #__event_table_order_' . $number . ' (id INT NOT NULL AUTO_INCREMENT, thname TEXT NOT NULL, ordering INT NOT NULL default 0, realname TEXT, datatp TEXT, PRIMARY KEY (id) )';
		$model->dbFExe($query);			
	      			
        $cfgname = returnCfg('name');
		$cfgvalue = returnCfg('value');
		   		
		$model->insertConfig($cfgvalue, $cfgname, $number);
   
    	$mainframe->redirect("$compath&Itemid=$Itemid&choose=$number", JText::_('CONFIG_SAVE')); 
	}
}

function returnCfg($nv) {
	if ($nv == 'name') {
		$cfg = array('headline', 'acl_view', 'acl_edit', 'acl_delete_reorder', 'acl_new_row', 'acl_edit_table', 'linecolor1', 'linecolor2', 'cellspacing', 'cellpadding', 'filter', 'bbcode', 'bbimg', 'print', 'upcount', 'sitebreak', 'pretext', 'aftertext', 'sort', 'Uacl_view', 'Uacl_edit', 'Uacl_delete_reorder', 'Uacl_new_row', 'Uacl_edit_table', 'date_format', 'time_format');
	}
	else if ($nv == 'value') {
		$cfg = array('Event Table Edit', 'all', 'all', 'all', 'all', 'all', 'CCCCCC', 'FFFFFF', 5, 2, 0, 0, 0, 0, 1, '', '', '', '', '', '', '', '', '', '%d.%m.%Y', '%H:%M');
	}
	else {
		die('Error with returnCfg');
	}
	return $cfg;
}

function date_german_to_mysql($date) {
	$d    =    explode(".",$date);
    
	if ($date == '') {
		return NULL;
	}
	
    return    sprintf("%04d.%02d.%02d", $d[2], $d[1], $d[0]);
}

function date_mysql_to_german($date, $format) {
	if ($date == NULL) {
		return NULL;
	}
	
    return strftime( $format, strtotime( $date ));
}

function format_time($time, $format) {
	if ($time == NULL) {
		return NULL;
	}
	return strftime( $format, strtotime( $time ));
}

function switch_datatypes($dtype) {
	switch ($dtype) {
			case 'TEXT':
				$type = JText::_('TYPE_TEXT');
				break;
			case 'INT':
				$type = JText::_('TYPE_INT');
				break;
			case 'FLOAT':
				$type = JText::_('TYPE_FLOAT');
				break;
			case 'DATE':
				$type = JText::_('TYPE_DATE');				
				break;
			case 'TIME':
				$type = JText::_('TYPE_TIME');
				break;
	}
	return $type;
}

function do_bbcode_url ($action, $attributes, $content, $params, &$node_object) {
    if (!isset ($attributes['default'])) {
		$url = $content;
		$text = htmlspecialchars ($content);
	} else {
		$url = $attributes['default'];
		$text = $content;
	}
	if ($action == 'validate') {
		if (substr ($url, 0, 5) == 'data:' || substr ($url, 0, 5) == 'file:'
		|| substr ($url, 0, 11) == 'javascript:' || substr ($url, 0, 4) == 'jar:') {
			return false;
		}
		return true;
	}
	return '<a href="'.htmlspecialchars ($url).'" target="_blank">'.$text.'</a>';
}

function do_bbcode_img ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		if (substr ($content, 0, 5) == 'data:' || substr ($content, 0, 5) == 'file:'
		|| substr ($content, 0, 11) == 'javascript:' || substr ($content, 0, 4) == 'jar:') {
			return false;
		}
		return true;
	}
	return '<img src="'.htmlspecialchars($content).'" alt="bbcodeimg">';
}


function addbbcode($bbimg) {
	global $parsebbcode;
	require_once 'components/com_eventtableedit/includes/bb_code/stringparser_bbcode.class.php';
	$parsebbcode = new StringParser_bbcode();
	$parsebbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<b>', 'end_tag' => '</b>'),
                  'inline', array ('block', 'inline'), array ());
	$parsebbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<i>', 'end_tag' => '</i>'),
                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	$parsebbcode->addCode ('url', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'),
				  'link', array ('listitem', 'block', 'inline'), array ('link'));
	$parsebbcode->addCode ('link', 'callback_replace_single', 'do_bbcode_url', array (),
				  'link', array ('listitem', 'block', 'inline'), array ('link'));	
	$parsebbcode->addCode ('list', 'simple_replace', null, array ('start_tag' => '<ul>', 'end_tag' => '</ul>'),
                  'list', array ('block', 'listitem'), array ());
	$parsebbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
                  'listitem', array ('list'), array ());
	$parsebbcode->setCodeFlag ('*', 'closetag', 'bbcode_CLOSETAG_OPTIONAL');
	$parsebbcode->setCodeFlag ('*', 'paragraphs', false);
	$parsebbcode->setCodeFlag ('list', 'paragraph_type', 'bbcode_PARAGRAPH_BLOCK_ELEMENT');
	$parsebbcode->setCodeFlag ('list', 'opentag.before.newline', 'bbcode_NEWLINE_DROP');
	$parsebbcode->setCodeFlag ('list', 'closetag.before.newline', 'bbcode_NEWLINE_DROP');
		
	//Optional img
	if ($bbimg == 0) {
		$parsebbcode->addCode ('img', 'usecontent', 'do_bbcode_img', array (), 'image', array ('listitem', 'block', 'inline', 'link'), array ());
		$parsebbcode->setOccurrenceType ('img', 'image');
	}
}

function filter($rows2, $menge, $model) {
	//$menge = row count
	//function presently only will filter on the date	
	global $number;
	
	/***************************
	 * Date Filter
	 ***************************/
	
	//---Filter by unimx
	$filternach = JRequest::getVar('filternach', ''); //get the filter value from javascript input
	$suchmuster = '*';
	$ersetzenmit = '%';
	$filternach = str_replace($suchmuster,$ersetzenmit,$filternach);
	
	//example:
	//$newfilter = str_replace(*, %, $filter) -> search for *, replace with %, from filter
	//we do this because we tell the user they can use * for wildcard searches - while SQL needs % for wildcard character
	
	JRequest::setVar('filternach', $filternach); //replace javascript variable with filter result
		  
	//Filter for date
	//Code will piece apart a date in the format 10.27.1971
	//Separating each part and preparing for use in a query
	$parts = explode('.',$filternach);
	$dflag = true; // day flag
	$months = array(0,31,29,31,30,31,30,31,31,30,31,30,31);
	$dd = (int) $parts[0]; //day
	
	$mm = NULL;
	if (count($parts) > 1) {
		$mm = (int) $parts[1];  //month
	}
	if (count($parts) > 2) {
		$yyyy = (int) $parts[2]; //year
	}
	if(!$dd||!$mm||!$yyyy) {
		$dflag = false;
	}
	if(@mktime(0,0,0,$mm,$dd,$yyyy)<1) {
		$dflag = false;
	}
	if($mm != NULL && $dd>$months[$mm]) {
		$dflag = false;
	}
	if($mm==2&&$dd==29) {
		if($yyyy%4) {
			$dflag = false;
		}
		if(!$yyyy%100&&$yyyy%400) {
			$dflag = false;
		} 
	}
	if ($dflag) {
		$filternach = $parts[2] . '-' . $parts[1] . '-' . $parts[0]; //reconstruct filternach with date
	}
	
	/***************************
	 * Event Filter
	 ***************************/
	
	//get the filter variable from javascript
	$event_filter = JRequest::getVar('event_filter', '');
	//separate out the wildcard characters, prepare for SQL
	$wildcard = '*';
	$sql_wildcard = '%';
	$event_filter = str_replace($wildcard,$sql_wildcard,$event_filter);
	
	JRequest::setVar('event_filter', $event_filter); //replace javascript variable with filter result
	
	/***************************
	 * Key Word Filter
	 ***************************/
	
	//get the filter variable from javascript
	$keyword_filter = JRequest::getVar('keyword_filter', '');
	//separate out the wildcard characters, prepare for SQL
	$wildcard = '*';
	$sql_wildcard = '%';
	$keyword_filter = str_replace($wildcard,$sql_wildcard,$keyword_filter);
	
	JRequest::setVar('keyword_filter', $keyword_filter); //replace javascript variable with filter result
	
	/***************************
	 * City Filter
	 ***************************/
	
	//get the filter variable from javascript
	$city_filter = JRequest::getVar('city_filter', '');
	//separate out the wildcard characters, prepare for SQL
	$wildcard = '*';
	$sql_wildcard = '%';
	$city_filter = str_replace($wildcard,$sql_wildcard,$city_filter);
	
	JRequest::setVar('city_filter', $city_filter); //replace javascript variable with filter result
	
	/************************
	 * Prepare SQL Query
	 ************************/
	
	//Generate SQL-Query
	$ordering = $model->getConfig('sort', $number);
	if ($ordering == '') {
		$ordering = 'ordering';
		$reorderflag = 0;
	}
	$tabellenname = getTableName();
	$befehlteil_1 = "SELECT * FROM ". getTableName(). "  WHERE ";
	$befehlteil_2 = ' LIKE "'. "%". htmlentities($filternach, ENT_QUOTES, 'utf-8') . "%". '"'. " OR ";
	$befehlteil_3 = " ORDER BY ". $ordering;
	
	//Partial construction of query
	//$befehlteil_1 = SELECT * FROM table WHERE
	//$befehlteil_2 = LIKE '%10.27.1071%' OR 
	//$befehlteil_3 = ORDER BY order
	
	$befehlteil_fertig2 = '';
	foreach($rows2 as $row2) {
		$befehlteil_fertig = $row2->realname . ' ' .$befehlteil_2;
		$befehlteil_fertig2 .= $befehlteil_fertig;
	}
	
	//foreach parses through rows- one line for each
	//$befehlteil_fertig = row2name LIKE '%10.27.1971%' OR
	//$befehlteil_fertig2 = row2name LIKE '%0.27.1971%' OR ????
	
	//Cut the last or
	$befehlteil_fertig2 = $befehlteil_1. $befehlteil_fertig2;
	$befehlteil_fertig2 = substr($befehlteil_fertig2,0,-3);
	  
	$ryn = JRequest::getVar('resetyesno', 1, 'get');  //get reset variable from javascript
	$query = '';
	
	if ((($menge) != 0) && ($ryn && ((int) $model->getConfig('filter', $number) == 0) && $filternach != '')){
		$query = $befehlteil_fertig2. $befehlteil_3; //get complete query string
	}
	
	return $query;  
}

function getNextRL($rows2) {
	$flag = 1;
	$c = 0;
	while ($flag == 1) {
		$nametablehead = 'tname_' . $c;
		
		$flag = 0;	
		foreach ($rows2 as $row2) {
		
			$realname = $row2->realname;
			if ($nametablehead == $realname) {
				$flag = 1;
				$c++;
			}
		}
		$nametablehead = 'tname_' . $c;
	}
	
	return $nametablehead;
}
?>
