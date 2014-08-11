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

use Sule\Kotakin\Templates\FolderInterface;
use Sule\Kotakin\Kotakin as CoreKotakin;
use Sule\Kotakin\Models\FolderInterface as FolderModelInterface;
use Cartalyst\Sentry\Users\UserInterface as UserModelInterface;

class Folder implements FolderInterface
{
    /**
     * The Kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The folder.
     *
     * @var Sule\Kotakin\Models\FolderInterface
     */
    protected $folder;

    /**
     * The user model.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $userModel;

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
     * Set the document data.
     *
     * @param Sule\Kotakin\Models\FolderInterface $folder
     * @return void
     */
    public function setFolder(FolderModelInterface $folder)
    {
        $this->folder = $folder;
    }

    /**
     * Set the current user.
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return void
     */
    public function setForUser(UserModelInterface $user)
    {
        $this->userModel = $user;
    }

    /**
     * Get user model.
     *
     * @return Cartalyst\Sentry\Users\UserInterface
     */
    protected function getUserModel()
    {
        return $this->userModel;
    }

    /**
     * Return folder id.
     *
     * @return int
     */
    public function id()
    {
        if (!is_null($this->folder))
            return $this->folder->getId();

        return 0;
    }

    /**
     * Return folder parent id.
     *
     * @return int
     */
    public function parentId()
    {
        if (!is_null($this->folder))
            return $this->folder->getAttribute('parent_id');

        return 0;
    }

    /**
     * Return folder type.
     *
     * @return string
     */
    public function type()
    {
        if (!is_null($this->folder))
            return $this->folder->getAttribute('type');

        return '';
    }

    /**
     * Return folder slug.
     *
     * @return string
     */
    public function slug()
    {
        if (!is_null($this->folder))
            return $this->folder->getAttribute('slug');

        return '';
    }

    /**
     * Return folder permalink.
     *
     * @return string
     */
    public function permalink()
    {
        if (!is_null($this->folder))
            return 'folder/'.$this->folder->getAttribute('slug');

        return '';
    }

    /**
     * Return folder download permalink.
     *
     * @return string
     */
    public function downloadPermalink()
    {
        if (!is_null($this->folder))
            return 'folder/'.$this->folder->getAttribute('slug').'?dl=1';

        return '';
    }

    /**
     * Return folder base name.
     *
     * @return string
     */
    public function baseName()
    {
        return $this->name();
    }

    /**
     * Return folder name.
     *
     * @return string
     */
    public function name()
    {
        if (!is_null($this->folder))
            return $this->folder->getAttribute('name');

        return '';
    }

    /**
     * Return folder password.
     *
     * @return string
     */
    public function password()
    {
        if (!is_null($this->folder))
            return $this->folder->getAttribute('password');

        return '';
    }

    /**
     * Return folder description.
     *
     * @return string
     */
    public function description()
    {
        if (!is_null($this->folder))
            return $this->folder->getAttribute('description');

        return '';
    }

    /**
     * Return folder kind (shared or not).
     *
     * @return string
     */
    public function kind()
    {
        if (!is_null($this->folder)) {
            if ($this->isShared() and is_null($this->getUserModel())) {
                return $this->getKotakin()->getUtility()->t('shared folder');
            } else {
                return $this->getKotakin()->getUtility()->t('folder');
            }
        }

        return '';
    }

    /**
     * Check is folder shared.
     *
     * @return bool
     */
    public function isShared()
    {
        if (!is_null($this->folder))
            return (bool) $this->folder->getAttribute('is_shared');

        return false;
    }

    /**
     * Check is use allowed to upload in this folder.
     *
     * @return bool
     */
    public function isAllowUserUpload()
    {
        if (!is_null($this->folder))
            return (bool) $this->folder->getAttribute('user_upload');

        return false;
    }

    /**
     * Return folder created time.
     *
     * @return int
     */
    public function createdAt()
    {
        if (!is_null($this->folder))
            return strtotime($this->folder->getAttribute('created_at'));

        return 0;
    }

    /**
     * Return folder updated time.
     *
     * @return int
     */
    public function updatedAt()
    {
        if (!is_null($this->folder))
            return strtotime($this->folder->getAttribute('updated_at'));

        return 0;
    }

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\FileInterface|static
     */
    public function newInstance()
    {
        // This method just provides a convenient way for us to generate fresh template
        // instances of this current template. It is particularly useful during the
        // hydration of new objects.
        return new static;
    }

}
