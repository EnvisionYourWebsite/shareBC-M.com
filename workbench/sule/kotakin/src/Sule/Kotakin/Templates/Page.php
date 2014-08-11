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

use Sule\Kotakin\Templates\PageInterface;

use Sule\Kotakin\Kotakin as CoreKotakin;

use Sule\Kotakin\Models\TermInterface as TermModelInterface;
use Cartalyst\Sentry\Users\UserInterface as UserModelInterface;

use Sule\Kotakin\Templates\UserInterface;
use Sule\Kotakin\Templates\TermInterface;
use Sule\Kotakin\Templates\FolderInterface;
use Sule\Kotakin\Templates\DocumentInterface;
use Sule\Kotakin\Templates\DocumentLinkInterface;
use Sule\Kotakin\Templates\FileInterface;
use Sule\Kotakin\Templates\EmailInterface;

class Page implements PageInterface
{
    /**
     * The Kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The user template.
     *
     * @var Sule\Kotakin\Templates\UserInterface
     */
    protected $user;

    /**
     * The term template.
     *
     * @var Sule\Kotakin\Templates\TermInterface
     */
    protected $term;

    /**
     * The folder template.
     *
     * @var Sule\Kotakin\Templates\FolderInterface
     */
    protected $folder;

    /**
     * The document template.
     *
     * @var Sule\Kotakin\Templates\DocumentInterface
     */
    protected $doc;

    /**
     * The document link template.
     *
     * @var Sule\Kotakin\Templates\DocumentLinkInterface
     */
    protected $docLink;

    /**
     * The file template.
     *
     * @var Sule\Kotakin\Templates\FileInterface
     */
    protected $file;

    /**
     * The email template.
     *
     * @var Sule\Kotakin\Templates\EmailInterface
     */
    protected $email;
    
    /**
     * The metadata collection.
     *
     * @var array
     */
    protected $metadatas = array();

    /**
     * The attribute collection.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * The current active menu.
     *
     * @var string
     */
    protected $menu = '';

    /**
     * The term collection.
     *
     * @var array
     */
    protected $_collection;

    /**
     * The user model.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $userModel;

    /**
     * The term model.
     *
     * @var Sule\Kotakin\Models\TermInterface
     */
    protected $termModel;

    /**
     * The collection breadcrumbs.
     *
     * @var array
     */
    protected $collectionCrumbs = array();

    /**
     * The path collection.
     *
     * @var array
     */
    protected $_paths;

    /**
     * The user collection.
     *
     * @var array
     */
    protected $_users;

    /**
     * The user slug.
     *
     * @var string
     */
    protected $_userSlug;

    /**
     * The available file types.
     *
     * @var array
     */
    protected $_fileTypes;

    /**
     * The disk free space.
     *
     * @var int
     */
    protected $_diskFreeSpace;

    /**
     * The disk capacity.
     *
     * @var int
     */
    protected $_diskCapacity;

    /**
     * Is user allowed to upload in this collection.
     *
     * @var bool
     */
    protected $_isAllowUserUpload;

    /**
     * Set the user collection.
     *
     * @param array $users
     * @return void
     */
    public function setUsers(Array $users)
    {
        $this->_users = $users;
    }

    /**
     * Get the user collection.
     *
     * @return array
     */
    public function users()
    {
        return $this->_users;
    }

    /**
     * Set the user slug.
     *
     * @param string $slug
     * @return void
     */
    public function setUserSlug($slug)
    {
        $this->_userSlug = $slug;
    }

    /**
     * Get the user slug.
     *
     * @return string
     */
    public function userSlug()
    {
        return $this->_userSlug;
    }

    /**
     * Set the user template.
     *
     * @param Sule\Kotakin\Templates\UserInterface $user
     * @return void
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Get the user template.
     *
     * @return Sule\Kotakin\Templates\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the term template.
     *
     * @param Sule\Kotakin\Templates\TermInterface $term
     * @return void
     */
    public function setTerm(TermInterface $term)
    {
        $this->term = $term;
    }

    /**
     * Get the term template.
     *
     * @return Sule\Kotakin\Templates\TermInterface
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Set the folder template.
     *
     * @param Sule\Kotakin\Templates\FolderInterface $folder
     * @return void
     */
    public function setFolder(FolderInterface $folder)
    {
        $this->folder = $folder;
    }

