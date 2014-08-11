<?php

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sule\Kotakin\Models\EmailTemplate;

class EmailTemplateTableSeeder extends Seeder
{

    public function run()
    {
        EmailTemplate::create(array(
            'identifier'    => 'new_user',
            'subject'       => 'Welcome to {{ page.brand }}',
            'content_html'  => '<p>Dear {{ user.name }},</p>
<br/>
<p>Please use the following information to login:</p>
<br/>
<p><strong>URL:</strong> <a href="{{ user.permalink }}">Login</a></p>
<p><strong>Email:</strong> {{ user.email }}</p>
<p><strong>Password:</strong> {{ user.password }}</p>
<br/>
<p>Best regards.</p>',
            'content_plain' => 'Dear {{ user.name }}, 

Please use the following information to login: 

URL: {{ user.permalink }} 
Email: {{ user.email }} 
Password: {{ user.password }} 

Best regards',
            'note'          => 'Send to new created user.'
        ));

        EmailTemplate::create(array(
            'identifier'    => 'reset_password',
            'subject'       => 'Your Reset Password Instruction',
            'content_html'  => '<p>Dear {{ user.name }},</p>
<br/>
<p>Following is your reset password URL:</p>
<p><a href="{{ user.resetPasswordUrl }}">Reset Password</a></p>

<p>Best regards.</p>',
            'content_plain' => 'Dear {{ user.name }}, 

Following is your reset password URL: 
{{ user.resetPasswordUrl }}

Best regards.',
            'note'          => 'Send to user requesting password reset.'
        ));

        EmailTemplate::create(array(
            'identifier'    => 'new_files_uploaded',
            'subject'       => '{{ user.name }} has uploaded new files',
            'content_html'  => '<p>Dear {{ recipientUser.name }},</p>
<br/>
<p>{{ user.name }} has send you the following files:</p>
{% for file in files %}
- <a href="{{ url_to(\'/admin/%s\') | format(file.folder.permalink) }}">{{ file.title }}</a><br/>
{% endfor %}

<p>Best regards.</p>',
            'content_plain' => 'Dear {{ recipientUser.name }}, 

{{ user.name }} has send you the following files: 
{% for file in files %}
- {{ file.title }} ---> {{ url_to(\'/admin/%s\') | format(file.folder.permalink) }} 
{% endfor %}

Best regards.',
            'note'          => 'Send to user email recipients. Will be send after upload popup (modal) closed.'
        ));
    }

}
