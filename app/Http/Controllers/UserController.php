<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\User;


class UserController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'first_name' => array('required','string'),
            'last_name' => array('required','string'),
            'email' => array('required','email','unique:users'),
            'password' => array('required'),
            'age' => array('string'),
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        try {
            $hasher = app()->make('hash');
            $first_name = $request->input('first_name');
            $last_name = $request->input('last_name');
            $email = $request->input('email');
            $password = $hasher->make($request->input('password'));
            $age = $request->input('age');

            $save = User::create([
                'first_name'=> $first_name,
                'last_name'=> $last_name,
                'email'=> $email,
                'password'=> $password,
                'age'=> $age,
                // 'api_token'=> Str::random(60)
            ]);
            $res['status'] = true;
            $res['message'] = 'Registration success!';
            return response($res, 200);
        } catch (\Illuminate\Database\QueryException $ex) {
            $res['status'] = false;
            $res['message'] = $ex->getMessage();
            return response($res, 500);
        }
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => array('required','email'),
            'password' => array('required')
        ];

        $customMessages = [
           'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);
         $email   = $request->input('email');
        try {
            $login = User::where('email', $email)->first();
            $token = $login->createToken('api_token')->accessToken;
            if ($login) {
                if ($login->count() > 0) {
                    if (Hash::check($request->input('password'), $login->password)) {
                        try {
                            $api_token = sha1($login->id.time());

                            //   $create_token = User::where('id', $login->id)->update(['api_token' => $api_token]);
                              $res['status'] = true;
                              $res['message'] = 'Success login';
                            //   unset($login['api_token']);
                              $res['data'] =  $login;
                              $res['api_token'] =  $token;
                              
                            //   if (Auth::attempt(['email' => $email, 'password' => $password])) {
                            //     return response($res, 200);
                            // }
                            return response($res, 200);

                              


                        } catch (\Illuminate\Database\QueryException $ex) {
                            $res['status'] = false;
                            $res['message'] = $ex->getMessage();
                            return response($res, 500);
                        }
                    } else {
                        $res['success'] = false;
                        $res['message'] = 'Wrong password!';
                        return response($res, 401);
                    }
                } else {
                    $res['success'] = false;
                    $res['message'] = 'Invalid Request';
                    return response($res, 401);
                }
            } else {
                $res['success'] = false;
                $res['message'] = 'Email address not found';
                return response($res, 401);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $res['success'] = false;
            $res['message'] = $ex->getMessage();
            return response($res, 500);
        }
    }

    public function get_users(){
        $user = User::all();
        if ($user) {
              $res['status'] = true;
              $res['message'] = $user;

              return response($res);
        }else{
          $res['status'] = false;
          $res['message'] = 'Cannot find user!';

          return response($res);
        }
    }

    public function get_user(Request $request, $user_id)
    {
        $this->middleware("auth");
        // $user_id = \Request::route('user_id');
        $user = User::where('id',$user_id)->first();
        if ($user) {
              $res['status'] = true;
              $res['message'] = $user;

              return response($res);
        }else{
          $res['status'] = false;
          $res['message'] = 'Cannot find user!';

          return response($res);
        }
    }
    
}