    /**
     * Get the folder template.
     *
     * @return Sule\Kotakin\Templates\FolderInterface
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set the document template.
     *
     * @param Sule\Kotakin\Templates\DocumentInterface $doc
     * @return void
     */
    public function setDoc(DocumentInterface $doc)
    {
        $this->doc = $doc;
    }

    /**
     * Get the document template.
     *
     * @return Sule\Kotakin\Templates\DocumentInterface
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * Set the document link template.
     *
     * @param Sule\Kotakin\Templates\DocumentLinkInterface $docLink
     * @return void
     */
    public function setDocLink(DocumentLinkInterface $docLink)
    {
        $this->docLink = $docLink;
    }

    /**
     * Get the document link template.
     *
     * @return Sule\Kotakin\Templates\DocumentLinkInterface
     */
    public function getDocLink()
    {
        return $this->docLink;
    }

    /**
     * Set the file template.
     *
     * @param Sule\Kotakin\Templates\FileInterface $user
     * @return void
     */
    public function setFile(FileInterface $file)
    {
        $this->file = $file;
    }

    /**
     * Get the file template.
     *
     * @return Sule\Kotakin\Templates\FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the email template.
     *
     * @param Sule\Kotakin\Templates\EmailInterface $user
     * @return void
     */
    public function setEmail(EmailInterface $email)
    {
        $this->email = $email;
    }

    /**
     * Get the email template.
     *
     * @return Sule\Kotakin\Templates\EmailInterface
     */
    public function getEmail()
    {
        return $this->email;
    }

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
     * Get current config value.
     *
     * @param string $key
     * @param string $formValue
     * @return string
     */
    public function config($key, $formValue = '')
    {
        if ( ! empty($formValue))
            return $formValue;

        if (strpos($key, 'mail_from') !== false) {
            $config = $this->getKotakin()->config('mail_from');
            $config = unserialize($config);

            if (isset($config[str_replace('mail_from_', '', $key)]))
                return $config[str_replace('mail_from_', '', $key)];
        } else {
            $config = $this->getKotakin()->config($key);
        }

        return $config;
    }

    /**
     * Set the current active menu.
     *
     * @param string $menu
     * @return void
     */
    public function setActiveMenu($menu)
    {
        return $this->menu = $menu;
    }

    /**
     * Get the current active menu.
     *
     * @param string $menu
     * @return bool
     */
    public function isActiveMenu($menu)
    {
        return ($this->menu == $menu);
    }

    /**
     * Check if disk info is available.
     *
     * @return bool
     */
    public function diskInfoAvailable()
    {
        return ($this->diskFreeSpace() !== false and $this->diskCapacity() !== false);
    }

    /**
     * Return system disk free space.
     *
     * @param bool $useBytes
     * @return string | bool
     */
    public function diskFreeSpace($useBytes = false)
    {
        if (is_null($this->_diskFreeSpace)) {
            if ( ! function_exists('disk_free_space')) {
                $this->_diskFreeSpace = 0;
                return false;
            }

            $this->_diskFreeSpace = disk_free_space(base_path());

            if ($this->_diskFreeSpace === false)
                $this->_diskFreeSpace = 0;
        }

        if ($useBytes)
            return $this->_diskFreeSpace;
        
        $utility = $this->getKotakin()->getUtility();
        return $utility->humanReadableFileSize($this->_diskFreeSpace);
    }

    /**
     * Return system disk capacity.
     *
     * @param bool $useBytes
     * @return string | bool
     */
    public function diskCapacity($useBytes = false)
    {
        if (is_null($this->_diskCapacity)) {
            if ( ! function_exists('disk_total_space')) {
                $this->_diskCapacity = 0;
                return false;
            }

            $this->_diskCapacity = disk_total_space(base_path());

            if ($this->_diskCapacity === false)
                $this->_diskCapacity = 0;
        }

        if ($useBytes)
            return $this->_diskCapacity;

        $utility = $this->getKotakin()->getUtility();
        return $utility->humanReadableFileSize($this->_diskCapacity);
    }

    /**
     * Return system disk used space.
     *
     * @param bool $useBytes
     * @param bool $usePercentage
     * @return string
     */
    public function diskUsedSpace($useBytes = false, $usePercentage = false)
    {
        $used = $this->diskCapacity(true) - $this->diskFreeSpace(true);

        if ($useBytes)
            return $used;

        if ($usePercentage)
            return round(($used * 100) / $this->diskCapacity(true));
        
        $utility = $this->getKotakin()->getUtility();
        return $utility->humanReadableFileSize($used);
    }

