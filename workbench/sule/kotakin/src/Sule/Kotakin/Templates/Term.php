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

use Sule\Kotakin\Templates\TermInterface;
use Sule\Kotakin\Kotakin as CoreKotakin;

use Sule\Kotakin\Models\TermInterface as TermModelInterface;
use Cartalyst\Sentry\Users\UserInterface as UserModelInterface;

class Term implements TermInterface
{
    /**
     * The Kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The Term.
     *
     * @var Sule\Kotakin\Models\TermInterface
     */
    protected $term;

    /**
     * The user model.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $userModel;

    /**
     * The file author.
     *
     * @var Sule\Kotakin\Templates\UserInterface
     */
    protected $_author;

    /**
     * The object.
     *
     * @var Sule\Kotakin\Templates\FolderInterface | Sule\Kotakin\Templates\FileInterface
     */
    protected $_object;

    /**
     * The kind of term.
     *
     * @var string
     */
    protected $_kind;

    /**
     * The folder share user id collection.
     *
     * @var array
     */
    protected $_sharedWithUserIds;

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
     * Set the term data.
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @return void
     */
    public function setTerm(TermModelInterface $term)
    {
        $this->term = $term;
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
     * Return term id.
     *
     * @return int
     */
    public function id()
    {
        if (!is_null($this->term))
            return $this->term->getId();

        return 0;
    }

    /**
     * Return term author.
     *
     * @return Sule\Kotakin\Templates\UserInterface
     */
    public function author()
    {
        if (!is_null($this->term) and is_null($this->_author)) {
            $this->_author = $this->getKotakin()->getTemplate()->getUser();
            $this->_author->setKotakin($this->getKotakin());

            if ($this->term->author) {
                $this->_author->setUser($this->term->author);
            }
        }

        return $this->_author;
    }

    /**
     * Return term object.
     *
     * @return Sule\Kotakin\Templates\FolderInterface | Sule\Kotakin\Templates\DocumentInterface
     */
    public function object()
    {
        if (!is_null($this->term) and is_null($this->_object)) {
            if ($this->isFolder()) {
                $folder = $this->getKotakin()->getFolder()->newQuery()
                    ->where('id', '=', $this->term->getAttribute('object_id'))
                    ->first();

                $this->_object = $this->getKotakin()->getTemplate()->getFolder()->newInstance();
                $this->_object->setKotakin($this->getKotakin());
                $this->_object->setFolder($folder);
            }

            if ($this->isFile()) {
                $doc = $this->getKotakin()->getDoc()->newQuery()
                    ->where('id', '=', $this->term->getAttribute('object_id'))
                    ->first();

                $this->_object = $this->getKotakin()->getTemplate()->getDoc()->newInstance();
                $this->_object->setKotakin($this->getKotakin());
                $this->_object->setDoc($doc);
            }

            $user = $this->getUserModel();
            if ($user) {
                $this->_object->setForUser($user);
            }
            unset($user);
        }

        return $this->_object;
    }

    /**
     * Check is a folder.
     *
     * @return bool
     */
    public function isFolder()
    {
        if (!is_null($this->term))
            return (bool) $this->term->getAttribute('is_folder');

        return false;
    }

    /**
     * Check is a file.
     *
     * @return bool
     */
    public function isFile()
    {
        if (!is_null($this->term))
            return (bool) $this->term->getAttribute('is_file');

        return false;
    }

    /**
     * Check if folder authored by given user.
     *
     * @param Sule\Kotakin\Templates\UserInterface $user
     * @return bool
     */
    public function isOwnedBy(UserInterface $user)
    {
        if (!is_null($this->term)) {
            return ($this->term->getAttribute('author_id') == $user->id());
        }

        return false;
    }

    /**
     * Check is folder shared with the given user id.
     *
     * @return bool
     */
    public function sharedWith($userId)
    {
        if (!is_null($this->term) and is_null($this->_sharedWithUserIds)) {
            $this->_sharedWithUserIds = array();
            $shares = $this->term->shares;

            if (count($shares) > 0) {
                foreach ($shares as $item) {
                    $this->_sharedWithUserIds[] = $item->getAttribute('user_id');
                }
            }
        }

        return in_array($userId, $this->_sharedWithUserIds);
    }

    /**
     * Return term created time.
     *
     * @return int
     */
    public function createdAt()
    {
        if (!is_null($this->term))
            return strtotime($this->term->getAttribute('created_at'));

        return 0;
    }

    /**
     * Return term updated time.
     *
     * @return int
     */
    public function updatedAt()
    {
        if (!is_null($this->term))
            return strtotime($this->term->getAttribute('updated_at'));

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
