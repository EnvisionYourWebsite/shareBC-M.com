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

use Sule\Kotakin\Models\TermSharingInterface;

class TermSharing extends Model implements TermSharingInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'terms_sharings';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the term's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the term's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the term.
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
     * Delete the term.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Validates the term and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\TermSharingExistsException
     */
    public function validate()
    {
        // Check if term_id field was passed
        if ( ! $termId = $this->term_id)
            throw new InvalidDataException("A term_id is required for a term, none given.");

        // Check if user_id field was passed
        if ( ! $userId = $this->user_id)
            throw new InvalidDataException("A user_id is required for a term, none given.");

        // Check if file already exists
        $query = $this->newQuery();
        $persistedItem = $query->where('term_id', '=', $termId)
                                ->where('user_id', '=', $userId)->first();

        if ($persistedItem and $persistedItem->getId() != $this->getId())
            throw new TermSharingExistsException("A term sharing already exists, must be unique for term.");

        return true;
    }

}