    /**
     * Return system max file upload size.
     *
     * @param bool $useBytes
     * @return string
     */
    public function maxFileUploadSize($useBytes = false)
    {
        $utility = $this->getKotakin()->getUtility();
        return $utility->humanReadableFileSize($utility->getBytes(ini_get('upload_max_filesize')));
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
     * Set term model.
     *
     * @param Sule\Kotakin\Models\TermInterface
     * @return void
     */
    public function setCollectionParentTerm(TermModelInterface $term)
    {
        $this->termModel = $term;
    }

    /**
     * Get term model.
     *
     * @return Sule\Kotakin\Models\TermInterface
     */
    protected function getTermModel()
    {
        return $this->termModel;
    }

    /**
     * Get current term model id.
     *
     * @return int
     */
    public function getCurrentFolderId()
    {
        if ( ! is_null($this->termModel))
            return $this->termModel->getId();

        return 0;
    }

    /**
     * Set collection breadcrumbs.
     *
     * @param array $crumbs
     * @return void
     */
    public function setCollectionBreadcrumbs(array $crumbs)
    {
        $this->collectionCrumbs = $crumbs;
    }

    /**
     * Return list of breadcrumb items.
     *
     * @return string
     */
    public function breadcrumbs($itemFormat, $activeClass = 'active')
    {
        if ( ! empty($this->collectionCrumbs)) {
            $items = array(sprintf($itemFormat, '', '', $this->getAttribute('brand')));
        } else {
            $items = array(sprintf($itemFormat, $activeClass, '', $this->getAttribute('brand')));
        }

        if ( ! empty($this->collectionCrumbs)) {
            foreach ($this->collectionCrumbs as $index => $item) {
                $items[] = sprintf($itemFormat, '', '/'.$item->object()->permalink(), $item->object()->name());
            }
        }

        if ( ! empty($items))
            return implode($items);
        else
            return '';
    }

    /**
     * Check if in root collection.
     *
     * @return bool
     */
    public function isRootCollection()
    {
        return ( ! $this->getTermModel());
    }

    /**
     * Check if user allowed to upload in this collection.
     *
     * @return bool
     */
    public function isAllowUserUpload()
    {
        if (is_null($this->_isAllowUserUpload)) {
            $termModel = $this->getTermModel();

            if ( ! is_null($termModel)) {
                $folder = $this->getKotakin()->getFolder()
                                ->find($termModel->getAttribute('object_id'));

                if ( ! is_null($folder)) {
                    $this->_isAllowUserUpload = (bool) $folder->getAttribute('user_upload');
                }

                unset($folder);
            }

            unset($termModel);
        }

        return $this->_isAllowUserUpload;
    }

    /**
     * Return list of all terms.
     *
     * @return array
     */
    public function collection()
    {
        if (is_null($this->_collection)) {
            $this->_collection = array();
            $userModel = $this->getUserModel();

            if ($userModel) {
                $this->userCollection();
            } else {
                $this->adminCollection();
            }

            unset($userModel);
        }

        return $this->_collection;
    }

    /**
     * Set the user collection.
     *
     * @return void
     */
    protected function userCollection()
    {
        $userModel = $this->getUserModel();
        $termModel = $this->getTermModel();

        $termIds = array();
        $shares  = $userModel->shares;

        if (count($shares) > 0) {
            foreach ($shares as $item) {
                $termIds[] = $item->getAttribute('term_id');
            }
        }

        unset($shares);
        
        if ( ! empty($termIds)) {
            if (is_null($termModel)) {
                $this->_collection = $this->getKotakin()->getTerm()
                                            ->where('parent_id', '=', 0)
                                            ->whereIn('id', $termIds)
                                            ->orderBy('is_folder', 'desc')
                                            ->orderBy('name', 'asc')
                                            ->get();
            } else {
                $this->_collection = $this->getKotakin()->getTerm()
                                            ->where('parent_id', '=', $termModel->getId())
                                            ->whereIn('id', $termIds)
                                            ->orderBy('is_folder', 'desc')
                                            ->orderBy('name', 'asc')
                                            ->get();
            }
        }

        if (count($this->_collection) > 0) {
            foreach ($this->_collection as $index => $term) {
                $this->_collection[$index] = $this->getTerm()->newInstance();
                $this->_collection[$index]->setKotakin($this->getKotakin());
                $this->_collection[$index]->setTerm($term);
                $this->_collection[$index]->setForUser($this->getUserModel());
            }
        }
    }

    /**
     * Set the admin collection.
     *
     * @return void
     */
    protected function adminCollection()
    {
        $termModel = $this->getTermModel();

        if (is_null($termModel)) {
            $this->_collection = $this->getKotakin()->getTerm()
                                        ->where('parent_id', '=', 0)
                                        ->orderBy('is_folder', 'desc')
                                        ->orderBy('name', 'asc')
                                        ->get();
        } else {
            $this->_collection = $this->getKotakin()->getTerm()
                                        ->where('parent_id', '=', $termModel->getId())
                                        ->orderBy('is_folder', 'desc')
                                        ->orderBy('name', 'asc')
                                        ->get();
        }

        if (count($this->_collection) > 0) {
            foreach ($this->_collection as $index => $term) {
                $this->_collection[$index] = $this->getTerm()->newInstance();
                $this->_collection[$index]->setKotakin($this->getKotakin());
                $this->_collection[$index]->setTerm($term);
            }
        }
    }

    /**
     * Return all available folder paths.
     *
     * @return array
     */
    public function paths()
    {
        if (is_null($this->_paths)) {
            $this->_paths = $this->collectPaths(0, array('/'));
        }

        return $this->_paths;
    }

    /**
     * Collect all available folder paths.
     *
     * @param int $parentId
     * @param array $paths
     * @return array
     */
    protected function collectPaths($parentId = 0, $paths = array())
    {
        $terms = $this->getKotakin()->getTerm()
                        ->where('parent_id', '=', $parentId)
                        ->orderBy('is_folder', 'desc')
                        ->orderBy('name', 'asc')
                        ->get();

        if (count($terms) > 0) {
            foreach ($terms as $term) {
                $folder = $this->getKotakin()->getFolder()->newQuery()
                                ->where('id', '=', $term->getAttribute('object_id'))
                                ->first();

                if ($folder) {
                    $paths[] = '/'.$folder->getAttribute('slug').'/';
                }

                $paths = $this->collectPaths($term->getAttribute('id'), $paths);
            }
        }

        return $paths;
    }

    /**
     * Set available file types.
     *
     * @param array $types
     * @return void
     */
    public function setFileTypes(Array $types)
    {
        $this->_fileTypes = $types;
    }

    /**
     * Return available file types.
     *
     * @return array
     */
    public function fileTypes()
    {
        return $this->_fileTypes;
    }

    /**
     * Set the metadata.
     *
     * @param string $str
     * @param string $placement
     * @return Sule\Kotakin\Templates\PageInterface
     */
    public function setMetadata($str, $placement = 'header')
    {
        $this->metadatas[$placement][] = $str;

        return $this;
    }

    /**
     * Get the metadata collection.
     *
     * @param string $placement
     * @return array
     */
    public function getMetadata($placement = 'header')
    {
        if (isset($this->metadatas[$placement]))
            return $this->metadatas[$placement];

        return array();
    }

    /**
     * Get an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $inAttributes = array_key_exists($key, $this->attributes);

        // If the key references an attribute, we can just go ahead and return the
        // plain attribute value. This allows every attribute to
        // be dynamically accessed through the _get method without accessors.
        if ($inAttributes or $this->hasGetMutator($key))
            return $this->getAttributeValue($key);
    }

    /**
     * Get a plain attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval to a form that is more useful for usage.
        if ($this->hasGetMutator($key))
            return $this->mutateAttribute($key, $value);

        return $value;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        if (array_key_exists($key, $this->attributes))
            return $this->attributes[$key];
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get'.studly_case($key).'Attribute');
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get'.studly_case($key).'Attribute'}($value);
    }

    /**
     * Set a given attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return Sule\Kotakin\Templates\PageInterface
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)){
            $method = 'set'.studly_case($key).'Attribute';

            return $this->{$method}($value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set'.studly_case($key).'Attribute');
    }

    /**
     * Get all of the current attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @return Sule\Kotakin\Templates\PageInterface
     */
    public function setRawAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Dynamically retrieve attributes.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute exists.
     *
     * @param  string  $key
     * @return void
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset an attribute.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

}
