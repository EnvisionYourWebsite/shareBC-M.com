<?php

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sule\Kotakin\Libraries\UUID;
use Sule\Kotakin\Models\Option;

class OptionTableSeeder extends Seeder
{

    public function run()
    {
        Option::create(array(
            'name'  => 'uuid',
            'value' => UUID::v4()
        ));

        Option::create(array(
            'name'  => 'locale',
            'value' => 'en_US'
        ));

        Option::create(array(
            'name'  => 'ga_code',
            'value' => ''
        ));

        Option::create(array(
            'name'  => 'brand',
            'value' => 'Kotakin'
        ));

        Option::create(array(
            'name'  => 'mail_driver',
            'value' => 'mail'
        ));

        Option::create(array(
            'name'  => 'mail_host',
            'value' => ''
        ));

        Option::create(array(
            'name'  => 'mail_port',
            'value' => ''
        ));

        Option::create(array(
            'name'  => 'mail_from',
            'value' => serialize(array(
                'address' => 'noreply@host.com',
                'name'    => 'Kotakin'
            ))
        ));

        Option::create(array(
            'name'  => 'mail_username',
            'value' => ''
        ));

        Option::create(array(
            'name'  => 'mail_password',
            'value' => ''
        ));

        Option::create(array(
            'name'  => 'mail_reply_to',
            'value' => 'noreply@host.com'
        ));

        Option::create(array(
            'name'  => 'mail_encryption',
            'value' => 'tls'
        ));

        Option::create(array(
            'name'  => 'mail_sendmail',
            'value' => '/usr/bin'
        ));

        Option::create(array(
            'name'  => 'image_driver',
            'value' => 'gd'
        ));

        Option::create(array(
            'name'  => 'imagemagick_path',
            'value' => '/usr/bin'
        ));
    }

}
