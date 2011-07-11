<?php // $Id: format.php,v 1.83.2.3 2008/12/10 06:05:27 dongsheng Exp $
      // Display the whole course as "topics" made of of modules
      // In fact, this is very similar to the "weeks" format, in that
      // each "topic" is actually a week.  The main difference is that
      // the dates aren't printed - it's just an aesthetic thing for
      // courses that aren't so rigidly defined by time.
      // Included from "view.php"
      
    require_once($CFG->libdir.'/ajax/ajaxlib.php');
global  $useajax;
if($useajax===true)
{
    require_js($CFG->wwwroot.'/course/format/ouil/ajaxcourse.js');
    require_js($CFG->wwwroot.'/course/format/ouil/block_classes.js');
    require_js($CFG->wwwroot.'/course/format/ouil/section_classes.js');
}
  
//    $topic = optional_param('ouil', -1, PARAM_INT);
    $topic = optional_param('topic', -1, PARAM_INT);

    // Bounds for block widths
    // more flexible for theme designers taken from theme config.php
    $lmin = (empty($THEME->block_l_min_width)) ? 100 : $THEME->block_l_min_width;
    $lmax = (empty($THEME->block_l_max_width)) ? 210 : $THEME->block_l_max_width;
    $rmin = (empty($THEME->block_r_min_width)) ? 100 : $THEME->block_r_min_width;
    $rmax = (empty($THEME->block_r_max_width)) ? 210 : $THEME->block_r_max_width;

    $cmin = (empty($THEME->block_c_min_width)) ? 100 : $THEME->block_c_min_width;
    $cmax = (empty($THEME->block_c_max_width)) ? 210 : $THEME->block_c_max_width;

/*
    define('BLOCK_L_MIN_WIDTH', $lmin);
    define('BLOCK_L_MAX_WIDTH', $lmax);
    define('BLOCK_R_MIN_WIDTH', $rmin);
	 define('BLOCK_R_MAX_WIDTH', $rmax);
    define('BLOCK_C_MIN_WIDTH', $cmin);
	 define('BLOCK_C_MAX_WIDTH', $cmax);
*/


$lmax=200;
    define('BLOCK_L_MIN_WIDTH', $lmax);
    define('BLOCK_L_MAX_WIDTH', $lmax);

$rmax=200;

    define('BLOCK_R_MIN_WIDTH', $rmax);
	 define('BLOCK_R_MAX_WIDTH', $rmax);
	if(defined('BLOCK_POS_CENTER'))
	{
		$cmax=200;
		    define('BLOCK_C_MIN_WIDTH', $cmax);
			 define('BLOCK_C_MAX_WIDTH', $cmax);
	}

    $preferred_width_left  = bounded_number(BLOCK_L_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]),  
                                            BLOCK_L_MAX_WIDTH);

    $preferred_width_right = bounded_number(BLOCK_R_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), 
                                            BLOCK_R_MAX_WIDTH);

	if(defined('BLOCK_POS_CENTER'))
	{
		$preferred_width_center  = bounded_number(BLOCK_C_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_CENTER]),  
                                            BLOCK_C_MAX_WIDTH);
	}
//echo ("<BR>  preferred_width_center ".$preferred_width_center);
//echo ("<BR>  preferred_width_right ".$preferred_width_right);
//echo ("<BR>  preferred_width_left ".$preferred_width_left);
$preferred_width_center="500";

    if ($topic != -1) {
        $displaysection = course_set_display($course->id, $topic);
    } else {
        if (isset($USER->display[$course->id])) {       // for admins, mostly
            $displaysection = $USER->display[$course->id];
        } else {
            $displaysection = course_set_display($course->id, 0);
        }
    }
	require_once($CFG->dirroot.'/course/format/ouil/lib.php');

    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
        $course->marker = $marker;
        if (! set_field("course", "marker", $marker, "id", $course->id)) {
            error("Could not mark that topic for this course");
        }
    }

    $streditsummary   = get_string('editsummary');
    $stradd           = get_string('add');
    $stractivities    = get_string('activities');
    $strshowalltopics = get_string('showalltopics');
    $strtopic         = get_string('topic');
    $strgroups        = get_string('groups');
    $strgroupmy       = get_string('groupmy');
    $editing          = $PAGE->user_is_editing();
    
    if ($editing) {
        $strstudents = moodle_strtolower($course->students);
        $strtopichide = get_string('topichide', '', $strstudents);
        $strtopicshow = get_string('topicshow', '', $strstudents);
        $strmarkthistopic = get_string('markthistopic');
        $strmarkedthistopic = get_string('markedthistopic');
        $strmoveup = get_string('moveup');
        $strmovedown = get_string('movedown');
    }


