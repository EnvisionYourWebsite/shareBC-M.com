<?php
namespace Sule\Kotakin\Controllers;

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

use Sule\Kotakin\Controllers\BaseUser;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

use Cartalyst\Sentry\Users\UserNotFoundException;

class User extends BaseUser
{
    /**
     * The validation rules.
     *
     * @var array
     */
    protected $validationRules = array(
        'name'           => 'required',
        'email'          => 'required|email|email_available',
        'date_format'    => 'required'
    );

    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin $kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @param string $slug
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry, $slug)
    {
        parent::__construct($kotakin, $sentry, $slug);
    }

    /**
     * Show user edit page
     *
     * @return Illuminate\View\View
     */
    public function edit()
    {
        $template = $this->getKotakin()->getTemplate()->getUser()->newInstance();
        $template->setKotakin($this->getKotakin());
        $template->setSentry($this->getSentry());
        $template->setUser($this->getUser());

        $this->getPage()->setActiveMenu('preference');

        $this->getPage()->setActiveMenu('me');
        $this->getPage()->setAttribute('title', sprintf($this->getUtility()->t('Your Profile | %s'), $this->getPage()->getAttribute('brand')));

        return View::make('kotakin::user_form', array(
            'page' => $this->getPage(),
            'user' => $template
        ));
    }

    /**
     * Save user info
     *
     * @return Illuminate\Routing\Redirector
     */
    public function save()
    {
        $userProvider = $this->getSentry()->getUserProvider();

        $user = $this->getUser();
        $userId = $user->getId();

        // Validate that email address is still available
        Validator::extend('email_available', function($attribute, $value, $parameters) use ($userProvider, $userId) {
            try {
                $user = $userProvider->findByLogin($value);
            } catch (UserNotFoundException $e) {
                return true;
            }

            if ($user->getId() == $userId)
                return true;
            
            unset($user);

            return false;
        });

        $password = Input::get('password');

        if ( ! empty($password)) {
            $this->validationRules['password']         = 'required';
            $this->validationRules['confirm_password'] = 'required|same:password';
        }

        $validator = Validator::make(Input::all(), $this->validationRules);

        if ( ! $validator->fails()) {
            // Save user
            $userData = array(
                'email' => Input::get('email')
            );

            if ( ! empty($password)) {
                $userData['password'] = $password;
            }

            $user->fill($userData);
            $user->save();

            // Save user profile
            $displayName = trim(Input::get('name'));
            $names       = explode(' ', $displayName);
            $firstName   = trim($names[0]);
            $lastName    = '';

            if (count($names) > 1) {
                $lastName = trim(str_replace($firstName, '', $displayName));
            }
            
            $profileData = array(
                'user_id'      => $user->getId(),
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'display_name' => $displayName,
                'phone'        => '',
                'date_format'  => Input::get('date_format')
            );

            $user->profile;
            $user->profile->fill($profileData);
            $user->profile->save();

            Session::flash('success', $this->getUtility()->t('User successfully saved.'));

            return Redirect::to('/'.$this->getSlug().'/me');
        } else {
            Session::flash('error', $this->getUtility()->t('An error occured during the saving process.'));
        }

        return Redirect::to('/'.$this->getSlug().'/me')
                        ->withInput()->withErrors($validator->errors());
    }

}