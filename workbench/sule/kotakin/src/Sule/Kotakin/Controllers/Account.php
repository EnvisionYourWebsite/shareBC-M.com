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

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

use Sule\Kotakin\Controllers\BaseUser;
use Sule\Kotakin\Kotakin;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\WrongPasswordException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Users\UserNotActivatedException;
use Cartalyst\Sentry\Throttling\UserSuspendedException;
use Cartalyst\Sentry\Throttling\UserBannedException;

class Account extends BaseUser
{
    /**
     * The login validation rules.
     *
     * @var array
     */
    protected $loginRules = array(
        'email'    => 'required|email',
        'password' => 'required'
    );

    /**
     * The change password validation rules.
     *
     * @var array
     */
    protected $passwordRules = array(
        'password'         => 'required',
        'password_confirm' => 'required|same:password'
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

        $this->setJSVars();
    }

    /**
     * Login page.
     *
     * @return Illuminate\Routing\Redirector | Illuminate\View\View
     */
	public function login()
	{
        $continueUri = Input::get('continue');
        $continueParam = '';
        if ( ! empty($continueUri)) {
            $continueParam = '?continue='.urlencode($continueUri);
        }

        // echo $continueUri;
        // exit();

        // Do authentication
        if ($_POST) {
            $messages = array(
                'email.required'    => $this->getUtility()->t('Email is required.'),
                'email.email'       => $this->getUtility()->t('Email is not valid.'),
                'password.required' => $this->getUtility()->t('Password is required.'),
            );

            $validator = Validator::make(Input::all(), $this->loginRules, $messages);

            if ( ! $validator->fails()) {
                $loggedIn = false;
                $userNotFound = false;
                $error = '';

                // Try to authenticate the user
                try {
                    $user = $this->getSentry()->authenticate(array(
                        'email'    => Input::get('email'), 
                        'password' => Input::get('password')
                    ), Input::get('remember'));

                    $loggedIn = true;
                } catch (WrongPasswordException $e) {
                    $userNotFound = true;
                } catch (UserNotFoundException $e) {
                    $userNotFound = true;
                } catch (UserNotActivatedException $e) {
                    $userNotFound = true;
                } catch (UserSuspendedException $e) {
                    $error = $this->getUtility()->t('Your account suspended from our website.');
                } catch (UserBannedException $e) {
                    $error = $this->getUtility()->t('You have been banned from our website.');
                }

                if ($userNotFound)
                    $error = $this->getUtility()->t('Email or Password is incorrect.');

                if ($loggedIn and ! empty($continueUri)) {
                    return Redirect::to($continueUri);
                }

                if ($loggedIn) {
                    return Redirect::to('/'.$this->getSlug());
                }

                Session::flash('error', $error);

                return Redirect::to('/'.$this->getSlug().'/login'.$continueParam)->withInput();
            }
            
            return Redirect::to('/'.$this->getSlug().'/login'.$continueParam)
                    ->withInput()->withErrors($validator->errors());
        }

        $this->getPage()->setAttribute('title', sprintf($this->getUtility()->t('Login | %s'), $this->getPage()->getAttribute('brand')));

		return View::make('kotakin::user_login', array(
			'page' => $this->getPage()
		));
	}

    /**
     * Logging out user
     *
     * @return Illuminate\Routing\Redirector
     */
    public function logout()
    {
        $this->getSentry()->logout();

        return Redirect::to('/'.$this->getSlug().'/login');
    }

    /**
     * Show the forgot password page
     *
     * @return mixed
     */
    public function forgot()
    {
        if ($_POST) {
            $email = Input::get('email');

            try {
                // Find the user using the user email address
                $user = $this->getSentry()->getUserProvider()->findByLogin($email);

                // Get the password reset code
                $resetCode = $user->getResetPasswordCode();

                // Send email to the user
                $this->sendResetCodeEmail($user);

                Session::flash('success', $this->getUtility()->t('Password Reset Email Sent.'));
            } catch (UserNotFoundException $e) {
               Session::flash('error', $this->getUtility()->t('No account was found with these details.'));
            }

            return Redirect::to('/'.$this->getSlug().'/forgot')->withInput();
        }

        // Set kotakin template data
        $this->getPage()->setAttribute('title', sprintf($this->getUtility()->t('Reset Password | %s'), $this->getPage()->getAttribute('brand')));

        return View::make('kotakin::user_forgot', array(
            'page' => $this->getPage()
        ));
    }

