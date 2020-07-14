<?php


namespace App\Http\Controllers;

use  App\Email;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;


/**
 * Class EmailController
 * @package App\Http\Controllers
 */
class EmailController extends Controller
{
    /**
     * Количетсво попыток до сброса кода
     */
    const  CODE_MAX_CHEKING_ATTEMPTS = 3;
    /**
     *Максимальное количество генераций кода в течение часа
     */
    const  CODE_MAX_GENERATION_PER_HOUR = 5;
    /**
     *Максимальное количество генераций кода в течение минуты
     */
    const  CODE_MAX_GENERATION_PER_MINUTES = 1;
    /**
     *Задержка в минутах для генерации  следующего кода
     */
    const  CODE_GENERATION_TIMEOUT = 5;
    /**
     *Время жизни кода
     */
    const  CODE_EXPIRED_TIMEOUT = 5;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @return mixed
     */
    public function allCodes()
    {
        return response()->json(Email::all());

    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function sendCode(Request $request)
    {
        //validation
        $this->validate($request, [
            'email' => 'required|email'
        ]);
        $email = $request->email;
        $sentPerHour = $this->countPer($email, 60);
        $sentPerMinutes = $this->countPer($email, self::CODE_GENERATION_TIMEOUT);

        if ($sentPerHour >= self::CODE_MAX_GENERATION_PER_HOUR || $sentPerMinutes >= self::CODE_MAX_GENERATION_PER_MINUTES) {
            return response()->json('Error: Too many attempts. You have to waite.', 401);
        } else {


            $code = $this->makeCode();

            if (mail($email, "Please confirm y our  email", 'Your code :' . $code)) {

                Email::invalidateEmail($email);
                //add  new
                Email::insert([
                    'email' => $email,
                    'code' => $code,
                    'is_valid' => Email::VALID_CODE,
                    'created_at' => date("Y-m-d H:i:s", time()),
                    'updated_at' => date("Y-m-d H:i:s", time()),
                    'attempts' => 0,
                ]);
                return response()->json('Code sent on your email', 200);
            }
        }

        return response()->json('Can not send the code. Please try again later', 401);

    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function checkCode(Request $request)
    {
        //validation
        $this->validate($request, [
            'email' => 'required|email',
            'code' => 'required',

        ]);

        if (!preg_match('@^\d{4}@', $request->code)) {
            return response()->json('Wrong code. Please check it.', 401);
        }


        /**
         * Общие требования к секретному коду:
         * 6) После трех неуспешных попыток проверить код (вызов checkCode) код инвалидируется;
         * 7) После успешной проверки кода он инвалидируется;
         */

        $chekedEmail = Email::where('email', $request->email)
            ->where('is_valid', Email::VALID_CODE)
            ->where('created_at', '>', date("Y-m-d H:i:s", time() - self::CODE_EXPIRED_TIMEOUT * 60))
            ->first();

        if ($chekedEmail) {
            Email::where('id', $chekedEmail->id)->increment('attempts');
            if ($chekedEmail->code == $request->code) {
                Email::where('email', stripslashes($request->email))->update(['attempts' => 0, 'is_valid' => Email::EXPIRED_CODE]);
                return response()->json('Your email successfully confirmed', 200);
            } else {
                if ($chekedEmail->attempts >= self::CODE_MAX_CHEKING_ATTEMPTS) {
                    Email::where('id', $chekedEmail->id)
                        ->update(['is_valid' => Email::NOT_COFIRMED_CODE]);
                }
                return response()->json('Your code is wrong', 201);
            }
        } else {
            return response()->json('Error: wrong email', 401);
        }


    }


    /**
     * генерим код
     * Код - это строка из 4-ех символов, каждый из которых является цифрой;
     * @return string
     */
    private function makeCode()
    {
        $code = "";
        for ($i = 0; $i < 4; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    /**
     * @param $email
     * @param $minutes
     * @return mixed
     */
    private function countPer($email, $minutes)
    {
        return Email::where('is_valid', '<>', Email::EXPIRED_CODE)
            ->where('email', stripslashes($email))
            ->where('created_at', '>', date("Y-m-d H:i:s", time() - $minutes * 60))
            ->count();
    }
}