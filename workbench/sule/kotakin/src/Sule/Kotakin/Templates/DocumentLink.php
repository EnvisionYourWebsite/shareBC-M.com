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

use Sule\Kotakin\Templates\DocumentLinkInterface;
use Sule\Kotakin\Kotakin as CoreKotakin;
use Cartalyst\Sentry\Users\UserInterface as UserModelInterface;
use Sule\Kotakin\Models\DocumentLinkInterface as DocumentLinkModelInterface;

class DocumentLink implements DocumentLinkInterface
{
    /**
     * The Kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The user data.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $user;

    /**
     * The link.
     *
     * @var Sule\Kotakin\Models\DocumentLinkInterface
     */
    protected $doc;

    /**
     * The document.
     *
     * @var Sule\Kotakin\Models\DocumentInterface
     */
    protected $_doc;

    /**
     * The link author.
     *
     * @var Sule\Kotakin\Templates\UserInterface
     */
    protected $_author;

    /**
     * The link password.
     *
     * @var string
     */
    protected $_password = '';

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
     * Set the user.
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return void
     */
    public function setUser(UserModelInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Get the user.
     *
     * @return Cartalyst\Sentry\Users\UserInterface
     */
    protected function getUser()
    {
        return $this->user;
    }

    /**
     * Set the link data.
     *
     * @param Sule\Kotakin\Models\DocumentLinkInterface $link
     * @return void
     */
    public function setLink(DocumentLinkModelInterface $link)
    {
        $this->link = $link;
    }

    /**
     * Set the link password.
     *
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    /**
     * Get the link password.
     *
     * @return string
     */
    protected function getPassword()
    {
        return $this->_password;
    }

    /**
     * Return the link id.
     *
     * @return int
     */
    public function id()
    {
        if (!is_null($this->link))
            return $this->link->getId();

        return 0;
    }

    /**
     * Return the link slug.
     *
     * @return string
     */
    public function slug()
    {
        if (!is_null($this->link))
            return $this->link->getAttribute('slug');

        return '';
    }

    /**
     * Return the link permalink.
     *
     * @return string
     */
    public function permalink()
    {
        if (!is_null($this->link))
            return 'i/'.$this->link->getAttribute('slug');

        return '';
    }

    /**
     * Return the link hashed password.
     *
     * @return string
     */
    public function password()
    {
        if (!is_null($this->link))
            return $this->link->getAttribute('password');

        return '';
    }

    /**
     * Return the link limit.
     *
     * @return int
     */
    public function limit()
    {
        if (!is_null($this->link))
            return $this->link->getAttribute('limit');

        return 0;
    }

    /**
     * Return the link valid time.
     *
     * @return int
     */
    public function validUntil()
    {
        if (!is_null($this->link))
            return strtotime($this->link->getAttribute('valid_until'));

        return 0;
    }

    /**
     * Return the link description.
     *
     * @return string
     */
    public function description()
    {
        if (!is_null($this->link))
            return $this->link->getAttribute('description');

        return '';
    }

    /**
     * Return the link total downloaded.
     *
     * @return int
     */
    public function downloadedTimes()
    {
        if (!is_null($this->link))
            return $this->link->getAttribute('downloaded_times');

        return 0;
    }

    /**
     * Return the link created time.
     *
     * @return int
     */
    public function createdAt()
    {
        if (!is_null($this->link))
            return strtotime($this->link->getAttribute('created_at'));

        return 0;
    }

    /**
     * Return the link updated time.
     *
     * @return int
     */
    public function updatedAt()
    {
        if (!is_null($this->link))
            return strtotime($this->link->getAttribute('updated_at'));

        return 0;
    }

    /**
     * Return the link author.
     *
     * @return Sule\Kotakin\Templates\UserInterface
     */
    public function author()
    {
        if (!is_null($this->link) and is_null($this->_author)) {
            $this->_author = $this->getKotakin()->getTemplate()->getUser();
            $this->_author->setKotakin($this->getKotakin());

            if ($this->link->author) {
                $this->_author->setUser($this->link->author);
            }
        }

        return $this->_author;
    }

    /**
     * Return the link document.
     *
     * @return Sule\Kotakin\Templates\DocumentInterface
     */
    public function doc()
    {
        if (!is_null($this->link) and is_null($this->_doc)) {
            $this->_doc = $this->getKotakin()->getTemplate()->getDoc()->newInstance();
            $this->_doc->setKotakin($this->getKotakin());
            $this->_doc->setDoc($this->link->document);
        }

        return $this->_doc;
    }

    /**
     * Check if link unlocked.
     *
     * @return bool
     */
    public function unlocked()
    {
        $password        = $this->password();
        $currentPassword = $this->getPassword();

        if (empty($password))
            return true;

        if (!is_null($this->link) and ! empty($currentPassword)) {
            return $this->getUser()->checkHash($currentPassword, $password);
        }

        return false;
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
