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
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Sule\Kotakin\Controllers\Admin\Base;
use Sule\Kotakin\Kotakin;
use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserInterface;

use Thapp\JitImage\JitImage;
use Thapp\JitImage\Cache\CacheInterface;

use Sule\Kotakin\Models\MediaInterface;
use Sule\Kotakin\Models\TermInterface;

use stdClass;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sule\Kotakin\Models\MediaExistsException;
use Cartalyst\Sentry\Users\UserNotFoundException;

class Document extends Base
{
    /**
     * The JitImage.
     *
     * @var Thapp\JitImage\JitImage
     */
    protected $jitImage;

    /**
     * The JitImage Cache.
     *
     * @var Thapp\JitImage\Cache\CacheInterface
     */
    protected $jitImageCache;

    /**
     * The currenty user.
     *
     * @var Cartalyst\Sentry\Users\UserInterface
     */
    protected $user;

    /**
     * The current user slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * Current term
     *
     * @var Sule\Kotakin\Models\TermInterface
     */
    protected $term;

    /**
     * The preview sizes.
     *
     * @var array
     */
    protected $previewSizes = array(
        'icon'   => '25x25',
        'thumb'  => '50x50',
        'small'  => '100x100',
        'medium' => '800x800'
    );

	/**
     * Create a new instance.
     *
     * @param Sule\Kotakin\Kotakin $kotakin
     * @param Cartalyst\Sentry\Sentry $sentry
     * @param Thapp\JitImage\JitImage $jitImage
     * @param Thapp\JitImage\Cache\CacheInterface $jitImageCache
     * @param string $slug
     * @return void
     */
    public function __construct(
        Kotakin $kotakin, 
        Sentry $sentry, 
        JitImage $jitImage = null, 
        CacheInterface $jitImageCache = null, 
        $slug = null
    )
    {
        parent::__construct($kotakin, $sentry);
        
        $this->user          = $sentry->getUser();
        $this->jitImage      = $jitImage;
        $this->jitImageCache = $jitImageCache;
        $this->slug          = $slug;
    }

    /**
     * Get the JitImage.
     *
     * @return Thapp\JitImage\JitImage
     */
    protected function getJitImage()
    {
        return $this->jitImage;
    }

    /**
     * Get the JitImage Cache.
     *
     * @return Thapp\JitImage\Cache\CacheInterface
     */
    protected function getJitImageCache()
    {
        return $this->jitImageCache;
    }

    /**
     * Get the current user.
     *
     * @return Cartalyst\Sentry\Users\UserInterface
     */
    protected function getUser()
    {
        return $this->user;
    }

    /**
     * Get current user slug.
     *
     * @return string
     */
    protected function getSlug()
    {
        return $this->slug;
    }

    /**
     * Show file page
     *
     * @param string $slug
     * @return Illuminate\View\View | Illuminate\Http\Response
     */
    public function view($slug)
    {
        $docSlug  = $this->getUtility()->xssClean($slug);
        $download = (bool) Input::get('dl', false);
        $source   = (bool) Input::get('source', false);

        $doc = $this->getKotakin()->getDoc()->where('slug', '=', $docSlug)->first();
        if ( ! $doc) {
            return App::abort(404);
        }

        if ( ! $doc->media) {
            return App::abort(404);
        }

        if ($download) {
            return $this->download($doc->media, $source);
        }
    }

    /**
     * Download the specified file
     *
     * @param Sule\Kotakin\Models\MediaInterface $media
     * @param bool $source
     * @return Illuminate\Http\Response
     */
    protected function download(MediaInterface $media, $source = false)
    {
        $file  = storage_path().'/'.$media->getAttribute('path').'/'.$media->getAttribute('filename').'.'.$media->getAttribute('extension');
        $title = $media->getAttribute('title');
        $title = preg_replace('/[^(\x20-\x7F)]*/', '', $title);

        if ($source) {
            $handle = fopen($file, 'rb');
            $contents = fread($handle, filesize($file));
            fclose($handle);

            return Response::make($contents, 200, array(
                'Content-type' => $media->getAttribute('mime_type'),
                'Content-Disposition' => 'inline; filename="'.$title.'"',
                'Content-Length' => $media->getAttribute('size')
            ));
         } else {
            return Response::download($file, $title, array(
                'Content-type' => $media->getAttribute('mime_type')
            ));
        }
    }

