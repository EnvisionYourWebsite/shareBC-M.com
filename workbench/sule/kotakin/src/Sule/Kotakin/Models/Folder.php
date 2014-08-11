<?php
namespace Sule\Kotakin\Models;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Eloquent\Model;

use Sule\Kotakin\Models\FolderInterface;

class Folder extends Model implements FolderInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'folders';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the folder's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the folder's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the folder.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $this->validate();
        return parent::save();
    }

    /**
     * Delete the folder.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Validates the folder and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\FolderExistsException
     */
    public function validate()
    {
        // Check if name field was passed
        if ( ! $this->name)
            throw new InvalidDataException("A name is required for a folder, none given.");

        // Check if slug field was passed
        if ( ! $slug = $this->slug)
            throw new InvalidDataException("A slug is required for a folder, none given.");

        // Check if file already exists
        $query = $this->newQuery();
        $persistedItem = $query->where('slug', '=', $slug)->first();

        if ($persistedItem and $persistedItem->getId() != $this->getId())
            throw new FolderExistsException("A folder already exists with slug [$slug], must be unique for folder.");

        return true;
    }

}