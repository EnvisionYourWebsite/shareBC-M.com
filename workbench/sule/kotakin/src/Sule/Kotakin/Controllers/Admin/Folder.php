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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

use Sule\Kotakin\Controllers\Admin\Base;
use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserInterface;

use Sule\Kotakin\Models\FolderInterface;
use Sule\Kotakin\Models\TermInterface;

use Alchemy\Zippy\Zippy;

use Sule\Kotakin\Models\FolderExistsException;
use Sule\Kotakin\Models\TermSharingExistsException;
use Alchemy\Zippy\Exception\NotSupportedException;
use Alchemy\Zippy\Exception\RunTimeException;
use Alchemy\Zippy\Exception\InvalidArgumentException;

class Folder extends Base
{

    /**
     * The current user.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $user;

    /**
     * The folder validation rules.
     *
     * @var array
     */
    protected $validationRules = array(
        'name' => array(
            'required', 
            'regex:([a-zA-Z0-9_\-\.\,\s])'
        )
    );

    /**
     * The folder validation messages.
     *
     * @var array
     */
    protected $validationMessages = array();

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

        $this->user = $sentry->getUser();

        $this->validationMessages = array(
            'name.required' => $this->getUtility()->t('Folder nam is required.'),
            'name.regex'    => $this->getUtility()->t('Only a-z, A-Z, 0-9, _, -, . and , only allowed for folder name.')
        );
    }

    /**
     * Get the sentry.
     *
     * @return Cartalyst\Sentry\Sentry
     */
    protected function getSentry()
    {
        return $this->sentry;
    }

    /**
     * Get current user.
     *
     * @return Cartalyst\Sentry\Users\UserInterface
     */
    protected function getUser()
    {
        return $this->user;
    }

    /**
     * Create a new folder
     *
     * @param Sule\Kotakin\Models\FolderInterface $folder
     * @param Sule\Kotakin\Models\TermInterface $term
     * @return Illuminate\Routing\Redirector
     */
	public function create(FolderInterface $folder = null, TermInterface $term = null)
	{
        $validation = Validator::make(Input::all(), $this->validationRules, $this->validationMessages);

        $success = false;

        if ( ! $validation->fails()) {
            $success = true;

            $newFolder = $this->getKotakin()->getFolder()->newInstance();
            
            $newFolder->fill(array(
                'parent_id' => 0, 
                'type'      => 'folder', 
                'name'      => Input::get('name'), 
                'slug'      => Str::slug(Input::get('name'))
            ));

            $allowUserUpload = 1;

            if ( ! is_null($folder)) {
                $allowUserUpload = $folder->getAttribute('user_upload');

                $newFolder->setAttribute('parent_id', $folder->getId());
                $newFolder->setAttribute('slug', $folder->getAttribute('slug').'/'.$newFolder->getAttribute('slug'));
                $newFolder->setAttribute('user_upload', $allowUserUpload);
            }
            
            if ( ! $newFolder->save()) {
                $success = false;
            }

            if ($success) {
                $newTerm = $this->getKotakin()->getTerm()->newInstance();
            
                $data = array(
                    'parent_id' => 0, 
                    'object_id' => $newFolder->getId(), 
                    'author_id' => $this->getUser()->getId(), 
                    'name'      => Input::get('name'), 
                    'is_folder' => 1
                );

                $newTerm->fill($data);

                if ( ! is_null($term)) {
                    $newTerm->setAttribute('parent_id', $term->getId());
                }
                
                if ( ! $newTerm->save()) {
                    $newFolder->delete();

                    $success = false;
                } else {
                    if ( ! is_null($term)) {
                        if (count($term->shares) > 0) {
                            $users = array();
                            
                            foreach ($term->shares as $item) {
                                $users[] = $item->getAttribute('user_id');
                            }

                            $this->share($newTerm, $users, $allowUserUpload);

                            unset($users);
                        }
                    }
                }
            }
        }

        if ( ! $success) {
            $errorList = $validation->errors()->toArray();
            $errors = array();

            foreach ($errorList as $error) {
                $errors[] = $error[0];
            }

            Session::flash('error', implode(', ', $errors));

            unset($errorList);
            unset($errors);
        } else {
            Session::flash('success', sprintf($this->getUtility()->t('Folder "%s" successfully created.'), $newFolder->getAttribute('name')));
        }

        return Redirect::to(URL::current());
	}

    /**
     * Rename a folder
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param string $name
     * @return Illuminate\Routing\Redirector
     */
    public function rename(TermInterface $term, $name)
    {
        $validation = Validator::make(array(
            'name' => $name
        ), $this->validationRules, $this->validationMessages);

        $success = false;
        $errors = array();

        if ( ! $validation->fails()) {
            $success = true;

            $folder = $this->getKotakin()->getFolder()->newQuery()
                            ->where('id', '=', $term->getAttribute('object_id'))->first();

            if ($folder) {
                $previousName = $folder->getAttribute('name');
                $previousSlug = $folder->getAttribute('slug');

                $folder->fill(array(
                    'name' => $name, 
                    'slug' => str_replace(Str::slug($previousName), Str::slug($name), $previousSlug)
                ));

                try {
                    $folder->save();
                } catch (FolderExistsException $e) {
                    $success = false;

                    $errors[] = sprintf($this->getUtility()->t('Folder "%s" is already exist.'), $name);
                }
            }
        }

        if ($success) {
            $term->fill(array(
                'name' => $name
            ));
            $term->save();
        }

        if ( ! $success) {
            $errorList = $validation->errors()->toArray();

            foreach ($errorList as $error) {
                $errors[] = $error[0];
            }

            Session::flash('error', implode(', ', $errors));

            unset($errorList);
            unset($errors);
        } else {
            Session::flash('success', sprintf($this->getUtility()->t('Folder "%s" successfully renamed to "%s".'), $previousName, $name));
        }

        return Redirect::to(URL::current());
    }

    /**
     * Delete a folder with all content
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param bool $returnBoolean
     * @return Illuminate\Routing\Redirector
     */
    public function delete(TermInterface $term, $returnBoolean = true)
    {
        $success = false;

        $name   = $term->getAttribute('name');
        $childs = $term->childs;

        $folder = $this->getKotakin()->getFolder()->newQuery()
                        ->where('id', '=', $term->getAttribute('object_id'))->first();

        if ($folder) {
            $success = $folder->delete();
        }

        if ($success) {
            $success = $term->delete();
        }

        if ($success and count($childs) > 0) {
            foreach ($childs as $term) {
                if ($term->getAttribute('is_folder') == 1) {
                    $this->delete($term, false);
                } else {
                    $doc = new Document($this->getKotakin(), $this->getSentry());

                    $theDoc = $this->getKotakin()->getDoc()->newQuery()
                                    ->where('id', '=', $term->getAttribute('object_id'))->first();

                    if ($theDoc) {
                        $doc->delete($theDoc->getAttribute('slug'));
                    } else {
                        $doc->delete();
                        $this->removeShares($term);
                        $term->delete();
                    }
                }
            }
        }

        if ( ! $returnBoolean) {
            if ( ! $success) {
                Session::flash('error', sprintf($this->getUtility()->t('Failed to delete "%s" folder.'), $name));
            } else {
                Session::flash('success', sprintf($this->getUtility()->t('Folder "%s" successfully deleted.'), $name));
            }

            return Redirect::to(URL::current());
        }

        return $success;
    }

    /**
     * Share specified folder term
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param array $users
     * @param bool $allowUserUpload
     * @return Illuminate\Routing\Redirector
     */
    public function share(TermInterface $term, Array $users, $allowUserUpload)
    {
        $name = $term->getAttribute('name');
        
        list($success, $removeShared) = $this->shareFolder($term, $users, $allowUserUpload);

        if ( ! $success) {
            Session::flash('error', sprintf($this->getUtility()->t('Failed to share "%s" folder.'), $name));
        } else {
            if ($removeShared) {
                Session::flash('success', sprintf($this->getUtility()->t('Folder "%s" successfully unshared.'), $name));
            } else {
                Session::flash('success', sprintf($this->getUtility()->t('Folder "%s" successfully shared.'), $name));
            }
        }

        return Redirect::to(URL::current());
    }

    /**
     * Share specified folder term
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param array $users
     * @param bool $allowUserUpload
     * @return array
     */
    public function shareFolder(TermInterface $term, Array $users, $allowUserUpload)
    {
        $success      = false;
        $removeShared = false;

        $childs = $term->childs;

        if (count($childs) > 0) {
            foreach ($childs as $item) {
                if ($item->getAttribute('is_folder') == 1) {
                    $this->shareFolder($item, $users, $allowUserUpload);
                } else {
                    $this->shareFile($item, $users);
                }
            }
        }

        unset($childs);

        $folder = $this->getKotakin()->getFolder()->newQuery()
                        ->where('id', '=', $term->getAttribute('object_id'))->first();

        if ($folder) {
            $this->itemShare($term, $users);

            if (empty($users)) {
                $folder->setAttribute('is_shared', 0);
                $removeShared = true;
            } else {
                $folder->setAttribute('is_shared', 1);
            }

            $folder->setAttribute('user_upload', $allowUserUpload);

            $folder->save();

            $success = true;
        }

        return array($success, $removeShared);
    }

    /**
     * Share specified doc term
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param array $users
     * @return void
     */
    public function shareFile(TermInterface $term, Array $users)
    {
        $doc = $this->getKotakin()->getDoc()->newQuery()
                        ->where('id', '=', $term->getAttribute('object_id'))->first();

        if ($doc) {
            $this->itemShare($term, $users);
        }

        unset($doc);
    }

    /**
     * Share specified term item
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param array $users
     * @return void
     */
    protected function itemShare(TermInterface $term, Array $users)
    {
        $toRemoveUser  = array();
        $previousUsers = $term->shares;

        if (count($previousUsers) > 0) {
            foreach ($previousUsers as $share) {
                if ( ! in_array($share->getAttribute('user_id'), $users)) {
                    $toRemoveUser[] = $share;
                }
            }
        }

        if (! empty($users)) {
            foreach ($users as $userId) {
                try {
                    $item = $this->getKotakin()->getTermSharing()->newInstance();
                    $item->fill(array(
                        'term_id' => $term->getId(),
                        'user_id' => $userId
                    ));
                    $item->save();
                } catch (TermSharingExistsException $e) {}
            }
        }

        if ( ! empty($toRemoveUser)) {
            foreach ($toRemoveUser as $item) {
                $item->delete();
            }
        }

        unset($previousUsers);
        unset($toRemoveUser);
    }

    /**
     * Share specified folder term
     *
     * @param Sule\Kotakin\Models\FolderInterface $folder
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param bool $return
     * @return Illuminate\Http\Response | Illuminate\Routing\Redirector | Sule\Kotakin\Models\MediaInterface
     */
    public function download(FolderInterface $folder, TermInterface $term, $return = false)
    {
        $user       = $this->getUser();
        $folderName = 'kotakin';
        $path       = storage_path().'/'.$folderName;
        $fileName   = $user->getId().'.'.$term->getId().'.'.$folder->getId().'.'.time();

        if ( ! File::isDirectory($path))
            File::makeDirectory($path, Config::get('kotakin::chmod_folder'), true);

        $error = '';

        $zippy = Zippy::load();

        try {
            $items   = $this->createArchive($term, $term->getAttribute('name'));
            $archive = $zippy->create($path.'/'.$fileName.'.zip', $items);
            unset($items);

            if (File::exists($path.'/'.$fileName.'.zip')) {
                $media = $this->getKotakin()->getMedia()->newInstance();
                $media->fill(array(
                    'parent_id'   => 0,
                    'object_id'   => $term->getId(),
                    'object_type' => 'Archive',
                    'author_id'   => $user->getId(),
                    'type'        => 'original',
                    'title'       => $term->getAttribute('name').'.zip',
                    'alt_text'    => $term->getAttribute('name').'.zip',
                    'path'        => $folderName,
                    'filename'    => $fileName,
                    'extension'   => 'zip',
                    'mime_type'   => 'application/zip',
                    'size'        => File::size($path.'/'.$fileName.'.zip')
                ));

                $media->save();

                if ( ! $return) {
                    return Response::download($path.'/'.$fileName.'.zip', $term->getAttribute('name').'.zip', array(
                        'Content-type' => 'application/zip'
                    ));
                } else {
                    return $media;
                }
            } else {
                $error = $this->getUtility()->t('Unable to find the compressed file.');
            }
        } catch (NotSupportedException $e) {
            $error = $e->getMessage();
        } catch (RunTimeException $e) {
            $error = $e->getMessage();
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        }

        if ( ! empty($error)) {
            Session::flash('error', sprintf($this->getUtility()->t('Unable to create archive because folder is empty or because following error: %s.'), $error));
        }
        
        if ($return)
            return false;

        return Redirect::to(URL::current());
    }

    /**
     * Create the archive
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param string $excludePath
     * @param string $parentPath
     * @param array $items
     * @param string $suffix
     * @return Illuminate\Routing\Redirector
     */
    protected function createArchive(TermInterface $term, $excludePath, $parentPath = '', $items = array(), $suffix = '')
    {
        if ($term->getAttribute('is_folder')) {
            if ( ! $this->inSuperAdminGroup($this->getUser()) or ! $this->inSuperAdminGroup($this->getUser()))
                if ( ! $this->isFolderSharedToThisUser($term))
                    return $items;

            $folder = $term->getAttribute('name');

            $parentPath .= $folder.'/';

            $childs = $term->childs;
            if (count($childs) > 0) {
                $fileNames = array();

                foreach ($childs as $item) {
                    if ($item->getAttribute('is_file') == 1) {
                        $fileName = $item->getAttribute('name');
                        
                        if (isset($fileNames[$fileName])) {
                            $fileNames[$fileName] += 1;
                        } else {
                            $fileNames[$fileName] = 1;
                        }

                        $suffix = '';
                        if ($fileNames[$fileName] > 1)
                            $suffix = $fileNames[$fileName];

                        $items = $this->createArchive($item, $excludePath, $parentPath, $items, $suffix);
                    } else {
                        $items = $this->createArchive($item, $excludePath, $parentPath, $items);
                    }
                }
            }
        } else {
            $doc = $this->getKotakin()->getDoc()->newQuery()
                        ->where('id', '=', $term->getAttribute('object_id'))
                        ->first();

            if ($doc) {
                $media = $doc->media;

                if ($media) {
                    $extension = $media->getAttribute('extension');
                    $fileName  = str_replace('.'.$extension, '', $media->getAttribute('title'));

                    if ( ! empty($suffix))
                        $fileName .= ' ('.$suffix.')';

                    $title = str_replace($excludePath.'/', '', $parentPath).$fileName.'.'.$extension;
                    $items[$title] = storage_path().'/'.$media->getAttribute('path').'/'.$media->getAttribute('filename').'.'.$media->getAttribute('extension');
                }
            }
        }

        return $items;
    }

    /**
     * Check if term shared to this user
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @return bool
     */
    protected function isFolderSharedToThisUser(TermInterface $term)
    {
        $shares = $term->shares;
        $isShared = false;

        if (count($shares) > 0) {
            foreach ($shares as $item) {
                if ($item->getAttribute('user_id') == $this->getUser()->getId()) {
                    $isShared = true;
                    break;
                }
            }
        }

        unset($shares);

        return $isShared;
    }

    /**
     * Remove shares if exist
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @return void
     */
    protected function removeShares(TermInterface $term)
    {
        $shares = $term->shares;

        if (count($shares) > 0) {
            foreach ($shares as $share) {
                $share->delete();
            }
        }

        unset($shares);
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

    /**
     * Check if user is in admin group
     *
     * @param Cartalyst\Sentry\Users\UserInterface $user
     * @return bool
     */
    protected function inAdminGroup(UserInterface $user)
    {
        $allowed = false;

        try {
            $group = $this->getSentry()->getGroupProvider()->findByName('Admin');

            if ($user->inGroup($group))
                $allowed = true;

            unset($group);
        } catch (GroupNotFoundException $e) {}

        return $allowed;
    }

}
