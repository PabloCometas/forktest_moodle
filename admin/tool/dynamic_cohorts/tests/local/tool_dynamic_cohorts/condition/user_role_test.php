<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tool_dynamic_cohorts\local\tool_dynamic_cohorts\condition;

use tool_dynamic_cohorts\condition_base;
use context_system;
use context_coursecat;

/**
 * Unit tests for user_role condition class.
 *
 * @package     tool_dynamic_cohorts
 * @copyright   2024 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers     \tool_dynamic_cohorts\local\tool_dynamic_cohorts\condition\user_role
 */
final class user_role_test extends \advanced_testcase {
    /**
     * Get condition instance for testing.
     *
     * @param array $configdata Config data to be set.
     * @return condition_base
     */
    protected function get_condition(array $configdata = []): condition_base {
        $condition = condition_base::get_instance(0, (object)[
            'classname' => '\tool_dynamic_cohorts\local\tool_dynamic_cohorts\condition\user_role',
        ]);
        $condition->set_config_data($configdata);

        return $condition;
    }

    /**
     * Test retrieving of config data.
     */
    public function test_retrieving_configdata(): void {
        $formdata = (object)[
            'operator' => 1,
            'roleid' => 1,
            'contextlevel' => 3,
            'courseid' => 1,
            'categoryid' => 1,
            'includechildren' => 0,
            'sortorder' => 0,
        ];

        $actual = $this->get_condition()::retrieve_config_data($formdata);
        $expected = [
            'operator' => 1,
            'roleid' => 1,
            'contextlevel' => 3,
            'courseid' => 1,
            'categoryid' => 1,
            'includechildren' => 0,
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test setting and getting config data.
     */
    public function test_set_and_get_configdata(): void {
        $condition = $this->get_condition([
            'operator' => 1,
            'roleid' => 1,
            'contextlevel' => 3,
            'courseid' => 1,
            'categoryid' => 1,
            'includechildren' => 0,
        ]);

        $this->assertEquals(
            [
                'operator' => 1,
                'roleid' => 1,
                'contextlevel' => 3,
                'courseid' => 1,
                'categoryid' => 1,
                'includechildren' => 0,
            ],
            $condition->get_config_data()
        );
    }

    /**
     * Test getting config description.
     */
    public function test_config_description(): void {
        $this->resetAfterTest();

        $roleid = $this->getDataGenerator()->create_role();
        $roles = get_all_roles();
        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course();

        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_SYSTEM,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);

        $this->assertSame(
            'Users who have role "' . $roles[$roleid]->name . '" in system context',
            $condition->get_config_description(),
        );

        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_SYSTEM,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);

        $this->assertSame(
            'Users who do not have role "' . $roles[$roleid]->name . '" in system context',
            $condition->get_config_description(),
        );

        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);

