<?php


namespace App\Http\Controllers;

class EmailController extends Controller
{
  
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
}