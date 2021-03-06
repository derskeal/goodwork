<?php

namespace App\Core\Http\Controllers\Auth;

use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Models\Token;
use App\Core\Models\Office;
use Illuminate\Http\Request;
use App\Core\Mail\UserRegistered;
use Illuminate\Support\Facades\Mail;
use App\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array                                      $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'active'   => 1,
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Show the application registration form.
     *
     * @param  mixed                     $token
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm($token)
    {
        if (Token::where('token', decrypt($token))->exists()) {
            return view('auth.register', compact('token'));
        }

        abort(403);
    }

    /**
     * @param Request $request
     * @param $token
     */
    public function confirmNewRegistration(Request $request, $token)
    {
        $token = Token::where('token', decrypt($token))->first();
        if ($token && ($token->email == $request->email)) {
            $this->register($request, $token);

            $user = User::whereEmail($request->email)->first();
            $role = Role::find($token->role_id);
            $user->role_id = $role->id;
            $user->save();

            $token->delete();

            return redirect('/');
        }

        abort(403);
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed                    $user
     * @return void
     */
    protected function registered(Request $request, $user)
    {
        $office = Office::where('name', 'Headquarter')->first();
        $user->offices()->attach($office->id);
        Mail::to($user->email)
            ->send(new UserRegistered($user));
    }
}
