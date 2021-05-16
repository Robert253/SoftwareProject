<?php
// This file is part of Moodle Course Rollover Plugin
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package     local_knowledgebase
 * @author      Robert Foster 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/knowledgebase/classes/form/edit.php');

global $DB;

$PAGE->set_url(new moodle_url('/local/knowledgebase/edit.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit');


// We want to display our form.
$mform = new edit();



if ($mform->is_cancelled()) {
    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/knowledgebase/show.php', 'You cancelled the message form');


} else if ($fromform = $mform->get_data()) {

    // Insert the data into our database table.
    $recordtoinsert = new stdClass();
    $recordtoinsert->messagetext = $fromform->messagetext;
    $recordtoinsert->messagebody = $fromform->messagebody;
    $recordtoinsert->messagetype = $fromform->messagetype;

    $DB->insert_record('local_knowledgebase', $recordtoinsert);

    // Go back to manage.php page
    redirect($CFG->wwwroot . '/local/knowledgebase/show.php', 'You created a message with title ' . $fromform->messagetext);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();