    /**
     * Show the reset password page
     *
     * @param string $code
     * @return mixed
     */
    public function reset($code = '')
    {
        if ( ! empty($code)) {
            $codeInvalid = false;

            try {
                // Find the user using the user email address
                $user = $this->getSentry()->getUserProvider()->findByResetPasswordCode($code);

                // Check if the reset password code is valid
                if ( ! $user->checkResetPasswordCode($code))
                    $codeInvalid = true;
            } catch (UserNotFoundException $e) {
                $codeInvalid = true;
            }

            if ($codeInvalid) {
                Session::flash('error', $this->getUtility()->t('Wrong password reset code provided.'));

                return Redirect::to('/'.$this->getSlug().'/forgot');
            }
        }

        if ($_POST) {
            $messages = array(
                'password.required'         => $this->getUtility()->t('New password is required.'),
                'password_confirm.required' => $this->getUtility()->t('New password confirmation is required.'),
                'password_confirm.same'     => $this->getUtility()->t('Password does not match.'),
            );

            $validator = Validator::make(Input::all(), $this->passwordRules, $messages);

            if ( ! $validator->fails()) {
                $user->setAttribute('password', Input::get('password'));
                if ($user->save())
                    return Redirect::to('/'.$this->getSlug().'/reset_complete');
            }

            return Redirect::to('/'.$this->getSlug().'/reset/'.$code)
                        ->withInput()->withErrors($validator->errors());
        }

        // Set kotakin template data
        $this->getPage()->setAttribute('title', sprintf($this->getUtility()->t('Reset Password | %s'), $this->getPage()->getAttribute('brand')));

        return View::make('kotakin::user_reset', array(
            'page' => $this->getPage()
        ));
    }

    /**
     * Show the reset password complete page
     *
     * @return Illuminate\View\View
     */
    public function resetComplete()
    {
        // Set kotakin template data
        $this->getPage()->setAttribute('title', sprintf($this->getUtility()->t('Reset Password | %s'), $this->getPage()->getAttribute('brand')));

        return View::make('kotakin::user_reset_complete', array(
            'page' => $this->getPage()
        ));
    }

    /**
     * Send user a reset code via email
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return void
     */
    public function sendResetCodeEmail($user)
    {
        $userTemplate = $this->getKotakin()->getTemplate()->getUser();
        $userTemplate->setKotakin($this->getKotakin());
        $userTemplate->setUser($user);
        $userTemplate->setResetPasswordUrl(URL::to('/'.$this->getSlug().'/reset/%s'));

        $this->getKotakin()->getMailer()->send('reset_password', array(
            'user' => $userTemplate
        ), function($message) use ($userTemplate) {
            $message->to($userTemplate->email(), $userTemplate->firstName());
        });
    }

    /**
     * Set default JavaScript variables.
     *
     * @return void
     */
    protected function setJSVars()
    {
        $media = $this->getKotakin()->getMedia()->newQuery()
                        ->where('object_type', '=', 'Background')
                        ->where('object_id', '=', $this->getUser()->getId())
                        ->first();

        if ($media) {
            $background = '/'.$media->path.'/'.$media->filename.'.'.$media->extension;
        } else {
            $background = '/packages/sule/kotakin/img/kotakin.jpg';
        }
        
        $js = '<script type="text/javascript">';
        $js .= 'jQuery(function($){';
        $js .= '$.supersized({';
        $js .= 'slides: [ {image: "'.URL::to($background).'", title: ""} ]';
        $js .= '});';
        $js .= '});';
        $js .= '</script>';

        $this->getPage()->setMetadata($js, 'footer');

        unset($media);
    }

}