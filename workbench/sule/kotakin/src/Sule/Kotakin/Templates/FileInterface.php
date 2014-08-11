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

interface FileInterface
{
    
    /**
     * Return file id.
     *
     * @return int
     */
    public function id();

    /**
     * Return file parent id.
     *
     * @return int
     */
    public function parentId();

    /**
     * Return file author.
     *
     * @return Sule\Kotakin\Templates\UserInterface
     */
    public function author();

    /**
     * Return file type.
     *
     * @return string
     */
    public function type();

    /**
     * Return file title.
     *
     * @return string
     */
    public function title();

    /**
     * Return file alt text.
     *
     * @return string
     */
    public function altText();

    /**
     * Return file path.
     *
     * @return string
     */
    public function path();

    /**
     * Return file name.
     *
     * @return string
     */
    public function filename();

    /**
     * Return file extension.
     *
     * @return string
     */
    public function extension();

    /**
     * Return file permalink.
     *
     * @return string
     */
    public function permalink();

    /**
     * Return file mime type.
     *
     * @return string
     */
    public function mimeType();

    /**
     * Return file size.
     *
     * @return string
     */
    public function size();

    /**
     * Return file width if image.
     *
     * @return int
     */
    public function width();

    /**
     * Return file height if image.
     *
     * @return int
     */
    public function height();

    /**
     * Return file metadata.
     *
     * @return array
     */
    public function metadata();

    /**
     * Return file thumbnail.
     *
     * @param string $size
     * @param bool $icon
     * @return Sule\Kotakin\Templates\FileInterface
     */
    public function thumb($size = '100x100', $icon = false);

    /**
     * Return file thumbnails.
     *
     * @return array
     */
    public function thumbs();

    /**
     * Return file created time.
     *
     * @return int
     */
    public function createdAt();

    /**
     * Return file folder.
     *
     * @return Sule\Kotakin\Templates\FolderInterface
     */
    public function folder();

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\FileInterface|static
     */
    public function newInstance();

}
