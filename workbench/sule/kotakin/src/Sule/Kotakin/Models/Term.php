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

use Sule\Kotakin\Models\TermInterface;

class Term extends Model implements TermInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'terms';

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
     * Returns the relationship between term and term.
     *
     * @return Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function parent()
    {
        return $this->belongsTo('Sule\Kotakin\Models\Term', 'parent_id');
    }

    /**
     * Returns the relationship between term and term.
     *
     * @return Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function childs()
    {
        return $this->hasMany('Sule\Kotakin\Models\Term', 'parent_id');
    }

    /**
     * Returns the relationship between term and sharing.
     *
     * @return Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function shares()
    {
        return $this->hasMany('Sule\Kotakin\Models\TermSharing');
    }

    /**
     * Validates the term and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     */
    public function validate()
    {
        // Check if object_id field was passed
        if ( ! $this->object_id)
            throw new InvalidDataException("A object_id is required for a term, none given.");

        // Check if author_id field was passed
        if ( ! $this->author_id)
            throw new InvalidDataException("A author_id is required for a term, none given.");

        // Check if name field was passed
        if ( ! $this->name)
            throw new InvalidDataException("A name is required for a term, none given.");

        return true;
    }

}