<?php


namespace App\Http\Controllers;

use  App\Email;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;


class EmailController extends Controller
{
    const  CODE_MAX_CHEKING_ATTEMPTS = 3;
    const  CODE_MAX_GENERATION_PER_HOUR = 5;
    const  CODE_MAX_GENERATION_PER_MINUTES = 1;
    const  CODE_GENERATION_TIMEOUT = 5;
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
     * @param $request
     * @return mixed
     */
    public function sendCode(Request $request)
    {
        //validation
        $this->validate($request, [
            'email' => 'required|email'
        ]);
        $email = $request->email;
//        if (filter_var($request->email, FILTER_VALIDATE_EMAIL) === false) {
//            return response()->json('Error: wrong email', 401);
//        }
        //проверем можно ли еще генерить код
        //check max generated  code per hour
        $sentPerHour = $this->countPer($email, 60);
//        where('is_valid', '<>', Email::INVALID_CODE)
//            ->where('email', stripslashes())
//            ->where('created_at', '>', date("Y-m-d H:i:s", time() - 1 * 60 * 60))
//            ->count();

        $sentPerMinutes = $this->countPer($email, self::CODE_GENERATION_TIMEOUT);
//        $sentPerMinutes = Email::where('is_valid', '<>', Email::INVALID_CODE)
//            ->where('email', stripslashes($request->email))
//            ->where('created_at', '>', date("Y-m-d H:i:s", time() - 5 * 60))
//            ->count();

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



    public function checkCode(Request $request)
    {
        //validation
        $this->validate($request, [
            'email' => 'required|email',
            'code' => 'required',

        ]);
        // 1) Код - это строка из 4-ех символов, каждый из которых является цифрой;
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
//        dd($chekedEmail->code);
        if ($chekedEmail) {
            Email::where('id', $chekedEmail->id)->increment('attempts');
            if ($chekedEmail->code == $request->code) {
//                Email::where('email', stripslashes($this->email))->update(['is_valid' => Email::NOT_COFIRMED_CODE]);
                /* 8) После успешной проверки кода все счетчики ограничений по отправке кода для данного email обнуляются.*/
                Email::where('email', stripslashes($request->email))->update(['attempts' => 0, 'is_valid' => Email::EXPIRED_CODE]);
//                Email::where('id', $chekedEmail['id'])->update(['attempts' => 0, 'is_valid' => Email::EXPIRED_CODE]);

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
     *     //        * 1) Код - это строка из 4-ех символов, каждый из которых является цифрой;
     */
    private function makeCode()
    {
        $code = "";
        for ($i = 0; $i < 4; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    private function countPer($email, $minutes)
    {
        return Email::where('is_valid', '<>', Email::EXPIRED_CODE)
            ->where('email', stripslashes($email))
            ->where('created_at', '>', date("Y-m-d H:i:s", time() - $minutes * 60))
            ->count();
    }
}