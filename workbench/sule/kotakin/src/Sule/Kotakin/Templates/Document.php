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

use Sule\Kotakin\Templates\DocumentInterface;
use Sule\Kotakin\Kotakin as CoreKotakin;
use Sule\Kotakin\Models\DocumentInterface as DocumentModelInterface;
use Cartalyst\Sentry\Users\UserInterface as UserModelInterface;

class Document implements DocumentInterface
{
    /**
     * The Kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The document.
     *
     * @var Sule\Kotakin\Models\DocumentInterface
     */
    protected $doc;

    /**
     * The user model.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $userModel;

    /**
     * The file.
     *
     * @var Sule\Kotakin\Templates\FileInterface
     */
    protected $_file;

    /**
     * The file location.
     *
     * @var string
     */
    protected $_location;

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
     * @param Sule\Kotakin\Models\DocumentInterface $doc
     * @return void
     */
    public function setDoc(DocumentModelInterface $doc)
    {
        $this->doc = $doc;
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
     * Return document id.
     *
     * @return int
     */
    public function id()
    {
        if (!is_null($this->doc))
            return $this->doc->getId();

        return 0;
    }

    /**
     * Return document basename.
     *
     * @return string
     */
    public function baseName()
    {
        return str_replace('.'.$this->file()->extension(), '', $this->name());
    }

    /**
     * Return document name.
     *
     * @return string
     */
    public function name()
    {
        return $this->file()->title();
    }

    /**
     * Return document slug.
     *
     * @return string
     */
    public function slug()
    {
        if (!is_null($this->doc))
            return $this->doc->getAttribute('slug');

        return '';
    }

    /**
     * Return document permalink.
     *
     * @return string
     */
    public function permalink()
    {
        if (!is_null($this->doc))
            return 'i/'.$this->doc->getAttribute('slug');

        return '';
    }

    /**
     * Return document download permalink.
     *
     * @return string
     */
    public function downloadPermalink()
    {
        if (!is_null($this->doc))
            return 'file/'.$this->doc->getAttribute('slug').'?dl=1';

        return '';
    }

    /**
     * Return document description.
     *
     * @return string
     */
    public function description()
    {
        if (!is_null($this->doc))
            return $this->doc->getAttribute('description');

        return '';
    }

    /**
     * Return document mime type.
     *
     * @return string
     */
    public function kind()
    {
        return $this->file()->mimeType();
    }

    /**
     * Return document created time.
     *
     * @return int
     */
    public function createdAt()
    {
        if (!is_null($this->doc))
            return strtotime($this->doc->getAttribute('created_at'));

        return 0;
    }

    /**
     * Return document updated time.
     *
     * @return int
     */
    public function updatedAt()
    {
        if (!is_null($this->doc))
            return strtotime($this->doc->getAttribute('updated_at'));

        return 0;
    }

    /**
     * Return document location permalink.
     *
     * @return string
     */
    public function locationPermalink()
    {
        return 'folder/'.$this->location();
    }

    /**
     * Return document location.
     *
     * @return string
     */
    public function location()
    {
        if (!is_null($this->doc) and is_null($this->_location)) {
            $this->_location = '/';

            $term = $this->getKotakin()->getTerm()->newQuery()
                            ->where('object_id', '=', $this->id())
                            ->first();

            if ($term) {
                if ($term->parent) {
                    $folder = $this->getKotakin()->getFolder()->newQuery()
                                    ->where('id', '=', $term->parent->getAttribute('object_id'))
                                    ->first();

                    if ($folder) {
                        $this->_location = $folder->getAttribute('slug');
                    }

                    unset($folder);
                }
            }

            unset($term);
        }

        return $this->_location;
    }

    /**
     * Return document file.
     *
     * @return Sule\Kotakin\Templates\FileInterface
     */
    public function file()
    {
        if (!is_null($this->doc) and is_null($this->_file)) {
            $this->_file = $this->getKotakin()->getTemplate()->getFile()->newInstance();
            $this->_file->setKotakin($this->getKotakin());
            $this->_file->setFile($this->doc->media);
        }

        return $this->_file;
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
