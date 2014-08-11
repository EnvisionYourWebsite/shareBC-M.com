<?php
namespace Sule\Kotakin\Controllers\Admin;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use Sule\Kotakin\Controllers\Admin\Base;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

use Cartalyst\Sentry\Users\UserInterface;

class EmailTemplate extends Base
{
    /**
     * The validation rules.
     *
     * @var array
     */
    protected $validationRules = array(
        'id'            => 'required',
        'subject'       => 'required',
        'content_html'  => 'required',
        'content_plain' => 'required'
    );

    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry)
    {
        parent::__construct($kotakin, $sentry);

        if ( ! $this->inSuperAdminGroup($sentry->getUser())) {
            App::abort(404);
        }
    }

    /**
     * Show email templates page
     *
     * @return Illuminate\View\View
     */
	public function index()
	{
        $templateList = $this->getKotakin()->getEmailTemplate()->all();
        $templates = array();

        if (count($templateList) > 0) {
            foreach ($templateList as $index => $template) {
                $templates[$index] = $this->getPage()->getEmail()->newInstance();
                $templates[$index]->setEmail($template);
            }
        }

        $this->getPage()->setActiveMenu('preference');
        $this->getPage()->setAttribute('title', $this->getUtility()->t('Email Template | Admin'));

		return View::make('kotakin::admin_mail_template', array(
			'page' => $this->getPage(),
            'templates' => $templates
		));
	}

    /**
     * Save the email template
     *
     * @return Illuminate\Routing\Redirector
     */
    public function save()
    {
        $validator = Validator::make(Input::all(), $this->validationRules);

        if ( ! $validator->fails()) {
            $template = $this->getKotakin()->getEmailTemplate()->find($id = Input::get('id'));

            if ($template) {
                $template->fill(array(
                    'subject'       => Input::get('subject', ''),
                    'content_html'  => Input::get('content_html', ''),
                    'content_plain' => Input::get('content_plain', '')
                ));

                if ($template->save()) {
                    Session::flash('success', $this->getUtility()->t('Template successfully saved.'));
                    return Redirect::to('/admin/preference/mail_template');
                }
            }
        }

        Session::flash('error', $this->getUtility()->t('An error occured during the saving process.'));

        return Redirect::to('/admin/preference/mail_template')
                        ->withInput()->withErrors($validator->errors());
    }

    /**
     * Check if user is in super admin group
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return bool
     */
    protected function inSuperAdminGroup(UserInterface $user)
    {
        $allowed = false;

        try {
            $group = $this->getSentry()->getGroupProvider()->findByName('Super Admin');

            if ($user->inGroup($group))
                $allowed = true;

            unset($group);
        } catch (GroupNotFoundException $e) {}

        return $allowed;
    }

}