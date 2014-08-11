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

use Sule\Kotakin\Models\DocumentInterface;

class Document extends Model implements DocumentInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'documents';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the document's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the document's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the document.
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
     * Delete the document.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Returns the relationship between document and file.
     *
     * @return Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function media()
    {
        return $this->belongsTo('Sule\Kotakin\Models\Media');
    }

    /**
     * Validates the document and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\DocumentExistsException
     */
    public function validate()
    {
        // Check if media_id field was passed
        if ( ! $this->media_id)
            throw new InvalidDataException("A media_id is required for a document, none given.");

        // Check if slug field was passed
        if ( ! $slug = $this->slug)
            throw new InvalidDataException("A slug is required for a document, none given.");

        // Check if file already exists
        $query = $this->newQuery();
        $persistedItem = $query->where('slug', '=', $slug)->first();

        if ($persistedItem and $persistedItem->getId() != $this->getId())
            throw new DocumentExistsException("A document already exists with slug [$slug], slug must be unique for document.");

        return true;
    }

}