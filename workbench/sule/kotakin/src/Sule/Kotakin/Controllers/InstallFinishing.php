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

use Illuminate\Routing\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;

use Cartalyst\Sentry\Users\UserExistsException;
use Cartalyst\Sentry\Groups\GroupNotFoundException;

class InstallFinishing extends Controller
{

    /**
     * The kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The Sentry.
     *
     * @var Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry)
    {
        $this->kotakin = $kotakin;
        $this->sentry  = $sentry;
    }

	/**
     * Show the installer.
     *
     * @return Illuminate\View\View
     */
    public function index()
    {
        try {
            $group = $this->sentry->getGroupProvider()->findByName('Super Admin');
            
            $users = $this->sentry->getUserProvider()->findAllInGroup($group);

            if ( ! empty($users)) {
                return Redirect::to('/install/complete');
            }
        } catch (GroupNotFoundException $e) {}

        if ($_POST) {
            $validator = Validator::make(Input::all(), array(
                'name'     => 'required',
                'email'    => 'required|email',
                'password' => 'required'
            ));

            $isError      = true;
            $errorMessage = '';

            if ( ! $validator->fails()) {
                $isError = false;

                try {
                    $user = $this->sentry->register(array(
                        'email'    => Input::get('email'),
                        'password' => Input::get('password'),
                    ), true);

                    $userProvider = $this->sentry->getUserProvider();
                    $profile = $userProvider->getEmptyUserProfile();

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
                        'date_format'  => 'Y-m-d H:i:s'
                    );

                    $profile->fill($profileData);
                    if ( ! $profile->save())
                    {
                        $user->delete();
                        $isError = true;
                    }

                    if ( ! $isError) {
                        try {
                            $group = $this->sentry->getGroupProvider()->findByName('Super Admin');
                            $user->addGroup($group);
                        } catch (GroupNotFoundException $e) {
                            $user->delete();
                            $profile->delete();
                            $isError = true;
                        }
                    }

                    if ( ! $isError) {
                        return Redirect::to('/install/complete');
                    }
                } catch (UserExistsException $e) {
                    $isError = true;
                }
            }

            if ($isError) {
                Session::flash('error', 'There is a problem while registering a new user.');
            }

            return Redirect::to('/install/user')
                        ->withInput()->withErrors($validator->errors());
        }

        return view::make('kotakin::install/user', array(
            'menu' => 'user'
        ));
    }

    /**
     * Show the installer.
     *
     * @return Illuminate\View\View
     */
    public function complete()
    {
        return view::make('kotakin::install/complete', array(
            'menu' => 'complete'
        ));
    }

}