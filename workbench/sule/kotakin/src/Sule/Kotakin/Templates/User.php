<?php
namespace Sule\Kotakin\Templates;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sule\Kotakin\Templates\UserInterface as UserTemplateInterface;

use Sule\Kotakin\Kotakin as CoreKotakin;
use Cartalyst\Sentry\Sentry;

use Cartalyst\Sentry\Users\UserInterface as UserModelInterface;

use stdClass;

use Cartalyst\Sentry\Groups\GroupNotFoundException;

class User implements UserTemplateInterface 
{

    /**
     * The Kotakin.
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
     * The user data.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $user;

    /**
     * The avilable mime types.
     *
     * @var array
     */
    protected $_availableMimeTypes = array();

    /**
     * The user background.
     *
     * @var string
     */
    protected $_background;

    /**
     * The reset password URL.
     *
     * @var string
     */
    protected $_resetPasswordUrl;

    /**
     * The raw password.
     *
     * @var string
     */
    protected $_rawPassword;

    /**
     * The base permalink.
     *
     * @var string
     */
    protected $_permalink;

    /**
     * The recipient user id collection.
     *
     * @var array
     */
    protected $_recipientIds;

    /**
     * Set the kotakin.
     *
     * @param Sule\Kotakin\Kotakin $kotakin
     * @return void
     */
    public function setKotakin(CoreKotakin $kotakin)
    {
        $this->kotakin = $kotakin;
    }

    /**
     * Get the kotakin.
     *
     * @return Sule\Kotakin\Kotakin
     */
    protected function getKotakin()
    {
        return $this->kotakin;
    }

    /**
     * Set the Sentry.
     *
     * @param Cartalyst\Sentry\Sentry $sentry
     * @return void
     */
    public function setSentry(Sentry $sentry)
    {
        $this->sentry = $sentry;
    }

    /**
     * Get the Sentry.
     *
     * @return Cartalyst\Sentry\Sentry
     */
    protected function getSentry()
    {
        return $this->sentry;
    }

