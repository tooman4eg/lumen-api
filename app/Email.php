<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email
 * @package App
 */
class Email extends Model
{
    const NOT_COFIRMED_CODE = 0;// не  подтвержденный  и не использованный код.
    const VALID_CODE = 1;//  код с которым сейчас работаем
    const EXPIRED_CODE = 2;// код который больше не участвует в процессе  проверок

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

    /**
     * Делаем все записи  по емейлу не валидными и не участвующими  поиске
     *
     * @param $email
     * @return mixed
     *
     */
    public static function invalidateEmail($email)
    {
        Email::where('email', stripslashes($email))->where('is_valid', '<>', Email::EXPIRED_CODE)->update(['is_valid' => self::NOT_COFIRMED_CODE]);

    }


}
