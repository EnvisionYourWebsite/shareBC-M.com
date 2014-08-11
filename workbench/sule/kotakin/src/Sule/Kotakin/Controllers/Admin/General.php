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

class General extends Base
{
    /**
     * The validation rules.
     *
     * @var array
     */
    protected $validationRules = array(
        'brand'             => 'required',
        'mail_driver'       => 'required',
        'mail_from_address' => 'required',
        'mail_from_name'    => 'required',
        'mail_reply_to'     => 'required',
        'image_driver'      => 'required'
    );

    /**
     * The smtp validation rules.
     *
     * @var array
     */
    protected $smtpValidationRules = array(
        'mail_host'         => 'required',
        'mail_port'         => 'required',
        'mail_encryption'   => 'required',
        'mail_username'     => 'required',
        'mail_password'     => 'required'
    );

    /**
     * The sendmail validation rules.
     *
     * @var array
     */
    protected $sendmailValidationRules = array(
        'mail_sendmail' => 'required'
    );

    /**
     * The imagemagick validation rules.
     *
     * @var array
     */
    protected $imagemagickValidationRules = array(
        'imagemagick_path' => 'required'
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
     * Show user list page
     *
     * @return Illuminate\View\View
     */
	public function index()
	{
        $this->getPage()->setActiveMenu('preference');
        $this->getPage()->setAttribute('title', $this->getUtility()->t('General Preference | Admin'));

		return View::make('kotakin::admin_general', array(
			'page' => $this->getPage()
		));
	}

    /**
     * Save the general preferences
     *
     * @return Illuminate\Routing\Redirector
     */
    public function save()
    {
        $input       = Input::all();
        $mailDriver  = (isset($input['mail_driver'])) ? $input['mail_driver'] : 'mail';
        $imageDriver = (isset($input['image_driver'])) ? $input['image_driver'] : 'gd';

        if ($mailDriver == 'smtp') {
            $this->validationRules = array_merge($this->validationRules, $this->smtpValidationRules);
        }

        if ($mailDriver == 'sendmail') {
            $this->validationRules = array_merge($this->validationRules, $this->sendmailValidationRules);
        }

        if ($imageDriver == 'im') {
            $this->validationRules = array_merge($this->validationRules, $this->imagemagickValidationRules);
        }

        $validator = Validator::make($input, $this->validationRules);

        unset($input['_token']);
        unset($input['/admin/preference/general']);

        if ( ! $validator->fails()) {
            $input['mail_from'] = serialize(array(
                'address' => $input['mail_from_address'],
                'name'    => $input['mail_from_name']
            ));

            unset($input['mail_from_address']);
            unset($input['mail_from_name']);

            foreach ($input as $name => $value) {
                $option = $this->getKotakin()->getOption()->newQuery()
                                ->where('name', '=', $name)->first();

                if ($option) {
                    $option->setAttribute('value', $value);
                    $option->save();
                }

                unset($option);
            }

            Session::flash('success', $this->getUtility()->t('Preferences successfully saved.'));
            return Redirect::to('/admin/preference/general');
        }

        Session::flash('error', $this->getUtility()->t('An error occured during the saving process.'));

        return Redirect::to('/admin/preference/general')
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