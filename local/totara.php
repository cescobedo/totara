<?php
/**
 * Moodle - Modular Object-Oriented Dynamic Learning Environment
 *          http://moodle.org
 * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright  Totara Learning Solutions Limited
 * @author     Jonathan Newman <jonathan.newman@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package    totara
 * @subpackage local
 *
 * Functions should all start with the totara_ prefix.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}


/**
 * Save a notification message for displaying on the subsequent page view
 *
 * Optionally supply a url for redirecting to before displaying the message.
 *
 * @param   string  $message    Message to display
 * @param   string  $redirect   Url to redirect to (optional)
 * @return  void
 */
function totara_set_notification($message, $redirect = null) {
    global $SESSION;

    if (!is_array($SESSION->totara_notifications)) {
        $SESSION->totara_notifications = array();
    }

    $SESSION->totara_notifications[] = $message;

    if ($redirect !== null) {
        redirect($redirect);
        exit();
    }
}


/*
 * Return an array containing any notifications in $SESSION
 *
 * Should be called in the theme's header
 *
 * @return  array
 */
function totara_get_notifications() {
    global $SESSION;

    // Check if any notifications have yet to be displayed
    if (empty($SESSION->totara_notifications)) {
        return array();
    }

    // Get notifications and reset session
    $notifications = $SESSION->totara_notifications;
    $SESSION->totara_notifications = array();

    return $notifications;
}


/**
 * resets the customised front page blocks.  designed to be called from local_postinst
 *
 * @return bool
 */
function totara_reset_frontpage_blocks() {
    global $CFG;

    // first delete pre-set ones
    execute_sql('DELETE FROM ' . $CFG->prefix . 'block_instance
        WHERE pageid = ' . SITEID . "
        AND pagetype = 'course-view'"
    );

    // build new block array
    $blocks = array(
        (object)array(
            'blockid'  =>  get_field('block', 'id', 'name', 'admin_tree'),
            'pageid'   => SITEID,
            'pagetype' => 'course-view',
            'position' => 'l',
            'weight'   => 1,
            'visible'  => 1,
            'configdata' => '',
        ),
        (object)array(
            'blockid'  =>  get_field('block', 'id', 'name', 'messages'),
            'pageid'   => SITEID,
            'pagetype' => 'course-view',
            'position' => 'r',
            'weight'   => 1,
            'visible'  => 1,
            'configdata' => '',
        ),
        (object)array(
            'blockid'  =>  get_field('block', 'id', 'name', 'calendar_month'),
            'pageid'   => SITEID,
            'pagetype' => 'course-view',
            'position' => 'r',
            'weight'   => 2,
            'visible'  => 1,
            'configdata' => '',
        ),
        (object)array(
            'blockid'  =>  get_field('block', 'id', 'name', 'guides'),
            'pageid'   => SITEID,
            'pagetype' => 'course-view',
            'position' => 'r',
            'weight'   => 3,
            'visible'  => 1,
            'configdata' => '',
        ),
    );

    // insert blocks
    foreach ($blocks as $b) {
        insert_record('block_instance', $b);
    }

    return 1;

}

/**
 * adds guides block on the site admin pages.  designed to be called from local_postinst
 *
 * @return bool
 */
function totara_add_guide_block_to_adminpages() {
    global $CFG;

        $b = (object)array(
            'blockid'  =>  get_field('block', 'id', 'name', 'guides'),
            'pageid'   => 0,
            'pagetype' => 'admin',
            'position' => 'r',
            'weight'   => 0,
            'visible'  => 1,
            'configdata' => '',
        );
    insert_record('block_instance', $b);

}

