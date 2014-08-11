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

use Sule\Kotakin\Models\MediaInterface;

class Media extends Model implements MediaInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'medias';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the media's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the media's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the media.
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
     * Delete the media.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Returns the relationship between media and user.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('Sule\Kotakin\Models\User', 'author_id');
    }

    /**
     * Returns the relationship between media and media.
     *
     * @return Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function childs()
    {
        return $this->hasMany('Sule\Kotakin\Models\Media', 'parent_id');
    }

    /**
     * Returns the relationship between media and user.
     *
     * @return Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function document()
    {
        return $this->hasOne('Sule\Kotakin\Models\Document');
    }

    /**
     * Validates the media and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\MediaExistsException
     */
    public function validate()
    {
        // Check if object_type field was passed
        if ( ! $this->object_type)
            throw new InvalidDataException("A object_type is required for a media, none given.");

        // Check if object_id field was passed
        if ( ! $this->object_id)
            throw new InvalidDataException("A object_id is required for a media, none given.");

        // Check if author_id field was passed
        if ( ! $this->author_id)
            throw new InvalidDataException("A author_id is required for a media, none given.");

        // Check if path field was passed
        if ( ! $this->path)
            throw new InvalidDataException("A path is required for a media, none given.");

        // Check if filename field was passed
        if ( ! $filename = $this->filename)
            throw new InvalidDataException("A filename is required for a media, none given.");

        // Check if extension field was passed
        if ( ! $this->extension)
            throw new InvalidDataException("A extension is required for a media, none given.");

        // Check if mime_type field was passed
        if ( ! $this->mime_type)
            throw new InvalidDataException("A mime_type is required for a media, none given.");

        // Check if file already exists
        $query = $this->newQuery();
        $persistedItem = $query->where('filename', '=', $filename)->first();

        if ($persistedItem and $persistedItem->getId() != $this->getId())
            throw new MediaExistsException("A media already exists with filename [$filename], filename must be unique for media.");

        return true;
    }

}