/// Layout the whole page as three big columns.
    echo '<table id="layout-table" cellspacing="0" summary="'.get_string('layouttable').'"    ><tr>';

/// The left column ...
    $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
    foreach ($lt as $column) {
        switch ($column) {
            case 'left':
    if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
        echo '<td style="width:'.$preferred_width_left.'px;" id="left-column"    >';
        print_container_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
        print_container_end();
        echo '</td>';
    }

            break;
            case 'middle':
/// Start main column
    echo '<td id="middle-column"  width="'.$preferred_width_center.'">';
      

    print_container_start();
    echo skip_main_destination();
//    print_heading_block(get_string('topicoutline'), 'outline');
    echo '<table class="maintopics"   cellspacing="0"  width="100%" summary="'.get_string('layouttable').'" >';
	if(defined('BLOCK_POS_CENTER')) {
	    if (blocks_have_content($pageblocks, BLOCK_POS_CENTER) || $editing) {

	     echo '<TR><td   id="center-column">';
	        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_CENTER);
		echo '</td></TR>';
		}
}

/// If currently moving a file then show the current clipboard
    if (ismoving($course->id)) {
        $stractivityclipboard = strip_tags(get_string('activityclipboard', '', addslashes($USER->activitycopyname)));
        $strcancel= get_string('cancel');
        echo '<tr class="clipboard">';
        echo '<td >';
        echo $stractivityclipboard.'&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey='.$USER->sesskey.'">'.$strcancel.'</a>)';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
	/*
    echo '<table class="topics"     cellspacing="0"    width="100%"   summary="'.get_string('layouttable').'">';
/// Print Section 0

    $section = 0;
    $thissection = $sections[$section];
    if ($thissection->summary or $thissection->sequence or isediting($course->id)) {
        echo '<tr id="section-0" class="section main">';
   //     echo '<td class="left side">&nbsp;</td>';
        echo '<td  class="middle side" colspan="3">';
        
        echo '<div class="summary">';
        $summaryformatoptions->noclean = true;
        echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

        if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
            echo '<a title="'.$streditsummary.'" '.
                 ' href="editsection.php?id='.$thissection->id.'"><img src="'.$CFG->pixpath.'/t/edit.gif" '.
                 ' alt="'.$streditsummary.'" /></a><br /><br />';
        }
        echo '</div>';

        print_section($course, $thissection, $mods, $modnamesused);

        if (isediting($course->id)) {
            print_section_add_menus($course, $section, $modnames);
        }

        echo '</td>';
//        echo '<td class="right side">&nbsp;</td>';
        echo '</tr>';
        echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';
    }//section 0
echo '</table>';
	*/
	
if(!is_inside_frontpage($context)){// moodle home page
    echo '<table    cellspacing="0" cellpadding="0"  width="100%"   summary="'.get_string('layouttable').'">';
	 echo '<TR><td class="middle_headerright" width="3"></td>';
 		echo '<TD class="middle_headercenter"></td>';
 		echo '<TD class="middle_headercenter"></td>';
	 echo '</td><td class="middle_headerleft" width="3"></td></tr>';
	    echo '<TR class="headerline">';
		 echo '<td class="middle_headercenter" ></td>';
		$imgopenallsection='<IMG id ="actionOnallsectionIMG"   src="'.$CFG->pixpath .'/t/down.gif"    title="'.get_string('openAllSectionTxt','format_ouil'). '"  >';

		echo '<TD   class="middle_headercenter">'.get_string('learningObjectsTopicsHeader','format_ouil').'</td>';
//			echo '<TD class="middle_headercenter"></td>';
	echo '<TD   class="middle_headercenter2"  align="center" width="30"><a id ="actionOnallSection"   class="setAllsectionOpen"  href="javascript:SetChecked(true)"   	/>'.$imgopenallsection.'</a></TD>';
	 echo '<td class="middle_headercenter"></td>';
	 echo '</TR></table>';

		 }

    echo '<table class="topics"   cellpadding="0"  cellspacing="0"     width="100%"   summary="'.get_string('layouttable').'">';

/// Now all the normal modules by topic
/// Everything below uses "section" terminology - each "section" is a topic.

    $timenow = time();
    $section = 1;
    $sectionmenu = array();

    while ($section <= $course->numsections) {

        if (!empty($sections[$section])) {
            $thissection = $sections[$section];

        } else {
            unset($thissection);
            $thissection->course = $course->id;   // Create a new section structure
            $thissection->section = $section;
            $thissection->summary = '';
            $thissection->visible = 1;
            if (!$thissection->id = insert_record('course_sections', $thissection)) {
                notify('Error inserting new topic!');
            }
        }

        $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);





        if (!empty($displaysection) and $displaysection != $section) {
            if ($showsection) {
                $strsummary = strip_tags(format_string($thissection->summary,true));
                if (strlen($strsummary) < 57) {
                    $strsummary = ' - '.$strsummary;
                } else {
                    $strsummary = ' - '.substr($strsummary, 0, 60).'...';
                }
                $sectionmenu['topic='.$section] = s($section.$strsummary);
            }
            $section++;
            continue;
        }

       $trclass="cps";
        $aclass="openSection";
		$currentsectionText="&nbsp;";

		$SectionTxt_title=get_string('openSectionTxt','format_ouil');

        if ($showsection) {

            $currenttopic = ($course->marker == $section);

            $currenttext = '';
			$markerClass=" hid";
            if (!$thissection->visible) {
                $sectionstyle = ' hidden';
            } else if ($currenttopic) {
				$trclass="cps2";
                $sectionstyle = ' current';
				$currentsectionText='&nbsp;&nbsp;<IMG src="'.$CFG->themewww .'/'.current_theme().'/images/current_section.jpg"  alt="'.get_string('curentSection','format_ouil').'"  >';
				$markerClass="";
		        $aclass="closeSection";
                $currenttext = get_accesshide(get_string('currenttopic','access'));
				$SectionTxt_title=get_string('closeSectionTxt','format_ouil');

            } else {
		       // $aclass="closeSection";
                $sectionstyle = '';
            }
///
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
$tr2="cpsFocus";
			echo '<tr class="'.$trclass.'"  onmouseover="this.className=\''.$tr2.'\';"      onmouseout="this.className=\''.$trclass.'\';"  >';

			if (empty($thissection->summary)) {
				$thissection->summary='';
				echo '<td class="td_left"   ></td>';
				echo '<td  class="td_center"   ><a href="#"   class="'.$aclass.'"  onclick="toggle_topic(this); return false;"          >'.$currenttext.'</a></td>';
				echo '<td class="td_right" >&nbsp;</td>';
			} else {
					echo '<td class="td_left"   >'.$currentsectionText.'</td>';	
				echo '<td   class="td_center"    ><a href="#"  class="'.$aclass.'" onclick="toggle_topic(this); return false;">'.$currenttext.' <span class="cps_large">'.html_to_text($thissection->summary).'</span></a>  </td>';
				echo '<td class="td_right" >&nbsp;</td>';
			}
			echo '</tr>';
			*/


			$trclass="cpsTest";
				$tr2="cpsFocusTest";
				echo '<TR><TD colspan="3" valign="bottom">';

 echo  '<table  width="100%"  dir="ltr"  cellpadding="0"  ><td width="7" class="block_headerleft"> </td><td  class="block_headermiddle" width="310"> </td><td width="7" class="block_headerright"></td></table>';
echo '</TD></TR>';

			echo '<tr class="'.$trclass.'"  onmouseover="this.className=\''.$tr2.'\';"      onmouseout="this.className=\''.$trclass.'\';"  >';
			if (empty($thissection->summary)) {
				$thissection->summary='';
//				echo '<td class="td_left"   ></td>';
				echo '<td  class="td_center"   colspan="2" ><a href="#"   id="toggle-'.$section.'"  class="'.$aclass.'"  onclick="toggle_topic(this); return false;"    title="'.$SectionTxt_title. '"      >'.$currenttext.'&nbsp;</a></td>';
				echo '<td class="td_right" >&nbsp;</td>';
			} else {
//					echo '<td class="td_left"  width="35">'.$currentsectionText.'</td>';	
				echo '<td   class="td_center"   colspan="2"   width="450"><a href="#"  class="'.$aclass.'"  id="toggle-'.$section.'"   onclick="toggle_topic(this); return false;" title="'.$SectionTxt_title. '"     >'.$currenttext.' <span class="cps_large"  id="sectionheader-'.$section.'">'.html_to_text($thissection->summary).$currentsectionText.'</span>&nbsp;</a></td>';
				echo '<td class="td_right" >&nbsp;</td>';
			}
			echo '</tr>';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			if($displaysection==0){
//	echo '<tr id="section-'.$section.'" class="section main'.$sectionstyle.' hid"   >';
	echo '<tr id="section-'.$section.'" class="section main'.$sectionstyle.$markerClass.'" >';
			}else{
			echo '<tr id="section-'.$section.'"   class="section main'.$sectionstyle.'">';
			}
///
		//	 echo '<tr id="section-'.$section.'" class="section main'.$sectionstyle.'">';

            
            echo '<td class="left side"  >'.$currenttext.$section.'</td>';

            echo '<td class="content"  width="450">';
            if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
                echo get_string('notavailable');
            } else {
                echo '<div class="summary">';
                $summaryformatoptions->noclean = true;
                echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

                if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    echo ' <a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
                         '<img src="'.$CFG->pixpath.'/t/edit.gif" alt="'.$streditsummary.'" /></a><br /><br />';
                }
                echo '</div>';

              echo '<div class="sectioncontent">';
				print_section($course, $thissection, $mods, $modnamesused);
	            echo '</div >';
                if (isediting($course->id)) {
                    print_section_add_menus($course, $section, $modnames);
                }
            }
            echo '</td>';

            echo '<td class="right side">';
            if ($displaysection == $section) {      // Show the zoom boxes
                echo '<a href="view.php?id='.$course->id.'&amp;topic=0#section-'.$section.'" title="'.$strshowalltopics.'">'.
                     '<img src="'.$CFG->pixpath.'/i/all.gif" alt="'.$strshowalltopics.'" /></a><br />';
            } else {
                $strshowonlytopic = get_string('showonlytopic', '', $section);
                echo '<a href="view.php?id='.$course->id.'&amp;topic='.$section.'" title="'.$strshowonlytopic.'">'.
                     '<img src="'.$CFG->pixpath.'/i/one.gif" alt="'.$strshowonlytopic.'" /></a><br />';
            }

            if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                if ($course->marker == $section) {  // Show the "light globe" on/off
                    echo '<a href="view.php?id='.$course->id.'&amp;marker=0&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strmarkedthistopic.'">'.
                         '<img src="'.$CFG->pixpath.'/i/marked.gif" alt="'.$strmarkedthistopic.'" /></a><br />';
                } else {
                    echo '<a href="view.php?id='.$course->id.'&amp;marker='.$section.'&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strmarkthistopic.'">'.
                         '<img src="'.$CFG->pixpath.'/i/marker.gif" alt="'.$strmarkthistopic.'" /></a><br />';
                }

                if ($thissection->visible) {        // Show the hide/show eye
                    echo '<a href="view.php?id='.$course->id.'&amp;hide='.$section.'&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strtopichide.'">'.
                         '<img src="'.$CFG->pixpath.'/i/hide.gif" alt="'.$strtopichide.'" /></a><br />';
                } else {
                    echo '<a href="view.php?id='.$course->id.'&amp;show='.$section.'&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strtopicshow.'">'.
                         '<img src="'.$CFG->pixpath.'/i/show.gif" alt="'.$strtopicshow.'" /></a><br />';
                }

                if ($section > 1) {                       // Add a arrow to move section up
                    echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=-1&amp;sesskey='.$USER->sesskey.'#section-'.($section-1).'" title="'.$strmoveup.'">'.
                         '<img src="'.$CFG->pixpath.'/t/up.gif" alt="'.$strmoveup.'" /></a><br />';
                }

                if ($section < $course->numsections) {    // Add a arrow to move section down
                    echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=1&amp;sesskey='.$USER->sesskey.'#section-'.($section+1).'" title="'.$strmovedown.'">'.
                         '<img src="'.$CFG->pixpath.'/t/down.gif" alt="'.$strmovedown.'" /></a><br />';
                }

            }

            echo '</td></tr>';
            echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';
        }

        $section++;
    }
    echo '</table>';

    if (!empty($sectionmenu)) {
        echo '<div align="center" class="jumpmenu">';
        echo popup_form($CFG->wwwroot.'/course/view.php?id='.$course->id.'&amp;', $sectionmenu,
                   'sectionmenu', '', get_string('jumpto'), '', '', true);
        echo '</div>';
    }

    print_container_end();
    echo '</td>';

            break;
            case 'right':
    // The right column
    if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $editing) {
        echo '<td style="width:'.$preferred_width_right.'px" id="right-column">';
        print_container_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
        print_container_end();
        echo '</td>';
    }

            break;
        }
    }
    echo '</tr></table>';
?>