    /**
     * Rename a file
     *
     * @param Sule\Kotakin\Models\TermInterface $term
     * @param string $name
     * @return Illuminate\Routing\Redirector
     */
    public function rename(TermInterface $term, $name)
    {
        $success = false;

        $doc = $this->getKotakin()->getDoc()->newQuery()
                    ->where('id', '=', $term->getAttribute('object_id'))->first();

        if ($doc) {
            $media = $doc->media;

            if ($media) {
                $previousName = $media->getAttribute('title');
                $extension    = pathinfo($previousName, PATHINFO_EXTENSION);
                $newName      = $name.'.'.$extension;

                $media->fill(array(
                    'title'    => $newName, 
                    'alt_text' => $newName
                ));

                $success = $media->save();
            }
        }

        if ($success) {
            $thumbs = $media->childs;
            if (count($thumbs) > 0) {
                foreach ($thumbs as $media) {
                    $media->fill(array(
                        'title'    => $newName, 
                        'alt_text' => $newName
                    ));

                    $media->save();
                }
            }
        }

        if ($success) {
            $term->fill(array(
                'name' => $newName
            ));
            $term->save();
        }

        if ( ! $success) {
            Session::flash('error', sprintf($this->getUtility()->t('Failed to rename "%s" file to "%s".'), $previousName, $newName));
        } else {
            Session::flash('success', sprintf($this->getUtility()->t('File "%s" successfully renamed to "%s".'), $previousName, $newName));
        }

        return Redirect::to(URL::current());
    }

    /**
     * Upload file
     *
     * @return Illuminate\Http\Response
     */
    public function upload()
    {
        $response        = new stdClass;
        $response->files = array();

        $termId = Input::get('folder', 0);
        $files  = Input::file('files');

        $subFolder = date('Ymd');
        $folder    = Config::get('kotakin::file_folder').'/'.$subFolder;
        $path      = storage_path().'/'.$folder;

        if ( ! File::isDirectory($path))
            File::makeDirectory($path, Config::get('kotakin::chmod_folder'), true);

        if ( ! empty($files)) {
            foreach ($files as $index => $file) {
                $response->files[] = $this->processFile(
                    $index, 
                    $termId, 
                    $path, 
                    $folder, 
                    $subFolder, 
                    $file
                );
            }
        }

        return Response::json($response);
    }

    /**
     * Delete file
     *
     * @param string $slug
     * @return void
     */
    public function delete($slug)
    {
        if ( ! $this->isAllowed()) {
            return App::abort(404);
        }

        $docSlug = $this->getUtility()->xssClean($slug);

        $doc = $this->getKotakin()->getDoc()->where('slug', '=', $docSlug)->first();
        if ( ! $doc) {
            return App::abort(404);
        }

        $user = $this->getUser();

        $term = $this->getKotakin()->getTerm()->where('object_id', '=', $doc->getId())
                                    ->where('is_file', '=', 1)->first();

        if ( ! $term) {
            return App::abort(404);
        }

        $media = $doc->media;

        if ( ! isset($media)) {
            return App::abort(404);
        }

        $term->delete();

        // Remove all thumbs
        if (count($doc->media->childs) > 0) {
            foreach ($doc->media->childs as $file) {
                @unlink(public_path().'/'.$file->getAttribute('path').'/'.$file->getAttribute('filename').'.'.$file->getAttribute('extension'));
                $file->delete();
            }
        }

        // Remove the original file
        @unlink(storage_path().'/'.$doc->media->getAttribute('path').'/'.$doc->media->getAttribute('filename').'.'.$doc->media->getAttribute('extension'));
        $doc->media->delete();

        $doc->delete();
    }

