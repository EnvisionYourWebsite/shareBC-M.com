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

use Sule\Kotakin\Templates\FileInterface;
use Sule\Kotakin\Kotakin as CoreKotakin;
use Sule\Kotakin\Models\MediaInterface;
use stdClass;

class File implements FileInterface
{
    /**
     * The Kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

    /**
     * The media file.
     *
     * @var Sule\Kotakin\Models\MediaInterface
     */
    protected $file;

    /**
     * The file author.
     *
     * @var Sule\Kotakin\Templates\UserInterface
     */
    protected $_author;

    /**
     * The file folder.
     *
     * @var Sule\Kotakin\Templates\FolderInterface
     */
    protected $_folder;

    /**
     * The metadata collection.
     *
     * @var array
     */
    protected $_metadatas;

    /**
     * The thumbnail collection.
     *
     * @var array
     */
    protected $_thumbs;

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
     * Set the file data.
     *
     * @param Sule\Kotakin\Models\MediaInterface $media
     * @return void
     */
    public function setFile(MediaInterface $media)
    {
        $this->file = $media;
    }

    /**
     * Return file id.
     *
     * @return int
     */
    public function id()
    {
        if (!is_null($this->file))
            return $this->file->getId();

        return 0;
    }

    /**
     * Return file parent id.
     *
     * @return int
     */
    public function parentId()
    {
        if (!is_null($this->file))
            return $this->file->getAttribute('parent_id');

        return 0;
    }

    /**
     * Return file author.
     *
     * @return Sule\Kotakin\Templates\UserInterface
     */
    public function author()
    {
        if (!is_null($this->file) and is_null($this->_author)) {
            $this->_author = $this->getKotakin()->getTemplate()->getUser();
            $this->_author->setKotakin($this->getKotakin());

            if ($this->file->author) {
                $this->_author->setUser($this->file->author);
            }
        }

        return $this->_author;
    }

    /**
     * Return file type.
     *
     * @return string
     */
    public function type()
    {
        if (!is_null($this->file))
            return $this->file->getAttribute('type');

        return '';
    }

    /**
     * Return file title.
     *
     * @return string
     */
    public function title()
    {
        if (!is_null($this->file))
            return $this->file->getAttribute('title');

        return '';
    }

    /**
     * Return file alt text.
     *
     * @return string
     */
    public function altText()
    {
        if (!is_null($this->file))
            return $this->file->getAttribute('alt_text');

        return '';
    }

    /**
     * Return file path.
     *
     * @return string
     */
    public function path()
    {
        if (!is_null($this->file))
            return $this->file->getAttribute('path');

        return '';
    }

    /**
     * Return file name.
     *
     * @return string
     */
    public function filename()
    {
        if (!is_null($this->file))
            return $this->file->getAttribute('filename');

        return '';
    }

    /**
     * Return file extension.
     *
     * @return string
     */
    public function extension()
    {
        if (!is_null($this->file))
            return $this->file->getAttribute('extension');

        return '';
    }

    /**
     * Return file permalink.
     *
     * @return string
     */
    public function permalink()
    {
        if (!is_null($this->file))
            return $this->path().'/'.$this->filename().'.'.$this->extension();

        return '';
    }

    /**
     * Return file mime type.
     *
     * @return string
     */
    public function mimeType()
    {
        if (!is_null($this->file))
            return $this->file->getAttribute('mime_type');

        return '';
    }

    /**
     * Return file size.
     *
     * @return string
     */
    public function size()
    {
        if (!is_null($this->file))
            return $this->getKotakin()->getUtility()->humanReadableFileSize($this->file->getAttribute('size'));

        return '';
    }

    /**
     * Return file width if image.
     *
     * @return int
     */
    public function width()
    {
        $data = $this->metadata();

        if (isset($data['width']))
            return $data['width'];

        return 0;
    }

    /**
     * Return file height if image.
     *
     * @return int
     */
    public function height()
    {
        $data = $this->metadata();

        if (isset($data['height']))
            return $data['height'];

        return 0;
    }

    /**
     * Return file metadata.
     *
     * @return array
     */
    public function metadata()
    {
        if (!is_null($this->file) and is_null($this->_metadatas)) {
            $this->_metadatas = $this->file->getAttribute('metadata');
            if ( ! empty($this->_metadatas))
                $this->_metadatas = unserialize($this->_metadatas);
        }

        return $this->_metadatas;
    }

    /**
     * Return file thumbnail.
     *
     * @param string $size
     * @param bool $icon
     * @return Sule\Kotakin\Templates\FileInterface
     */
    public function thumb($size = '100x100', $icon = false)
    {
        $thumbs = array();
        if ( ! $icon)
            $thumbs = $this->thumbs();

        if (count($thumbs) > 0) {
            foreach ($thumbs as $item) {
                $filename = $item->getAttribute('filename');

                if (strpos($filename, '_'.$size) > 1) {
                    $file = $this->newInstance();
                    $file->setKotakin($this->getKotakin());
                    $file->setFile($item);
                    break;
                }
            }
        } else {
            $mimeTypes = explode('/', $this->mimeType());

            $extension = strtolower($this->extension());
            if ( ! file_exists(public_path().'/packages/sule/kotakin/img/icons/'.$extension.'.png'))
                $extension = '_blank';
            
            $item = $this->getKotakin()->getMedia()->newInstance();
            $item->fill($this->file->getAttributes());
            $item->setAttribute('path', 'packages/sule/kotakin/img/icons');
            $item->setAttribute('filename', $extension);
            $item->setAttribute('extension', 'png');

            $file = $this->newInstance();
            $file->setKotakin($this->getKotakin());
            $file->setFile($item);

            unset($item);
            unset($mimeTypes);
        }

        unset($thumbs);

        if (isset($file))
            return $file;

        return null;
    }

    /**
     * Return file thumbnails.
     *
     * @return array
     */
    public function thumbs()
    {
        if (!is_null($this->file) and is_null($this->_thumbs)) {
            if ($this->file->childs) {
                $this->_thumbs = $this->file->childs;
            } else {
                $this->_thumbs = array();
            }
        }

        return $this->_thumbs;
    }

    /**
     * Return file created time.
     *
     * @return int
     */
    public function createdAt()
    {
        if (!is_null($this->file))
            return strtotime($this->file->getAttribute('created_at'));

        return 0;
    }

    /**
     * Return file folder.
     *
     * @return Sule\Kotakin\Templates\FolderInterface
     */
    public function folder()
    {
        if (!is_null($this->file) and is_null($this->_folder)) {
            if ($this->file->document) {
                $term = $this->getKotakin()->getTerm()->newQuery()
                                ->where('object_id', '=', $this->file->document->getAttribute('id'))
                                ->first();

                if ($term) {
                    if ($term->parent) {
                        $folder = $this->getKotakin()->getFolder()->newQuery()
                                        ->find($term->parent->getAttribute('object_id'));

                        if ($folder) {
                            $this->_folder = $this->getKotakin()->getTemplate()->getFolder();
                            $this->_folder->setKotakin($this->getKotakin());
                            $this->_folder->setFolder($folder);
                        }

                        unset($folder);
                    }
                }

                unset($term);
            }
        }

        return $this->_folder;
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
