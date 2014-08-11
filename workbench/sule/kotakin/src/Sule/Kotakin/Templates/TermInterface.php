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

interface TermInterface
{
    
    /**
     * Return term id.
     *
     * @return int
     */
    public function id();

    /**
     * Return term author.
     *
     * @return Sule\Kotakin\Templates\UserInterface
     */
    public function author();

    /**
     * Return term object.
     *
     * @return Sule\Kotakin\Templates\FolderInterface | Sule\Kotakin\Templates\DocumentInterface
     */
    public function object();

    /**
     * Check is a folder.
     *
     * @return bool
     */
    public function isFolder();

    /**
     * Check is a file.
     *
     * @return bool
     */
    public function isFile();

    /**
     * Check if folder authored by given user.
     *
     * @param Sule\Kotakin\Templates\UserInterface $user
     * @return bool
     */
    public function isOwnedBy(UserInterface $user);

    /**
     * Check is folder shared with the given user id.
     *
     * @return bool
     */
    public function sharedWith($userId);

    /**
     * Return term created time.
     *
     * @return int
     */
    public function createdAt();

    /**
     * Return term updated time.
     *
     * @return int
     */
    public function updatedAt();

    /**
     * Create a new instance of the given template.
     *
     * @return Sule\Kotakin\Templates\FileInterface|static
     */
    public function newInstance();

}
