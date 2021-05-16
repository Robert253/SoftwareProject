<?php
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_MyStudentHelper/showcourses',
                   get_string('showcourses', 'block_MyStudentHelper'),
                   get_string('showcoursesdesc', 'block_MyStudentHelper'),
                   0));
}
