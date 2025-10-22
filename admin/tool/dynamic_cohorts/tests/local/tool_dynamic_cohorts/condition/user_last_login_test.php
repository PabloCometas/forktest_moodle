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

/**
 * Unit tests for user_last_login condition class.
 *
 * @package     tool_dynamic_cohorts
 * @copyright   2024 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers     \tool_dynamic_cohorts\local\tool_dynamic_cohorts\condition\user_last_login
 */
final class user_last_login_test extends \advanced_testcase {
    /**
     * Get condition instance for testing.
     *
     * @param array $configdata Config data to be set.
     * @return condition_base
     */
    protected function get_condition(array $configdata = []): condition_base {
        $condition = condition_base::get_instance(0, (object)[
            'classname' => '\tool_dynamic_cohorts\local\tool_dynamic_cohorts\condition\user_last_login',
        ]);
        $condition->set_config_data($configdata);

        return $condition;
    }

    /**
     * Test retrieving of config data.
     */
    public function test_retrieving_configdata(): void {
        $formdata = (object)[
            'operator' => 3,
            'time' => 777777,
            'period_value' => 1,
            'period_type' => 'hour',
        ];

        $actual = $this->get_condition()::retrieve_config_data($formdata);
        $expected = [
            'operator' => 3,
            'time' => 777777,
            'period_value' => 1,
            'period_type' => 'hour',
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test setting and getting config data.
     */
    public function test_set_and_get_configdata(): void {
        $condition = $this->get_condition([
            'operator' => 3,
            'time' => 777777,
            'period_value' => 1,
            'period_type' => 'hour',
        ]);

        $this->assertEquals(
            [
                'operator' => 3,
                'time' => 777777,
                'period_value' => 1,
                'period_type' => 'hour',
            ],
            $condition->get_config_data()
        );
    }

    /**
     * Test getting config description.
     */
    public function test_config_description(): void {
        $now = time();

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_EVER,
            'time' => $now,
        ]);
        $this->assertSame('Users who have logged in at least once', $condition->get_config_description());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_NEVER,
            'time' => $now,
        ]);
        $this->assertSame('Users who have never logged in', $condition->get_config_description());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_IN_LAST,
            'time' => $now,
            'period_value' => 1,
            'period_type' => 'weeks',
        ]);
        $this->assertSame('Users who have logged in in the last 1 weeks', $condition->get_config_description());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_BEFORE,
            'time' => $now,
            'period_value' => 1,
            'period_type' => 'weeks',
        ]);
        $this->assertSame('Users who logged in before ' . userdate($now), $condition->get_config_description());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_AFTER,
            'time' => $now,
            'period_value' => 1,
            'period_type' => 'weeks',
        ]);
        $this->assertSame('Users who logged in after ' . userdate($now), $condition->get_config_description());
    }

    /**
     * Test is broken.
     */
    public function test_is_broken(): void {
        $condition = condition_base::get_instance(0, (object)[
            'classname' => '\tool_dynamic_cohorts\local\tool_dynamic_cohorts\condition\user_last_login',
        ]);
        $this->assertFalse($condition->is_broken());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_IN_LAST,
            'period_value' => 0,
            'period_type' => 'weeks',
        ]);
        $this->assertTrue($condition->is_broken());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_IN_LAST,
            'period_value' => 1,
            'period_type' => 'weeks',
        ]);
        $this->assertFalse($condition->is_broken());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_AFTER,
            'period_value' => 1,
            'period_type' => 'weeks',
            'time' => 0,
        ]);
        $this->assertTrue($condition->is_broken());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_AFTER,
            'period_value' => 1,
            'period_type' => 'weeks',
        ]);
        $this->assertTrue($condition->is_broken());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_BEFORE,
            'period_value' => 1,
            'period_type' => 'weeks',
            'time' => 0,
        ]);
        $this->assertTrue($condition->is_broken());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_BEFORE,
            'period_value' => 1,
            'period_type' => 'weeks',
        ]);
        $this->assertTrue($condition->is_broken());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_AFTER,
            'period_value' => 1,
            'period_type' => 'weeks',
            'time' => 7777,
        ]);
        $this->assertFalse($condition->is_broken());

        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_BEFORE,
            'period_value' => 1,
            'period_type' => 'weeks',
            'time' => 777,
        ]);
        $this->assertFalse($condition->is_broken());
    }

    /**
     * Test getting correct SQL.
     */
    public function test_get_sql_data(): void {
        global $DB;

        $now = time();

        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $totalusers = $DB->count_records('user');
        $this->assertTrue($totalusers > 2);

        // No one logged in so far. Should get all users.
        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_NEVER,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $this->assertCount($totalusers, $DB->get_records_sql($sql, $result->get_params()));

        // No one logged in so far. Should get no users.
        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_EVER,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $this->assertCount(0, $DB->get_records_sql($sql, $result->get_params()));

        // Emulate login for user 1 a week ago.
        $DB->set_field('user', 'lastaccess', $now - WEEKSECS, ['id' => $user1->id]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(1, $actual);
        $this->assertSame($user1->id, reset($actual)->id);

        // Emulate login for user 2 now.
        $DB->set_field('user', 'lastaccess', $now, ['id' => $user2->id]);

        // In the last day. Should be user 2.
        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_IN_LAST,
            'period_value' => 1,
            'period_type' => 'day',
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(1, $actual);
        $this->assertSame($user2->id, reset($actual)->id);

        // In the last two weeks. Should be user 1 and 2.
        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_IN_LAST,
            'period_value' => 2,
            'period_type' => 'weeks',
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(2, $actual);

        // After now. Should be user 2.
        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_AFTER,
            'time' => $now - 5,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(1, $actual);
        $this->assertSame($user2->id, reset($actual)->id);

        // Before now. Should be user 1.
        $condition = $this->get_condition([
            'operator' => user_last_login::OPERATOR_BEFORE,
            'time' => $now,
        ]);
        $result = $condition->get_sql();
        $sql = "SELECT u.id FROM {user} u {$result->get_join()} WHERE {$result->get_where()}";
        $actual = $DB->get_records_sql($sql, $result->get_params());
        $this->assertCount(1, $actual);
        $this->assertSame($user1->id, reset($actual)->id);
    }

    /**
     * Test events that the condition is listening to.
     */
    public function test_get_events(): void {
        $this->assertEquals([
            '\core\event\user_loggedin',
        ], $this->get_condition()->get_events());
    }
}