/**
* print out the Totara My Learning nav section
*/
function totara_print_my_learning_nav($return=false) {
    global $CFG, $USER;

    $usercontext = get_context_instance(CONTEXT_USER, $USER->id);

    $returnstr = '
        <table>
    ';
    if (has_capability('moodle/local:idpviewownlist', $usercontext)) {
        $returnstr .= '
            <tr>
                <td align="left">
                    <a href="'.$CFG->wwwroot.'/idp/index.php" title="'.get_string('developmentplan','local').'">
                    <img src="'. $CFG->wwwroot.'/pix/i/idp.png" width="32" height="32" /></a>
                </td>
                <td align="left" valign="center">
                    <span style="font-size: small"><a href="'.$CFG->wwwroot.'/idp/index.php">' . get_string('developmentplan', 'local') . '</a></span>
                </td>
            </tr>
        ';
    }
    $returnstr .= '
        <tr>
            <td align="left">
                <a href="'.$CFG->wwwroot.'/blocks/facetoface/mysignups.php" title=""><img src="'.$CFG->wwwroot.'/pix/i/bookings.png" width="32" height="32" /></a>
            </td>
            <td align="left" valign="center">
                <span style="font-size: small"><a href="'.$CFG->wwwroot.'/my/bookings.php?id='.$USER->id.'">'.get_string('bookings','local').'</a></span>
            </td>
        </tr>';
    if(get_config(NULL, 'idp_showlearnrec')==2){
        $returnstr .= '<tr>
            <td align="left">
                <a href="'.$CFG->wwwroot.'/my/records.php?id='.$USER->id.'" title=""><img src="' . $CFG->wwwroot . '/pix/i/rol.png" width="32" height="32" /></a>
            </td>
            <td align="left" valign="center">
                <span style="font-size: small"><a href="'.$CFG->wwwroot.'/my/records.php?id='.$USER->id.'">'.get_string('recordoflearning','local').'</a></span>
            </td>
        </tr>';
    }
    $returnstr .= '</table>';

    if ($return) {
        return $returnstr;
    }
    echo $returnstr;
}

/**
* print out the Totara My Team nav section
*/
function totara_print_my_team_nav($return=false) {
    global $CFG, $USER;

    $returnstr = '';

    $managerroleid = get_field('role','id','shortname','manager');

    // return users with this user as manager
    $teammembers = totara_get_staff();

    if (!empty($teammembers) && count($teammembers) > 0) {
        $returnstr = '
         <table>
             <tr>
                 <td align="left">
                     <a href="'.$CFG->wwwroot.'/my/team.php"><img src="'.$CFG->wwwroot.'/pix/i/teammembers.png" width="32" height="32" alt="'.get_string('viewmyteam','local').'" /></a>
                 </td>
                 <td align="left">
                     <a href="'.$CFG->wwwroot.'/my/team.php">'.get_string('viewmyteam','local').'</a><br />('.count($teammembers).' staff)
                 </td>
             </tr>
         </table>
        ';
    }
    return $returnstr;

}

function totara_print_report_manager($return=false) {
    global $CFG,$USER;
    require_once($CFG->dirroot.'/local/reportbuilder/lib.php');
    $reports = get_records('report_builder','','','fullname');
    if (!is_array($reports)){
        $reports = array();
    }
    $context = get_context_instance(CONTEXT_SYSTEM);

    $rows = array();
    foreach ($reports as $report) {
        // show reports user has permission to view, that are not hidden
        if(reportbuilder::is_capable($report->id) && !$report->hidden) {
            $viewurl = ($report->embeddedurl === null) ? $CFG->wwwroot .
                '/local/reportbuilder/report.php?id='.$report->id :
                $report->embeddedurl;
            $row = '
            <tr>
                <td align="left">
                    <a href="'.$CFG->wwwroot.'/local/reportbuilder/report.php?id='.$report->id.'" title="'.$report->fullname.'">
                    <img src="'.$CFG->wwwroot.'/pix/i/reports.png" width="32" height="32" /></a>
                </td>
                <td align="left" valign="center">
                    <span style="font-size: small;"><a href="'.$viewurl.'">'.$report->fullname.'</a>
                ';


            // if admin with edit mode on show settings button too
            if(has_capability('moodle/local:admin',$context) && isset($USER->editing) && $USER->editing) {
                $row .= '<a href="'.$CFG->wwwroot.'/local/reportbuilder/settings.php?id='.$report->id.'">'.
                    '<img src="'.$CFG->pixpath.'/t/edit.gif" alt="'.get_string('settings','local').'"></a>';
            }
            $row .= '</span>
                </td>
            </tr>
            ';
            $rows[] = $row;
        }
    }

    // if there are any rows print them
    $returnstr = '';
    if(count($rows)>0) {
        $returnstr = '<table>';
        $returnstr .= implode("\n",$rows);
        $returnstr .= '</table>';
    }

    if ($return) {
        return $returnstr;
    }
    echo $returnstr;
}