    /**
     * Process the uploaded file
     *
     * @param int $index;
     * @param int $termId;
     * @param string $path;
     * @param string $folder;
     * @param string $subFolder;
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return stdClass
     */
    protected function processFile(
        $index, 
        $termId, 
        $path, 
        $folder, 
        $subFolder, 
        UploadedFile $file
    )
    {
        $response         = new stdClass();
        $originalFileName = $file->getClientOriginalName();
        $extension        = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $mimeType         = $file->getClientMimeType();
        $size             = $file->getClientSize();

        $defaultData = array(
            'url'           => '',
            'thumbnail_url' => '',
            'name'          => $originalFileName,
            'type'          => $mimeType,
            'size'          => $size,
            'delete_url'    => '',
        );

        if ( ! empty($termId)) {
            if ( ! $this->isFolderStillExist($termId)) {
                $error = $this->getUtility()->t('The folder might be already deleted.');
                return $this->createResponse($defaultData, $error);
            }
        }

        if ( ! $this->isAllowed()) {
            $error = $this->getUtility()->t('Your session might be expired, reload / refresh the page to re-login.');
            return $this->createResponse($defaultData, $error);
        }

        $validationRules = array(
            'file' => 'required'
        );

        $userProfile     = $this->getUser()->profile;
        $allowedFileSize = 0;

        if ($userProfile) {
            $allowedFileTypes = $userProfile->getAttribute('allowed_file_types');
            if ( ! empty($allowedFileTypes)) {
                $validationRules['file'] .= '|mimes:'.$allowedFileTypes;
            }

            $allowedFileSize = $userProfile->getAttribute('max_upload_size');
        }

        unset($userProfile);

        $validation = Validator::make(array('file' => $file), $validationRules);
        $isSuccess = true;

        if ( ! $validation->fails()) {
            if ( ! $file->isValid()) {
                $error = sprintf($this->getUtility()->t('File is not accepted, please choose another one.'), $originalFileName);
                return $this->createResponse($defaultData, $error);
            }
        }

        if ( ! empty($allowedFileSize)) {
            if ($file->getClientSize() > $allowedFileSize) {
                $error = sprintf($this->getUtility()->t('File size is not accepted, please choose another one.'), $originalFileName);
                return $this->createResponse($defaultData, $error);
            }
        }

        $rawName  = sha1(md5(Crypt::encrypt($index.time().$this->getUser()->getId())));
        $fileName = $rawName.'.'.$extension;

        try {
            $file->move($path, $fileName);
        } catch (FileException $e) {
            $error = sprintf($this->getUtility()->t('Unable to process your uploaded file "%s": %s.'), $originalFileName, $e->getMessage());
            return $this->createResponse($defaultData, $error);
        }

        $dimension = $this->getUtility()->getDimension($path.'/'.$fileName);

        $media = $this->getKotakin()->getMedia()->newInstance();
        $media->fill(array(
            'parent_id'   => 0,
            'object_id'   => $this->getUser()->getId(),
            'object_type' => 'User',
            'author_id'   => $this->getUser()->getId(),
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
            
            $error = sprintf($this->getUtility()->t('Failed to save file "%s" informations.'), $originalFileName);
            return $this->createResponse($defaultData, $error);
        }

        if ($dimension['width'] > 0) {
            if ( ! $this->createThumbnails($index, $media, $subFolder, $this->previewSizes)) {
                $media->delete();
                @unlink($path.'/'.$fileName);

                $error = sprintf($this->getUtility()->t('Failed to create file "%s" thumbnails.'), $originalFileName);
                return $this->createResponse($defaultData, $error);
            }
        }
        
        $doc = $this->getKotakin()->getDoc()->newInstance();
        $doc->fill(array(
            'media_id' => $media->getId(),
            'slug'     => $this->getKotakin()->getUUID()->v5($this->getKotakin()->config('uuid'), $this->getUtility()->randString().time())
        ));
        if ( ! $doc->save()) {
            $media->delete();
            @unlink($path.'/'.$fileName);

            $error = sprintf($this->getUtility()->t('Failed to save file "%s" document informations.'), $originalFileName);
            return $this->createResponse($defaultData, $error);
        }

        $term = $this->getKotakin()->getTerm()->newInstance();
        $term->fill(array(
            'parent_id' => $termId,
            'object_id' => $doc->getId(),
            'author_id' => $this->getUser()->getId(),
            'name'      => $media->getAttribute('title'),
            'is_file'   => 1
        ));
        if ( ! $term->save()) {
            $doc->delete();
            $media->delete();
            @unlink($path.'/'.$fileName);

            $error = sprintf($this->getUtility()->t('Failed to save file "%s" document relation informations.'), $originalFileName);
            return $this->createResponse($defaultData, $error);
        }

        if ($this->term) {
            $this->shareIfNeeded($this->term, $term);
        }

        $fileTemplate = $this->getKotakin()->getTemplate()->getFile()->newInstance();
        $fileTemplate->setKotakin($this->getKotakin());
        $fileTemplate->setFile($media);

        $defaultData['url']           = URL::to('/admin/file/'.$doc->getAttribute('slug').'?dl=1');
        $defaultData['thumbnail_url'] = URL::to($fileTemplate->thumb('50x50')->permalink());
        $defaultData['delete_url']    = URL::to('/admin/file/'.$doc->getAttribute('slug'));

        return $this->createResponse($defaultData);
    }

    /**
     * Create upload file response
     *
     * @param array $data
     * @param string $error
     * @return bool
     */
    protected function createResponse($data, $error = '')
    {
        $data = array_merge(array(
            'url'           => '',
            'thumbnail_url' => '',
            'name'          => '',
            'type'          => '',
            'size'          => '',
            'delete_url'    => '',
        ), $data);

        $response                = new stdClass;
        $response->url           = $data['url'];
        $response->thumbnail_url = $data['thumbnail_url'];
        $response->name          = $data['name'];
        $response->type          = $data['type'];
        $response->size          = $this->getUtility()->humanReadableFileSize($data['size']);
        $response->delete_url    = $data['delete_url'];
        $response->delete_type   = 'DELETE';

        if ( ! empty($error))
            $response->error = $error;

        return $response;
    }

    /**
     * Create media thumbnails
     *
     * @param Sule\Kotakin\Models\MediaInterface $media
     * @param int $index
     * @param string $subFolder
     * @param array $sizes
     * @return bool
     */
    protected function createThumbnails($index, MediaInterface $media, $subFolder, $sizes)
    {
        $isSuccess        = true;
        $createdFiles     = array();
        $folder           = Config::get('kotakin::preview_folder').'/'.$subFolder;
        $path             = public_path().'/'.$folder;
        $fileName         = $index.'_'.$media->getId().'_'.time().'_'.$this->getUser()->getId();
        $extension        = $media->getAttribute('extension');

        $originalFilePath = storage_path().'/'.$media->getAttribute('path').'/'.$media->getAttribute('filename').'.'.$extension;

        if ( ! File::isDirectory($path))
            File::makeDirectory($path, Config::get('kotakin::chmod_folder'), true);

        foreach ($sizes as $size) {
            $newFileName    = $fileName.'_'.$size;
            $targetFilePath = $path.'/'.$newFileName.'.'.$extension;

            File::copy($originalFilePath, $targetFilePath);

            $thumb = $this->getKotakin()->getMedia()->newInstance();
            $thumb->fill(array(
                'parent_id'   => $media->getId(),
                'object_id'   => $this->getUser()->getId(),
                'object_type' => 'User',
                'author_id'   => $this->getUser()->getId(),
                'type'        => 'thumbnail',
                'title'       => $media->getAttribute('title'),
                'alt_text'    => $media->getAttribute('title'),
                'path'        => $folder,
                'filename'    => $newFileName,
                'extension'   => $extension,
                'mime_type'   => $media->getAttribute('mime_type'),
                'size'        => File::size($targetFilePath),
                'metadata'    => serialize($this->getUtility()->getDimension($targetFilePath))
            ));

            if ( ! $thumb->save()) {
                $isSuccess = false;
                break;
            } else {
                // Resize
                $heightWidth = explode('x', $size);
                $resizedFile = $this->getJitImage()->source($folder.'/'.$newFileName.'.'.$extension)->fit($heightWidth[0], $heightWidth[1]);
                if ($resizedFile !== false) {
                    $resizedFile = app_path().$resizedFile;
                    File::copy($resizedFile, $targetFilePath);
                    $this->getJitImageCache()->delete($folder.'/'.$newFileName.'.'.$extension);
                }
                unset($resizedFile);

                $createdFiles[] = $thumb;
            }
        }

        if ( ! $isSuccess) {
            if ( ! empty($createdFiles)) {
                foreach ($createdFiles as $item) {
                    @unlink(public_path().'/'.$item->getAttribute('path').'/'.$item->getAttribute('filename').'.'.$extension);
                    $item->delete();
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Share the file if needed
     *
     * @param Sule\Kotakin\Models\TermInterface $folderTerm
     * @param Sule\Kotakin\Models\TermInterface $fileTerm
     * @return void
     */
    protected function shareIfNeeded(TermInterface $folderTerm, TermInterface $fileTerm)
    {
        $shares = $folderTerm->shares;

        if (count($shares) > 0) {
            foreach ($shares as $share) {
                try {
                    $item = $this->getKotakin()->getTermSharing()->newInstance();
                    $item->fill(array(
                        'term_id' => $fileTerm->getId(),
                        'user_id' => $share->getAttribute('user_id')
                    ));
                    $item->save();
                } catch (TermSharingExistsException $e) {}
            }
        }

        unset($shares);
    }

    /**
     * Check if the folder still exist
     *
     * @param int $termId
     * @return bool
     */
    protected function isFolderStillExist($termId)
    {
        $this->term = $this->getKotakin()->getTerm()->newQuery()
                    ->where('id', '=', $termId)
                    ->where('is_folder', '=', 1)
                    ->first();

        if ( ! is_object($this->term))
            return false;

        return true;
    }

    /**
     * Check if user is in allowed to do something
     *
     * @return bool
     */
    protected function isAllowed()
    {
        if ($this->isLoggedIn()) {
            if ($this->inSuperAdminGroup($this->getUser())) {
                return true;
            }

            if ($this->inAdminGroup($this->getUser())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    protected function isLoggedIn()
    {
        if ($this->getSentry()->check())
            return true;

        return false;
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