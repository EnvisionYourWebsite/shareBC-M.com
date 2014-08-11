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

use Sule\Kotakin\Models\DocumentLinkInterface;

class DocumentLink extends Model implements DocumentLinkInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'documents_links';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the document link's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the document link's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the document link.
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
     * Delete the document link.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Returns the relationship between document link and document.
     *
     * @return Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function document()
    {
        return $this->belongsTo('Sule\Kotakin\Models\Document');
    }

    /**
     * Returns the relationship between document link and user.
     *
     * @return Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo('Sule\Kotakin\Models\User');
    }

    /**
     * Validates the document link and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\DocumentLinkExistsException
     */
    public function validate()
    {
        // Check if media_id field was passed
        if ( ! $this->document_id)
            throw new InvalidDataException("A document_id is required for a document link, none given.");

        // Check if slug field was passed
        if ( ! $slug = $this->slug)
            throw new InvalidDataException("A slug is required for a document link, none given.");

        // Check if file already exists
        $query = $this->newQuery();
        $persistedItem = $query->where('slug', '=', $slug)->first();

        if ($persistedItem and $persistedItem->getId() != $this->getId())
            throw new DocumentLinkExistsException("A document link already exists with slug [$slug], slug must be unique for document link.");

        return true;
    }

}