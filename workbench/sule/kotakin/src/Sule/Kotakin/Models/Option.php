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

use Sule\Kotakin\Models\OptionInterface;

use Sule\Kotakin\Models\OptionExistsException;

class Option extends Model implements OptionInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'options';

	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Returns the option's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the option's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * Saves the option.
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
     * Delete the option.
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * Validates the option and throws a number of
     * Exceptions if validation fails.
     *
     * @return bool
     * @throws Sule\Kotakin\Models\InvalidDataException
     * @throws Sule\Kotakin\Models\OptionExistsException
     */
    public function validate()
    {
        // Check if name field was passed
        if ( ! $name = $this->name)
            throw new InvalidDataException("A name is required for a option, none given.");

        // Check if option already exists
        $query = $this->newQuery();
        $persistedOption = $query->where('name', '=', $name)->first();

        if ($persistedOption and $persistedOption->getId() != $this->getId())
            throw new OptionExistsException("A option already exists with name [$name], name must be unique for option.");

        return true;
    }

}