        $this->assertSame(
            'Users who have role "' . $roles[$roleid]->name . '" in course ' . $course->fullname . ' (id ' . $course->id . ')',
            $condition->get_config_description(),
        );

        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);

        $this->assertSame(
            'Users who do not have role "' . $roles[$roleid]->name . '" in course ' . $course->fullname
            . ' (id ' . $course->id . ')',
            $condition->get_config_description(),
        );

        $condition = $this->get_condition([
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);

        $this->assertSame(
            'Users who have role "' . $roles[$roleid]->name . '" in course ' . $course->fullname . ' (id ' . $course->id . ')',
            $condition->get_config_description(),
        );

        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSECAT,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);

        $this->assertSame(
            'Users who have role "' . $roles[$roleid]->name . '" in category ' . $category->name . ' (id ' . $category->id . ') ',
            $condition->get_config_description(),
        );

        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSECAT,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);

        $this->assertSame(
            'Users who do not have role "' . $roles[$roleid]->name . '" in category ' . $category->name
            . ' (id ' . $category->id . ') ',
            $condition->get_config_description(),
        );

        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSECAT,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 1,
        ]);

        $this->assertSame(
            'Users who have role "' . $roles[$roleid]->name . '" in category ' . $category->name . ' (id ' . $category->id . ') '
            . 'including children (categories and courses)',
            $condition->get_config_description(),
        );

        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSECAT,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 1,
        ]);

        $this->assertSame(
            'Users who do not have role "' . $roles[$roleid]->name . '" in category ' . $category->name
            . ' (id ' . $category->id . ') '
            . 'including children (categories and courses)',
            $condition->get_config_description(),
        );
    }

    /**
     * Test is broken.
     */
    public function test_is_broken_and_broken_description(): void {
        $this->resetAfterTest();

        $roleid = $this->getDataGenerator()->create_role();
        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course();

        $condition = condition_base::get_instance(0, (object)[
            'classname' => '\tool_dynamic_cohorts\local\tool_dynamic_cohorts\condition\user_role',
        ]);

        $this->assertFalse($condition->is_broken());

        $condition = $this->get_condition([
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);
        $this->assertFalse($condition->is_broken());

        $condition = $this->get_condition([
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSECAT,
            'courseid' => $course->id,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);
        $this->assertFalse($condition->is_broken());

        // System context.
        $condition = $this->get_condition([
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_SYSTEM,
            'courseid' => 77777,
            'categoryid' => 77777,
            'includechildren' => 0,
        ]);
        $this->assertFalse($condition->is_broken());

        // Invalid course.
        $condition = $this->get_condition([
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => 77777,
            'categoryid' => 77777,
            'includechildren' => 0,
        ]);
        $this->assertTrue($condition->is_broken());
        $this->assertSame('Missing course', $condition->get_broken_description());

        // Invalid category.
        $condition = $this->get_condition([
            'roleid' => $roleid,
            'contextlevel' => CONTEXT_COURSECAT,
            'courseid' => 777777,
            'categoryid' => 777777,
            'includechildren' => 0,
        ]);
        $this->assertTrue($condition->is_broken());
        $this->assertSame('Missing course category', $condition->get_broken_description());

        // Invalid role.
        $condition = $this->get_condition([
            'roleid' => 777777,
            'contextlevel' => CONTEXT_COURSECAT,
            'courseid' => 777777,
            'categoryid' => 777777,
            'includechildren' => 0,
        ]);
        $this->assertTrue($condition->is_broken());
        $this->assertSame('Missing role', $condition->get_broken_description());
    }

    /**
     * Test getting correct SQL when operator "have role".
     */
    public function test_get_sql_data_operator_have_role(): void {
        global $DB;

        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $course2 = $this->getDataGenerator()->create_course(['category' => $category->id]);

        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);

        $manager = $this->getDataGenerator()->create_user();
        $catteacher = $this->getDataGenerator()->create_user();
        $courseteacher = $this->getDataGenerator()->create_user();
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id, context_system::instance()->id);
        $this->getDataGenerator()->role_assign($teacherrole->id, $catteacher->id, context_coursecat::instance($category->id)->id);
        $this->getDataGenerator()->enrol_user($courseteacher->id, $course1->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($student1->id, $course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($student2->id, $course1->id, $studentrole->id);

        // Manager role in system context. Should give us manager.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $managerrole->id,
            'contextlevel' => CONTEXT_SYSTEM,
            'includechildren' => 1,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(1, $actual);
        $this->assertSame($manager->id, reset($actual)->id);

        // Teacher role in system context. Should give us nothing.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_SYSTEM,
            'includechildren' => 1,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(0, $actual);

        // Manager role in category context. Should give us manager.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $managerrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(1, $actual);
        $this->assertSame($manager->id, reset($actual)->id);

        // Teacher role in category context without children. Should give us $catteacher.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(1, $actual);
        $this->assertSame($catteacher->id, reset($actual)->id);

        // Teacher role in category context with children. Should give us $catteacher and $courseteacher.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 1,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(2, $actual);
        $this->assertArrayHasKey($catteacher->id, $actual);
        $this->assertArrayHasKey($courseteacher->id, $actual);

        // Teacher role in course 1. Should give us $catteacher and $courseteacher.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course1->id,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(2, $actual);
        $this->assertArrayHasKey($catteacher->id, $actual);
        $this->assertArrayHasKey($courseteacher->id, $actual);

        // Teacher role in course 2. Should give us only $catteacher.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course2->id,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(1, $actual);
        $this->assertArrayHasKey($catteacher->id, $actual);

        // Student role in course 1. Should give us $student1 and $student2.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $studentrole->id,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course1->id,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(2, $actual);
        $this->assertArrayHasKey($student1->id, $actual);
        $this->assertArrayHasKey($student2->id, $actual);

        // Student role in course 2. Should give us nothing.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $studentrole->id,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course2->id,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(0, $actual);

        // Student role in category context with children. Should give us $student1 and $student2.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $studentrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 1,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(2, $actual);
        $this->assertArrayHasKey($student1->id, $actual);
        $this->assertArrayHasKey($student2->id, $actual);

        // Student role in category context without children. Should give us nothing.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_HAVE_ROLE,
            'roleid' => $studentrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(0, $actual);
    }

    /**
     * Test getting correct SQL when operator "have role".
     */
    public function test_get_sql_data_operator_do_not_have_role(): void {
        global $DB;

        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $course2 = $this->getDataGenerator()->create_course(['category' => $category->id]);

        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);

        $manager = $this->getDataGenerator()->create_user();
        $catteacher = $this->getDataGenerator()->create_user();
        $courseteacher = $this->getDataGenerator()->create_user();
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id, context_system::instance()->id);
        $this->getDataGenerator()->role_assign($teacherrole->id, $catteacher->id, context_coursecat::instance($category->id)->id);
        $this->getDataGenerator()->enrol_user($courseteacher->id, $course1->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($student1->id, $course1->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($student2->id, $course1->id, $studentrole->id);

        $totalusers = $DB->count_records('user');
        $this->assertTrue($totalusers > 5);

        // Manager role in system context. Should give us everyone except manager.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $managerrole->id,
            'contextlevel' => CONTEXT_SYSTEM,
            'includechildren' => 1,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers - 1, $actual);
        $this->assertArrayNotHasKey($manager->id, $actual);

        // Teacher role in system context. Should give us everyone.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_SYSTEM,
            'includechildren' => 1,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers, $actual);

        // Manager role in category context. Should give us everyone except manager.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $managerrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers - 1, $actual);
        $this->assertArrayNotHasKey($manager->id, $actual);

        // Teacher role in category context without children. Should give us everyone except $catteacher.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers - 1, $actual);
        $this->assertArrayNotHasKey($catteacher->id, $actual);

        // Teacher role in category context with children. Should give us everyone except $catteacher and $courseteacher.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 1,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers - 2, $actual);
        $this->assertArrayNotHasKey($catteacher->id, $actual);
        $this->assertArrayNotHasKey($courseteacher->id, $actual);

        // Teacher role in course 1. Should give us everyone except $catteacher and $courseteacher.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course1->id,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers - 2, $actual);
        $this->assertArrayNotHasKey($catteacher->id, $actual);
        $this->assertArrayNotHasKey($courseteacher->id, $actual);

        // Teacher role in course 2. Should give us everyone except $catteacher.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $teacherrole->id,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course2->id,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers - 1, $actual);
        $this->assertArrayNotHasKey($catteacher->id, $actual);

        // Student role in course 1. Should give us everyone except $student1 and $student2.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $studentrole->id,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course1->id,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers - 2, $actual);
        $this->assertArrayNotHasKey($student1->id, $actual);
        $this->assertArrayNotHasKey($student2->id, $actual);

        // Student role in course 2. Should give us everyone.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $studentrole->id,
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $course2->id,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers, $actual);

        // Student role in category context with children. Should give us everyone except $student1 and $student2.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $studentrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 1,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers - 2, $actual);
        $this->assertArrayNotHasKey($student1->id, $actual);
        $this->assertArrayNotHasKey($student2->id, $actual);

        // Student role in category context without children. Should give us everyone.
        $condition = $this->get_condition([
            'operator' => user_role::OPERATOR_DO_NOT_HAVE_ROLE,
            'roleid' => $studentrole->id,
            'contextlevel' => CONTEXT_COURSECAT,
            'categoryid' => $category->id,
            'includechildren' => 0,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount($totalusers, $actual);
    }

    /**
     * Test events that the condition is listening to.
     */
    public function test_get_events(): void {
        $this->assertEquals([
            'core\event\role_assigned',
            'core\event\role_unassigned',
        ], $this->get_condition()->get_events());
    }
}
