<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email
 * @package App
 */
class Email extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'email', 'is_valid', 'created_at', 'attempts'
    ];

    /**
     * @var string
     */
    protected $table = 'Email';

   



}
