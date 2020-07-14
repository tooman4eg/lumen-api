<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email
 * @package App
 */
class Email extends Model
{
    const NOT_COFIRMED_CODE = 0;
    const VALID_CODE = 1;
    const EXPIRED_CODE = 2;

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
