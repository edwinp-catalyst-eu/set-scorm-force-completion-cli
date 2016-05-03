<?php

// SCORM Force completion CLI script
define('CLI_SCRIPT', 1);
define('FORCECOMPLETED_NO', 0);

// Run from /admin/cli dir
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/scorm/locallib.php');

$courses = array_keys($DB->get_records_menu('course', array(), 'id', 'id'));
$sql = "SELECT s.id,
               s.forcecompleted,
               cm.id AS cmid
          FROM {scorm} s
          JOIN {course_modules} cm ON (cm.instance = s.id AND cm.module = 14)
         WHERE s.course = ?";

foreach ($courses as $course) {
    $params = array($course);
    if ($scormmodules = $DB->get_records_sql($sql, $params)) {
        foreach ($scormmodules as $scormmodule) {
            $scormupdate = new stdClass();
            $scormupdate->id = $scormmodule->id;
            $scormupdate->forcecompleted = FORCECOMPLETED_NO;
            if ($DB->update_record('scorm', $scormupdate)) {
                mtrace('SCORM ID ' . $scormupdate->id .
                        ' (Course module id: ' . $scormmodule->cmid .
                        ') forcecompleted value set to zero.');
            } else {
                mtrace('I haz probs');
            }
        }
    }
    rebuild_course_cache($course, true);
    mtrace('Course ID ' . $course . ' cache rebuilt.');
}
mtrace('fin');
