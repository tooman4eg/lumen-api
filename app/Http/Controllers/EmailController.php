<?php


namespace App\Http\Controllers;

use  App\Email;
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
     * @param $email
     * @return mixed
     */
    public function sendCode()
    {
      dd('test');
    }



    public function checkCode(Request $request)
    {
       dd('test');

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


}