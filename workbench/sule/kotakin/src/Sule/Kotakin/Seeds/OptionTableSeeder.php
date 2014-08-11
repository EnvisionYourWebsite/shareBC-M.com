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

use Sule\Kotakin\Libraries\UUID;
use Sule\Kotakin\Models\Option;
use Sule\Kotakin\Models\OptionExistsException;

class OptionTableSeeder extends Seeder
{

    public function run()
    {
        try {
            Option::create(array(
                'name'  => 'uuid',
                'value' => UUID::v4()
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'locale',
                'value' => 'en_US'
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'ga_code',
                'value' => ''
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'brand',
                'value' => 'Kotakin'
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_driver',
                'value' => 'mail'
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_host',
                'value' => ''
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_port',
                'value' => ''
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_from',
                'value' => serialize(array(
                    'address' => 'noreply@host.com',
                    'name'    => 'Kotakin'
                ))
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_username',
                'value' => ''
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_password',
                'value' => ''
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_reply_to',
                'value' => 'noreply@host.com'
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_encryption',
                'value' => 'tls'
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'mail_sendmail',
                'value' => '/usr/bin'
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'image_driver',
                'value' => 'gd'
            ));
        } catch (OptionExistsException $e) {}

        try {
            Option::create(array(
                'name'  => 'imagemagick_path',
                'value' => '/usr/bin'
            ));
        } catch (OptionExistsException $e) {}
    }

}
