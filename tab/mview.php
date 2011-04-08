<?php  // $Id: view.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * TAB
 * 
 * @author : Patrick Thibaudeau
 * @version $Id: version.php,v 1.0 2007/07/01 16:41:20
 * @package tab
 **/

    require_once("../../config.php");
    require_once("lib.php");
    require_js($CFG->wwwroot.'/lib/mobile.js');


    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a  = optional_param('a', 0, PARAM_INT);  // tab ID

    if ($id) {
        if (! $cm = $DB->get_record("course_modules", array("id"=>$id))) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
            error("Course is misconfigured");
        }
    
        if (! $tab = $DB->get_record("tab", array("id"=>$cm->instance))) {
            error("Course module is incorrect");
        }

    } else {
        if (! $tab = $DB->get_record("tab", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = $DB->get_record("course", array("id"=>$tab->course))) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("tab", array($tab->id=>$course->id))) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id);
    add_to_log($course->id, "tab", "view", "view.php?id=$cm->id", "$tab->id");

/// Print the page header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    $strtabs = get_string("modulenameplural", "tab");
    $strtab  = get_string("modulename", "tab");

    print_header("$course->shortname: $tab->name", "$course->fullname",
                 "$navigation <a href=index.php?id=$course->id>$strtabs</a> -> $tab->name", 
                  "", "", true, "",
                  navmenu($course, $cm));


///SQL to gather all Tab modules within the course. Needed if display tab menu is selected
			  
	$results = $DB->get_records_sql('SELECT '.$CFG->prefix.'course_modules.id as id, '.$CFG->prefix.'tab.name as name, '
							   .$CFG->prefix.'tab.taborder as taborder, '
                                                           .$CFG->prefix.'tab.menuname as menuname FROM ('.$CFG->prefix.'modules INNER JOIN '
							   .$CFG->prefix.'course_modules ON '
							   .$CFG->prefix.'modules.id = '.$CFG->prefix.'course_modules.module) INNER JOIN '
							   .$CFG->prefix.'tab ON '.$CFG->prefix.'course_modules.instance = '
							   .$CFG->prefix.'tab.id WHERE ((('.$CFG->prefix.'modules.name)="Tab") AND (('
							   .$CFG->prefix.'course_modules.course)="'.$course->id.'")) ORDER BY '.$CFG->prefix.'tab.taborder;');
        


	

	if ($tab->displaymenu == 1) {
        print_box_start();
        echo '<a id="displayText" href="javascript:toggle(\'m_menu\',\'displayText\',\''.get_string('show','mobile').' menu\',\''.get_string('hide','mobile').' menu\');">'.get_string('show','mobile').' menu</a>';
	echo '<div id="m_menu" style="display: none">'."\n";
	echo '	<table class="menutable" width="100%" border="0" cellpadding="4">'."\n";
	echo '  	<tr>'."\n";
	echo '  	  <td class="menutitle">'.$tab->menuname.'</td>'."\n";
	echo '  	</tr>'."\n";
		$i = 0; ///needed to determine color change on cell
	foreach ($results as $result){ /// foreach
        echo '	<tr';
		 if($tab->name == $result->name){//old code for different color = if ($i % 2) {
			echo ' class="row">'."\n";
			} else {
			echo '>'."\n";
			}
	 
        echo 	'<td><a href="view.php?id='.$result->id.'" ><div id="tab_menu_links">'.$result->name.'</div></a></td>'."\n";
  	echo '	</tr>'."\n";
	$i++;
	}
	echo '	</table>'."\n";
	echo '</div>';
        echo '<br>';
        print_box_end();
	}
 //print tab content here

	//-------------------------------Get tabs-----------------------------------------------
        $tabs = $DB->get_records('tab_content', array('tabid'=>$tab->id), 'tabcontentorder');

	$i =0;
        
	foreach ($tabs as $tab){
        print_box_start();
	echo '<a id="'.$tab->tabname.'displayText" href="javascript:toggle(\'m_'.$tab->tabname.'\',\''.$tab->tabname.'displayText\',\''.get_string('show','mobile').' '.$tab->tabname.'\',\''.get_string('hide','mobile').' '.$tab->tabname.'\');">'.get_string('show','mobile').' '.$tab->tabname.'</a>';
	echo '<div id="m_'.$tab->tabname.'" style="display: none">'."\n";
	
	
		$format = $tab->format;
	
		if ($format == 'NONE') {
			$tabcontent = $tab->tabcontent;
			
		} elseif ($format == 'Moodle') {
			$tabcontent = format_text($tab->tabcontent, FORMAT_MOODLE);
                        
		} elseif ($format == 'Plain') {
                        $tabcontent = format_text($tab->tabcontent, FORMAT_PLAIN);
                        
                } elseif ($format == 'HTML') {
                        $tabcontent = format_text($tab->tabcontent, FORMAT_HTML);
                        
                }
	echo trim($tabcontent);
        echo '</div>'."\n";
        print_box_end();
	}

	/// Finish the page
	print_footer($course);

?>

<?php
    function activity_link($activity,$cid)
    {
          global $CFG;
          $sql='SELECT '.$CFG->prefix.'course_modules.id as id,'.$CFG->prefix.$activity.'.name as name
	     FROM ('.$CFG->prefix.'modules INNER JOIN '
                                        .$CFG->prefix.'course_modules ON '
					.$CFG->prefix.'modules.id = '.$CFG->prefix.'course_modules.module) INNER JOIN '
					.$CFG->prefix.$activity.' ON '.$CFG->prefix.'course_modules.instance = '
                                        .$CFG->prefix.$activity.'.id WHERE ((('.$CFG->prefix.'modules.name)="'.$activity.'") AND (('
				        .$CFG->prefix.'course_modules.course)="'.$cid.'"))';
         $results = $DB->get_records_sql($sql) or die($sql);
         foreach ($results as $result){ /// foreach
             echo '<tr>';	 
             echo '<td><a href="../'.$activity.'/view.php?id='.$result->id.'">'.$result->name.'</a></td>'."\n";
  	     echo '</tr>'."\n"; 
	}
    }

   function get_activity($cmid)
   {      
          global $CFG;
          $sql="select m.name from ".$CFG->prefix."modules m, ".$CFG->prefix."course_modules cm where cm.id=$cmid and cm.module=m.id";
          $results = $DB->get_record_sql($sql) or die($sql);
          if($results)
            return $results->name;
          return "error";
   }
?>