function totara_print_my_courses() {
    global $CFG,$USER;
    $content = '';
    $courses = completion_info::get_all_courses($USER->id, 10);

    if ($courses) {
        $content .= '<table class="centerblock">
            <tr><th class="course">'.get_string('course').'</th>'.
            '<th class="status">'.get_string('status').'</th>'.
            '<th class="enroldate">'.get_string('enrolled', 'local').'</th>'.
            '<th class="startdate">'.get_string('started','local').'</th>'.
            '<th class="completeddate">'.get_string('completed','local').'</th></tr>';

        foreach($courses as $course) {
            $id = $course->course;
            $name = $course->name;
            $enrolled = $course->timeenrolled;
            $completed = $course->timecompleted;

            $statusstring = completion_completion::get_status($course);
            $status = get_string($statusstring, 'completion');

            $starteddate = '';
            if ($course->timestarted != 0) {
                $starteddate = userdate($course->timestarted, '%e %b %y');
            }
            $enroldate = isset($enrolled) && $enrolled != 0 ? userdate($enrolled, '%e %b %y') : null;
            $completeddate = isset($completed) && $completed != 0 ? userdate($completed, '%e %b %y') : null;
            $content .= "<tr><td class=\"course\"><a href=\"{$CFG->wwwroot}/course/view.php?id={$id}\" title=\"$name\">$name</a></td>";
            $content .=     "<td class=\"status\"><span class=\"completion-$statusstring\" title=\"$status\"></span></td><td class=\"enroldate\">$enroldate</td>";
            $content .=     "<td class=\"startdate\">$starteddate</td><td class=\"completeddate\">$completeddate</td></tr>\n";
        }
        $content .= "</table>\n";
        $content .= '<div class="allmycourses"><a href="'.$CFG->wwwroot.'/my/coursecompletions.php?id='.$USER->id.'">'.get_string('allmycourses','local').'</a></div>';
    }

    if (empty($content)) {
        $content = get_string('notenrolled','local');
    }
    echo '<div class="mycourses">';
    echo '<div class="header"><div class="title"><h2>'.get_string('mycourses','local').'</h2></div></div><div class="content">';
    echo $content;
    echo '</div></div>';
}

/**
* helper function to return a user's data stored in a given profile field
*
* @param int $userid id of the user whose user profile field value will be returned
* @param string $fieldshortname the shortname of the field to be returned
*
* @return string the field value
*/
function totara_print_user_profile_field($userid=null, $fieldshortname=null) {
    $sql = "SELECT uid.data
            FROM mdl_user_info_data uid
            JOIN mdl_user_info_field uif
              ON uif.id=uid.fieldid
            WHERE uif.shortname='{$fieldshortname}'
            AND uid.userid='{$userid}'
            ";
    return get_field_sql($sql);
}

/**
 * @param int $userid ID of user
 * @param int $managerid ID of a potential manager to check
 * @return boolean true if user $userid is managed by user $managerid
 *
 * If managerid is not set, uses the current user
**/
function totara_is_manager($userid, $managerid=null) {
    global $USER;
    if(!isset($managerid)) {
        $managerid = $USER->id;
    }
    $context = get_context_instance(CONTEXT_USER,$userid);
    $managerroleid = get_field('role','id','shortname','manager');

    // if a record exists, they are a manager to the user
    if(get_record('role_assignments','roleid',$managerroleid,'contextid',$context->id, 'userid', $managerid)) {
        return true;
    } else {
        return false;
    }

}

