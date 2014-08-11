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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use Sule\Kotakin\Controllers\Admin\Base;

use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserInterface;

use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Groups\GroupNotFoundException;
use Sule\Kotakin\Models\EmailRecipientExistsException;

class User extends Base
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
     * The extended validation rules.
     *
     * @var array
     */
    protected $extendedValidationRules = array(
        'access'         => 'required',
        'max_upload'     => 'required',
        'slug'           => 'slug_available'
    );

	/**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin $kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @return void
     */
    public function __construct(Kotakin $kotakin, Sentry $sentry)
    {
        parent::__construct($kotakin, $sentry);

        $userList = $sentry->getUserProvider()->findAll();
        $users    = array();

        if (count($userList) > 0) {
            $template = $this->getKotakin()->getTemplate()->getUser();
            foreach ($userList as $index => $user) {
                $users[$index] = $template->newInstance();
                $users[$index]->setKotakin($this->getKotakin());
                $users[$index]->setSentry($this->getSentry());
                $users[$index]->setUser($user);
            }
        }

        $this->getPage()->setUsers($users);

        unset($users);
    }

    /**
     * Show user list page
     *
     * @return Illuminate\View\View
     */
	public function index()
	{
        $this->getPage()->setActiveMenu('preference');
        $this->getPage()->setAttribute('title', $this->getUtility()->t('Users | Admin'));

		return View::make('kotakin::admin_users', array(
			'page' => $this->getPage()
		));
	}

    /**
     * Show user edit page
     *
     * @param int $userId
     * @param bool $isProfile
     * @return Illuminate\View\View
     */
    public function edit($userId = 0, $isProfile = false)
    {
        $template = $this->getKotakin()->getTemplate()->getUser()->newInstance();
        $template->setKotakin($this->getKotakin());
        $template->setSentry($this->getSentry());

        $user = null;

        if ($isProfile) {
            $user   = $this->getSentry()->getUser();
            $userId = $user->getId();
            $template->setUser($user);
        } else {
            if ( ! $this->inSuperAdminGroup($this->getSentry()->getUser())) {
                App::abort(404);
            }
        }

        if ( ! empty($userId) and is_null($user)) {
            $user = $this->getSentry()->getUserProvider()->findById($userId);
            $template->setUser($user);
        }

        if ($user) {
            $action = Input::get('action', '');

            switch ($action) {
                case 'deactivate':
                    return $this->deactivate($user);
                    break;
                
                case 'activate':
                    return $this->activate($user);
                    break;

                case 'delete':
                    return $this->delete($user);
                    break;
            }
        }

        unset($user);

        $this->getPage()->setActiveMenu('preference');

        if (empty($userId)) {
            $this->getPage()->setAttribute('title', $this->getUtility()->t('New User | Admin'));
        } else {
            if ($isProfile) {
                $this->getPage()->setActiveMenu('me');

                $this->getPage()->setAttribute('title', $this->getUtility()->t('Your Profile | Admin'));
            } else {
                $this->getPage()->setAttribute('title', $this->getUtility()->t('Edit User | Admin'));
            }
        }

        return View::make('kotakin::admin_users_form', array(
            'page' => $this->getPage(),
            'user' => $template
        ));
    }

    /**
     * Deactivate a user
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return Illuminate\Routing\Redirector
     */
    protected function deactivate(UserInterface $user)
    {
        $user->setAttribute('activated', 0);
        $user->save();

        Session::flash('success', $this->getUtility()->t('User successfully deactivated.'));
        return Redirect::to('/admin/preference/user/'.$user->getId());
    }

    /**
     * Deactivate a user
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return Illuminate\Routing\Redirector
     */
    protected function activate(UserInterface $user)
    {
        $user->setAttribute('activated', 1);
        $user->save();

        Session::flash('success', $this->getUtility()->t('User successfully activated.'));
        return Redirect::to('/admin/preference/user/'.$user->getId());
    }

    /**
     * Delete a user
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return Illuminate\Routing\Redirector
     */
    protected function delete(UserInterface $user)
    {
        // Remove all shares
        $shares = $user->shares;

        if (count($shares) > 0) {
            foreach ($shares as $item) {
                $item->delete();
            }
        }

        unset($shares);

        // Remove user background
        $media = $this->getKotakin()->getMedia()->newQuery()
                        ->where('object_type', '=', 'Background')
                        ->where('object_id', '=', $user->getId())
                        ->first();

        if ($media) {
            @unlink(public_path().'/'.$media->path.'/'.$media->filename.'.'.$media->extension);
            $media->delete();
        }

        // Change the author to super admin user
        // for terms and media
        $group = null;

        try {
            $group = $this->getSentry()->getGroupProvider()->findByName('Super Admin');
        } catch (GroupNotFoundException $e) {}

        if ($group) {
            $superAdminUser = $this->getSentry()->getUserProvider()->findAllInGroup($group);
            if ( ! empty($superAdminUser)) {
                $superAdminUser = $superAdminUser[0];
            }

            if ($superAdminUser) {
                $terms = $this->getKotakin()->getTerm()->newQuery()
                                ->where('author_id', '=', $user->getId())
                                ->get();

                if (count($terms) > 0) {
                    foreach ($terms as $term) {
                        $term->setAttribute('author_id', $superAdminUser->getId());
                        $term->save();
                    }
                }

                unset($terms);

                $medias = $this->getKotakin()->getMedia()->newQuery()
                                ->where('author_id', '=', $user->getId())
                                ->get();

                if (count($medias) > 0) {
                    foreach ($medias as $media) {
                        $media->setAttribute('object_id', $superAdminUser->getId());
                        $media->setAttribute('author_id', $superAdminUser->getId());
                        $media->save();
                    }
                }

                unset($medias);
            }

            unset($superAdminUser);
        }

        unset($group);

        // Delete user profile
        if ($user->profile) {
            $user->profile->delete();
        }

        // Delete the user
        $user->delete();

        Session::flash('success', $this->getUtility()->t('User successfully deleted.'));
        return Redirect::to('/admin/preference/user');
    }

    /**
     * Save user info
     *
     * @param int $userId
     * @param bool $isProfile
     * @return Illuminate\Routing\Redirector
     */
    public function save($userId = 0, $isProfile = false)
    {
        $userProvider = $this->getSentry()->getUserProvider();

        $user = null;

        if ($isProfile) {
            $user   = $this->getSentry()->getUser();
            $userId = $user->getId();
        } else {
            if ( ! $this->inSuperAdminGroup($this->getSentry()->getUser())) {
                App::abort(404);
            }
        }

        if ( ! empty($userId) and is_null($user)) {
            $user = $userProvider->findById($userId);

            if ( ! $user)
                App::abort(404);
        }

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

        // Validate that user slug is still available
        Validator::extend('slug_available', function($attribute, $value, $parameters) use ($userProvider, $userId) {
            $query = $userProvider->getEmptyUser()->newQuery()
                                ->where('url_slug', '=', $value);

            if ( ! empty($userId)) {
                $query = $query->where('id', '!=', $userId);
            }

            $user = $query->first();
            
            if ( ! $user)
                return true;

            unset($user);

            return false;
        });

        if ( ! $isProfile) {
            $this->validationRules = array_merge($this->validationRules, $this->extendedValidationRules);
        }

        $password = Input::get('password');

        if (empty($userId) or ! empty($password)) {
            $this->validationRules['password']         = 'required';
            $this->validationRules['confirm_password'] = 'required|same:password';
        }

        $file = Input::file('background');
        if (is_object($file)) {
            $this->validationRules['background'] = 'mimes:jpeg,jpg,JPEG,JPG';
        }

        $validator = Validator::make(Input::all(), $this->validationRules);

        if ( ! $validator->fails()) {
            // Save user
            $userData = array(
                'email' => Input::get('email')
            );

            if ( ! $isProfile) {
                $slug = Input::get('slug');
                if (empty($slug))
                    $slug = trim(Input::get('name'));

                $userData['url_slug'] = Str::slug($slug);
            }

            if ( ! empty($password)) {
                $userData['password'] = $password;
            }

            if ( ! is_null($user)) {
                $user->fill($userData);
                $user->save();
            } else {
                $user = $this->getSentry()->register($userData, true);
            }

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

            if ( ! $isProfile) {
                $fileTypes   = trim(Input::get('allowed_file'));

                $fileTypesArray = explode(',', $fileTypes);
                if ( ! empty($fileTypesArray)) {
                    foreach ($fileTypesArray as &$type) {
                        $type = trim($type);
                    }
                }

                $fileTypes = implode(',', $fileTypesArray);
                unset($fileTypesArray);

                $profileData['max_upload_size']    = (int) Input::get('max_upload');
                $profileData['allowed_file_types'] = $fileTypes;
            }

            $profile = $user->profile;
            if ( ! $profile) {
                $profile = $userProvider->getEmptyUserProfile();
            }
            $profile->fill($profileData);
            $profile->save();

            $groupId = (int) Input::get('access');

            // User group
            if ( ! $isProfile) {
                // Remove from previous group
                $previousGroup   = null;
                $previousGroupId = 0;
                $groups = $user->getGroups();
                if (count($groups) > 0) {
                    $previousGroup   = $groups[0];
                    $previousGroupId = $previousGroup->getId();
                }

                if ($previousGroupId != $groupId) {
                    if ( ! empty($previousGroupId))
                        $user->removeGroup($previousGroup);

                    // Register to specified group
                    try {
                        $group = $this->getSentry()->getGroupProvider()->findById($groupId);
                        $user->addGroup($group);
                    } catch (GroupNotFoundException $e) {}
                }

                // Email recipients
                $newRecipients = Input::get('recipients');
                $recipients = $user->emailRecipients;

                if ( ! empty($newRecipients)) {
                    foreach ($newRecipients as $index => $recipientUserId) {
                        if (isset($recipients[$index])) {
                            $recipients[$index]->fill(array(
                                'to_user_id' => $recipientUserId
                            ));
                            $recipients[$index]->save();

                            unset($recipients[$index]);
                        } else {
                            $emailRecipient = $this->getKotakin()->getEmailRecipient()->newInstance();
                            $emailRecipient->fill(array(
                                'from_user_id' => $user->getId(),
                                'to_user_id'   => $recipientUserId
                            ));
                            $emailRecipient->save();
                        }
                    }
                }

                if (count($recipients) > 0) {
                    foreach($recipients as $item) {
                        $item->delete();
                    }
                }
            }

            if ($file) {
                // Get previous background
                $prevMedia = $this->getKotakin()->getMedia()->newQuery()
                                ->where('object_id', '=', $user->getId())
                                ->where('object_type', '=', 'Background')
                                ->first();

                // Upload the new background
                $folder           = Config::get('kotakin::background_folder');
                $path             = public_path().'/'.$folder;
                $originalFileName = $file->getClientOriginalName();
                $extension        = pathinfo($originalFileName, PATHINFO_EXTENSION);
                $mimeType         = $file->getClientMimeType();
                $size             = $file->getClientSize();

                $rawName  = time().$user->getId();
                $fileName = $rawName.'.'.$extension;

                if ( ! File::isDirectory($path))
                    File::makeDirectory($path, Config::get('kotakin::chmod_folder'), true);

                try {
                    $file->move($path, $fileName);

                    $dimension = $this->getUtility()->getDimension($path.'/'.$fileName);

                    $media = $this->getKotakin()->getMedia()->newInstance();
                    $media->fill(array(
                        'parent_id'   => 0,
                        'object_id'   => $user->getId(),
                        'object_type' => 'Background',
                        'author_id'   => $this->getSentry()->getUser()->getId(),
                        'type'        => 'original',
                        'title'       => $originalFileName,
                        'alt_text'    => $originalFileName,
                        'path'        => $folder,
                        'filename'    => $rawName,
                        'extension'   => $extension,
                        'mime_type'   => $mimeType,
                        'size'        => $size,
                        'metadata'    => serialize($dimension)
                    ));

                    if ( ! $media->save()) {
                        @unlink($path.'/'.$fileName);
                        
                        Session::flash('error', sprintf($this->getUtility()->t('Failed to save file "%s" informations.'), $originalFileName));
                    } else {
                        if ($prevMedia) {
                            @unlink(public_path().'/'.$prevMedia->getAttribute('path').'/'.$prevMedia->getAttribute('filename').'.'.$prevMedia->getAttribute('extension'));
                            $prevMedia->delete();
                        }
                    }
                } catch (FileException $e) {
                    Session::flash('error', sprintf($this->getUtility()->t('Unable to process your uploaded file "%s".'), $originalFileName));
                }
            }

            if (empty($userId) or ! empty($password)) {
                if ( ! $this->sendEmail($user, $password, $groupId)) {
                    Session::flash('error', $this->getUtility()->t('An error occured while sending the email.'));
                }
            }

            Session::flash('success', $this->getUtility()->t('User successfully saved.'));

            if ( ! $isProfile) {
                return Redirect::to('/admin/preference/user/'.$user->getId());
            } else {
                return Redirect::to('/admin/me');
            }
        } else {
            Session::flash('error', $this->getUtility()->t('An error occured during the saving process.'));
        }

        if ( ! $isProfile) {
            return Redirect::to('/admin/preference/user/'.$userId)
                            ->withInput()->withErrors($validator->errors());
        } else {
            return Redirect::to('/admin/me')
                            ->withInput()->withErrors($validator->errors());
        }
    }

    /**
     * Send user a email
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @param string $password
     * @param int $groupId
     * @return bool
     */
    public function sendEmail($user, $password, $groupId)
    {
        $userTemplate = $this->getKotakin()->getTemplate()->getUser();
        $userTemplate->setKotakin($this->getKotakin());
        $userTemplate->setUser($user);
        $userTemplate->setBasePermalink(URL::to('/%s'));
        $userTemplate->setRawPassword($password);

        if ($groupId != 3) {
            $userTemplate->setBasePermalink(URL::to('/admin'));
        }

        return $this->getKotakin()->getMailer()->send('new_user', array(
            'page' => $this->getPage(),
            'user' => $userTemplate
        ), function($message) use ($userTemplate) {
            $message->to($userTemplate->email(), $userTemplate->firstName());
        });
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