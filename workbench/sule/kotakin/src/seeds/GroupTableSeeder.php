<?php

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cartalyst\Sentry\Groups\Eloquent\Group;

class GroupTableSeeder extends Seeder
{

    public function run()
    {
        Group::create(array(
            'name' => 'Super Admin'
        ));

        Group::create(array(
            'name' => 'Admin'
        ));

        Group::create(array(
            'name' => 'User'
        ));
    }

}
