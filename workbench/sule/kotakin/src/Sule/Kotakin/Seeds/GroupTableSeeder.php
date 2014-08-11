<?php
namespace Sule\Kotakin\Seeds;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Seeder;

use Cartalyst\Sentry\Groups\Eloquent\Group;
use Cartalyst\Sentry\Groups\GroupExistsException;

class GroupTableSeeder extends Seeder
{

    public function run()
    {
        try {
            Group::create(array(
                'name' => 'Super Admin'
            ));
        } catch (GroupExistsException $e) {}

        try {
            Group::create(array(
                'name' => 'Admin'
            ));
        } catch (GroupExistsException $e) {}

        try {
            Group::create(array(
                'name' => 'User'
            ));
        } catch (GroupExistsException $e) {}
    }

}