    /**
     * Set the user data.
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return void
     */
    public function setUser(UserModelInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Set the available mime types.
     *
     * @param array $mimeTypes
     * @return void
     */
    public function setAvailableMimeTypes(Array $mimeTypes)
    {
        $this->_availableMimeTypes = $mimeTypes;
    }

    /**
     * Set the reset password URL.
     *
     * @param string $url
     * @return void
     */
    public function setResetPasswordUrl($url)
    {
        $this->_resetPasswordUrl = $url;
    }

    /**
     * Set the raw password.
     *
     * @param string $password
     * @return void
     */
    public function setRawPassword($password)
    {
        $this->_rawPassword = $password;
    }

    /**
     * Return the raw password.
     *
     * @return string
     */
    public function password()
    {
        return $this->_rawPassword;
    }

    /**
     * Set the permalink.
     *
     * @param string $permalink
     * @return void
     */
    public function setBasePermalink($permalink)
    {
        $this->_permalink = $permalink;
    }

    /**
     * Return the permalink.
     *
     * @return string
     */
    public function permalink()
    {
        return sprintf($this->_permalink, $this->slug());
    }

    /**
     * Check is user defined.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return ! is_null($this->user);
    }

    /**
     * Check is user in specified group.
     *
     * @param string $name
     * @return bool
     */
    public function inGroup($name)
    {
        if (!is_null($this->user)) {
            try {
                $group = $this->getSentry()->getGroupProvider()->findByName($name);
            } catch (GroupNotFoundException $e) {
                return false;
            }

            return $this->user->inGroup($group);
        }

        return false;
    }

    /**
     * Return user id.
     *
     * @return int
     */
    public function id()
    {
        if (!is_null($this->user))
            return $this->user->getId();

        return 0;
    }

    /**
     * Return user email.
     *
     * @param string $formValue
     * @return string
     */
    public function email($formValue = '')
    {
        if ( ! empty($formValue))
            return $formValue;

        if (!is_null($this->user))
            return $this->user->getAttribute('email');

        return '';
    }

    /**
     * Return user name.
     *
     * @param string $formValue
     * @return string
     */
    public function name($formValue = '')
    {
        if ( ! empty($formValue))
            return $formValue;

        if (!is_null($this->user) and $this->user->profile)
            return $this->user->profile->getAttribute('display_name');

        return '';
    }

    /**
     * Return user firstname.
     *
     * @return string
     */
    public function firstName()
    {
        if (!is_null($this->user) and $this->user->profile)
            return $this->user->profile->getAttribute('first_name');

        return '';
    }

    /**
     * Return user lastname.
     *
     * @return string
     */
    public function lastName()
    {
        if (!is_null($this->user) and $this->user->profile)
            return $this->user->profile->getAttribute('last_name');

        return '';
    }

    /**
     * Return user background permalink.
     *
     * @return string
     */
    public function background()
    {
        if (!is_null($this->user) and is_null($this->_background)) {
            $media = $this->getKotakin()->getMedia()->newQuery()
                        ->where('object_id', '=', $this->id())
                        ->where('object_type', '=', 'Background')
                        ->first();

            if ($media) {
                $this->_background = '/'.$media->getAttribute('path').'/'.$media->getAttribute('filename').'.'.$media->getAttribute('extension');
            } else {
                $this->_background = '';
            }
        }

        return $this->_background;
    }

    /**
     * Return user phone.
     *
     * @return string
     */
    public function phone()
    {
        if (!is_null($this->user) and $this->user->profile)
            return $this->user->profile->getAttribute('phone');

        return '';
    }

    /**
     * Return user group.
     *
     * @return stdClass (id and name)
     */
    public function group()
    {
        $group = new stdClass;

        if (!is_null($this->user)) {
            $groups = $this->user->getGroups();
            if (count($groups) > 0) {
                $group->id   = $groups[0]->getId();
                $group->name = $groups[0]->getAttribute('name');
            }
        }

        return $group;
    }

    /**
     * Return user edit permalink.
     *
     * @return string
     */
    public function editPermalink()
    {
        if (!is_null($this->user))
            return '/admin/preference/user/'.$this->id();

        return '';
    }

    /**
     * Return user slug.
     *
     * @param string $formValue
     * @return string
     */
    public function slug($formValue = '')
    {
        if ( ! empty($formValue))
            return $formValue;

        if (!is_null($this->user))
            return $this->user->getAttribute('url_slug');

        return '';
    }

    /**
     * Return user date format.
     *
     * @param string $formValue
     * @return string
     */
    public function dateFormat($formValue = '')
    {
        if ( ! empty($formValue))
            return $formValue;

        if (!is_null($this->user) and $this->user->profile)
            return $this->user->profile->getAttribute('date_format');

        return 'Y/m/d H:i A';
    }

    /**
     * Return user max upload file size.
     *
     * @param bool $inBytes
     * @param string $formValue
     * @return string | int
     */
    public function maxUploadSize($inBytes = false, $formValue = '')
    {
        if ( ! empty($formValue))
            return $formValue;

        $utility = $this->getKotakin()->getUtility();
        $size = 0;

        if (!is_null($this->user) and $this->user->profile) {
            if ($inBytes) {
                $size = $this->user->profile->getAttribute('max_upload_size');
            } else {
                $size = $utility->humanReadableFileSize($this->user->profile->getAttribute('max_upload_size'));
            }
        }

        if (empty($size)) {
            if ($inBytes) {
                $size = $utility->getBytes(ini_get('upload_max_filesize'));
            } else {
                $size = $utility->humanReadableFileSize($utility->getBytes(ini_get('upload_max_filesize')));
            }
        }

        return $size;
    }

    /**
     * Return user allowed file upload types.
     *
     * @param string $formValue
     * @return string
     */
    public function allowedFileTypes($formValue = '')
    {
        if ( ! empty($formValue))
            return $formValue;

        if (!is_null($this->user) and $this->user->profile)
            return $this->user->profile->getAttribute('allowed_file_types');

        return '';
    }

    /**
     * Return user allowed file mime types.
     *
     * @return array
     */
    public function allowedMimeTypes()
    {
        $mimeTypes = array();
        $extensions = explode(',', $this->allowedFileTypes());
        
        if ( ! empty($extensions)) {
            foreach ($extensions as $extension) {
                if (isset($this->_availableMimeTypes[$extension])) {
                    $mimeTypes[] = $this->_availableMimeTypes[$extension];
                }
            }
        }

        unset($extensions);

        return $mimeTypes;
    }

    /**
     * Return user notification recipient user ids.
     *
     * @return array
     */
    public function recipientUserIds()
    {
        if (!is_null($this->user) and is_null($this->_recipientIds)) {
            $this->_recipientIds = array();
            $recipients = $this->user->emailRecipients;
            
            if (count($recipients) > 0) {
                foreach ($recipients as $item) {
                    $this->_recipientIds[] = $item->getAttribute('to_user_id');
                }
            }

            unset($recipients);
        }

        return $this->_recipientIds;
    }

    /**
     * Return user activation code.
     *
     * @return string
     */
    public function activationCode()
    {
        if (!is_null($this->user))
            return $this->user->getAttribute('activation_code');

        return '';
    }

    /**
     * Return user reset password code.
     *
     * @return string
     */
    public function resetPasswordCode()
    {
        if (!is_null($this->user))
            return $this->user->getAttribute('reset_password_code');

        return '';
    }

    /**
     * Return user last login time.
     *
     * @return int
     */
    public function lastLogin()
    {
        if (!is_null($this->user))
            return strtotime($this->user->getAttribute('last_login'));

        return 0;
    }

    /**
     * Return user registered time.
     *
     * @return int
     */
    public function createdAt()
    {
        if (!is_null($this->user))
            return strtotime($this->user->getAttribute('created_at'));

        return 0;
    }

    /**
     * Check is user activated.
     *
     * @return bool
     */
    public function isActivated()
    {
        if (!is_null($this->user))
            return (bool) $this->user->getAttribute('activated');

        return false;
    }

    /**
     * Return user reset password URL.
     *
     * @return string
     */
    public function resetPasswordUrl()
    {
        if (!is_null($this->user) and !is_null($this->_resetPasswordUrl))
            return sprintf($this->_resetPasswordUrl, $this->resetPasswordCode());

        return '';
    }

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\UserInterface|static
     */
    public function newInstance()
    {
        // This method just provides a convenient way for us to generate fresh template
        // instances of this current template. It is particularly useful during the
        // hydration of new objects.
        return new static;
    }

}
