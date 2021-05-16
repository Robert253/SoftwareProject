<?php
// This file is part of Moodle - http://moodle.org/
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
 * This file contains a renderer for various parts of the Diary module.
 *
 * @package   mod_diary
 * @copyright 2019 onwards AL Rachels drachels@drachels.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the diary module.
 *
 * @package mod_diary
 * @copyright 2019 onwards AL Rachels drachels@drachels.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_diary_renderer extends plugin_renderer_base {

    /**
     * Rendering diary files.
     *
     * @var int $diary
     */
    private $diary;

    /**
     * Initialize internal objects.
     *
     * @param int $cm
     */
    public function init($cm) {
        $this->cm = $cm;
    }

    /**
     * Return introduction box and content for the page generated by view.php file.
     *
     * @param int $diary Diary activity this description is for.
     * @param int $cm Course module this description is for.
     * @return $output Return a box with the description in it.
     */
    public function introduction($diary, $cm) {
        $output = '';

        if (trim($diary->intro)) {
            $output .= $this->box_start('generalbox boxaligncenter', 'intro');
            $output .= format_module_intro('diary', $diary, $cm->id);
            $output .= $this->box_end();
        }
        return $output;
    }

    /**
     * Print the toolbar above the entries on the page generated by view.php.
     *
     * @param int $firstkey Id of the entry for the current sort order.
     * @return $output Return all the buttons that are part of the toolbar.
     */
    public function toolbar($firstkey) {
        // 20201003 Changed toolbar code to $output instead of html_writer::alist.
        $options = array();
        $options['id'] = $this->cm->id;
        $output = ' ';
        // Print export to .csv file toolbutton.
        $options['action'] = 'download';
        $url = new moodle_url('/mod/diary/view.php', $options);
        $output .= html_writer::link($url, $this->pix_icon('i/export', get_string('csvexport', 'diary')), array(
            'class' => 'toolbutton'
        ));

        // Print reload toolbutton.
        $options['action'] = 'reload';
        $url = new moodle_url('/mod/diary/view.php', $options);
        $output .= html_writer::link($url, $this->pix_icon('t/reload', get_string('reload', 'diary')), array(
            'class' => 'toolbutton'
        ));

        // Print edit entry toolbutton.
        $options['action'] = 'editentry';
        $options['firstkey'] = $firstkey;
        $url = new moodle_url('/mod/diary/edit.php', $options);
        $output .= html_writer::link($url, $this->pix_icon('i/edit', get_string('edittopoflist', 'diary')), array(
            'class' => 'toolbutton'
        ));

        // Print sort to first entry toolbutton.
        $options['action'] = 'sortfirstentry';
        $options['firstkey'] = $firstkey;
        $url = new moodle_url('/mod/diary/view.php', $options);
        $output .= html_writer::link($url, $this->pix_icon('t/left', get_string('sortfirstentry', 'diary')), array(
            'class' => 'toolbutton'
        ));

        // Print lowest grade entry toolbutton.
        $options['action'] = 'lowestgradeentry';
        $options['firstkey'] = $firstkey;
        $url = new moodle_url('/mod/diary/view.php', $options);
        $output .= html_writer::link($url, $this->pix_icon('t/down', get_string('lowestgradeentry', 'diary')), array(
            'class' => 'toolbutton'
        ));

        // Print highest grade entry toolbutton.
        $options['action'] = 'highestgradeentry';
        $options['firstkey'] = $firstkey;
        $url = new moodle_url('/mod/diary/view.php', $options);
        $output .= html_writer::link($url, $this->pix_icon('t/up', get_string('highestgradeentry', 'diary')), array(
            'class' => 'toolbutton'
        ));

        // Print latest modified entry toolbutton.
        $options['action'] = 'latestmodifiedentry';
        $options['firstkey'] = $firstkey;
        $url = new moodle_url('/mod/diary/view.php', $options);
        $output .= html_writer::link($url, $this->pix_icon('t/right', get_string('latestmodifiedentry', 'diary')), array(
            'class' => 'toolbutton'
        ));

        $firstkey = '';
        // Return all available toolbuttons.
        return $output;
    }

    /**
     * Returns HTML for a diary inaccessible message.
     * Added 20161002
     *
     * @param string $message
     * @return <type>
     */
    public function diary_inaccessible($message) {
        global $CFG;
        $output = $this->output->box_start('generalbox boxaligncenter');
        $output .= $this->output->box_start('center');
        $output .= (get_string('notavailable', 'diary'));
        $output .= $message;
        $output .= $this->output->box('<a href="'
            .$CFG->wwwroot.'/course/view.php?id='
            .$this->page->course->id.'">'
            .get_string('returnto', 'diary', format_string($this->page->course->fullname, true))
            .'</a>', 'diarybutton standardbutton');
        $output .= $this->output->box_end();
        $output .= $this->output->box_end();
        return $output;
    }

    /**
     * Print the teacher feedback.
     * This renders the teacher feedback on the view.php page.
     *
     * @param object $course
     * @param object $entry
     * @param object $grades
     */
    public function diary_print_feedback($course, $entry, $grades) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/lib/gradelib.php');

        if (! $teacher = $DB->get_record('user', array(
            'id' => $entry->teacher
        ))) {
            throw new moodle_exception(get_string('generalerror', 'diary'));
        }

        echo '<table class="feedbackbox">';

        echo '<tr>';
        echo '<td class="left picture">';
        echo $this->output->user_picture($teacher, array(
            'courseid' => $course->id,
            'alttext' => true
        ));
        echo '</td>';
        echo '<td class="entryheader">';
        echo '<span class="author">' . fullname($teacher) . '</span>';
        echo '&nbsp;&nbsp;<span class="time">' . userdate($entry->timemarked) . '</span>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="left side">&nbsp;</td>';
        echo '<td class="entrycontent">';

        echo '<div class="grade">';

        // Gradebook preference.
        $gradinginfo = grade_get_grades($course->id, 'mod', 'diary', $entry->diary, array(
            $entry->userid
        ));

        // My preference.
        if (! empty($grades)) {
            echo get_string('grade') . ': ';
            echo $grades . '/' . number_format($gradinginfo->items[0]->grademax, 2);
        } else {
            print_string('nograde');
        }
        echo '</div>';

        // Feedback text.
        echo format_text($entry->entrycomment, FORMAT_PLAIN);
        echo '</td></tr></table>';
    }
}