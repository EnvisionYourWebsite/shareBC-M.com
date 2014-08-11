<?php
namespace Sule\Kotakin\Templates;

/*
 * This file is part of the Spavista.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cartalyst\Sentry\Users\UserInterface as UserModelInterface;

use Sule\Kotakin\Kotakin as CoreKotakin;

interface UserInterface
{
    
    /**
     * Return the raw password.
     *
     * @return string
     */
    public function password();

    /**
     * Return the permalink.
     *
     * @return string
     */
    public function permalink();

    /**
     * Check is user defined.
     *
     * @return bool
     */
    public function isLoggedIn();

    /**
     * Check is user in specified group.
     *
     * @param string $name
     * @return bool
     */
    public function inGroup($name);

    /**
     * Return user id.
     *
     * @return int
     */
    public function id();

    /**
     * Return user email.
     *
     * @param string $formValue
     * @return string
     */
    public function email($formValue = '');

    /**
     * Return user name.
     *
     * @param string $formValue
     * @return string
     */
    public function name($formValue = '');

    /**
     * Return user firstname.
     *
     * @return string
     */
    public function firstName();

    /**
     * Return user lastname.
     *
     * @return string
     */
    public function lastName();

    /**
     * Return user background permalink.
     *
     * @return string
     */
    public function background();

    /**
     * Return user phone.
     *
     * @return string
     */
    public function phone();

    /**
     * Return user group.
     *
     * @return stdClass (id and name)
     */
    public function group();

    /**
     * Return user edit permalink.
     *
     * @return string
     */
    public function editPermalink();

    /**
     * Return user slug.
     *
     * @param string $formValue
     * @return string
     */
    public function slug($formValue = '');

    /**
     * Return user date format.
     *
     * @param string $formValue
     * @return string
     */
    public function dateFormat($formValue = '');

    /**
     * Return user max upload file size.
     *
     * @param bool $inBytes
     * @param string $formValue
     * @return string | int
     */
    public function maxUploadSize($inBytes = false, $formValue = '');

    /**
     * Return user allowed file upload types.
     *
     * @param string $formValue
     * @return string
     */
    public function allowedFileTypes($formValue = '');

    /**
     * Return user allowed file mime types.
     *
     * @return array
     */
    public function allowedMimeTypes();

    /**
     * Return user notification recipient user ids.
     *
     * @return array
     */
    public function recipientUserIds();

    /**
     * Return user activation code.
     *
     * @return string
     */
    public function activationCode();

    /**
     * Return user reset password code.
     *
     * @return string
     */
    public function resetPasswordCode();

    /**
     * Return user last login time.
     *
     * @return int
     */
    public function lastLogin();

    /**
     * Return user registered time.
     *
     * @return int
     */
    public function createdAt();

    /**
     * Check is user activated.
     *
     * @return bool
     */
    public function isActivated();

    /**
     * Return user reset password URL.
     *
     * @return string
     */
    public function resetPasswordUrl();

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\UserInterface|static
     */
    public function newInstance();

}