/**
 * @param int $userid ID of a user to get the staff of
 * @return array Array of userids of staff who are managed by user $userid, or false if none
 *
 * if $userid is not set, returns staff of current user
**/
function totara_get_staff($userid=null) {
    global $USER,$CFG;

    if(!isset($userid)) {
        $userid = $USER->id;
    }
    $managerroleid = get_field('role','id','shortname','manager');

    // return users with this user as manager
    $sql = "SELECT c.instanceid as userid
        FROM {$CFG->prefix}role_assignments ra
        LEFT JOIN {$CFG->prefix}context c
          ON c.id=ra.contextid
        JOIN {$CFG->prefix}user u
          ON u.id=c.instanceid
        WHERE ra.roleid={$managerroleid}
          AND ra.userid={$userid}
          AND c.contextlevel=30";

    // no matches
    if(!$res = get_records_sql($sql)) {
        return false;
    }

    $staff = array();
    if(is_array($res)) {
        foreach($res as $record) {
            $staff[] = $record->userid;
        }
    }

    return $staff;
}

/**
* hacked page lib that gets used on any page that doesn't have one.
* really just exists to fulfil requirements and allow stickyblocks
*/
class totara_page_class_hack extends page_base {
    function get_type() {
        return 'Totara';
    }

    function user_is_editing() {
        if (defined('ADMIN_STICKYBLOCKS')) {
            return true;
        }
        return false;
    }

    function blocks_default_position() {
        return BLOCK_POS_LEFT; // avoid getting the admin block
    }

    function blocks_get_positions() {
        return array(BLOCK_POS_LEFT, BLOCK_POS_RIGHT);
    }

    function blocks_move_position(&$instance, $move) {
        if($instance->position == BLOCK_POS_LEFT && $move == BLOCK_MOVE_RIGHT) {
            return BLOCK_POS_RIGHT;
        } else if ($instance->position == BLOCK_POS_RIGHT && $move == BLOCK_MOVE_LEFT) {
            return BLOCK_POS_LEFT;
        }
        return $instance->position;
    }

    function get_id() {
        return 0;
    }

    function user_allowed_editing() {
        return $this->user_is_editing();
    }
    function url_get_path() {
        global $CFG;
        if (defined('ADMIN_STICKYBLOCKS')) {
            return $CFG->wwwroot . '/admin/stickyblocks.php';
        }
        return '';
    }

    function url_get_parameters() {
        if (defined('ADMIN_STICKYBLOCKS')) {
            return array('pt' => 'Totara');
        }
    }

    function print_header($title, $morenavlinks=NULL) {
        $nav = build_navigation($morenavlinks);
        print_header($title, $title, $nav);
    }
}
page_map_class('Totara', 'totara_page_class_hack');

/**
* local footer hook. nothing yet but this could print right blocks
*/
function totara_local_footer_hook() {
    if (!defined('Totara')) {
        return;
    }
    echo '</td></tr></table>';
}

/**
 * resets the customised sticky blocks settings.  designed to be called from /local/db/upgrade.php
 *
 * @param bool   $remove   dictates whether to remove existing sticky blocks
 * @param string $path     path to the stickyblocks definition file
 *
 * @return bool
 */
function totara_reset_stickyblocks($remove=false, $path='local') {
    global $CFG;

    if ($remove) {
        // remove existing.  we only remove from the custom pagetypes format_learning and my-collaboration
        delete_records('block_pinned', 'pagetype', 'format_learning');
    }

    // get the sticky block object
    $filepath = $CFG->dirroot .  '/' . $path . '/stickyblocks.php';
    if (!file_exists($filepath)) {
        debugging("Local caps reassignment called with invalid path $path");
        return false;
    }
    require_once($filepath);
    $blocks = get_custom_stickyblocks();
    if (!isset($blocks)) {
        return true; // nothing to do.
    }

    foreach($blocks as $block) {

        // check for existing record
        $id = get_field('block_pinned', 'id', 'blockid', $block->blockid, 'pagetype', $block->pagetype);

        if (empty($id)) {
            // if not there then insert a new record
            insert_record('block_pinned', $block);
        } else {
            // if there then just update the relevant settings
            $block->id = $id;
            update_record('block_pinned', $block);
        }
    }

    return true;

}