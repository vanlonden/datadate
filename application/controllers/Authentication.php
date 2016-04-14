<?php

use DataDate\Http\Controller;
use DataDate\Http\Request;
use DataDate\Services\Validation\ValidationException;

class Authentication extends Controller
{
    public function signInForm()
    {
        return $this->viewBuilder->build('auth.signin');
    }

    public function signUpForm()
    {
        return $this->viewBuilder->build('auth.signup');
    }

    public function signOut()
    {
        $this->session->unsetUser();

        return $this->redirector->to('/');
    }

    public function signIn(Request $request)
    {
        $this->validator->validate($request->post(), [
            'password' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->post('email'))->first();

        if ( ! password_verify($request->post('password'), $user->password)) {
            throw new ValidationException(['credentials' => ['The email and/or password you supplied are incorrect.']]);
        }

        $this->session->store('userId', $user->id);

        return $this->redirector->to('/');
    }

    public function signUp(Request $request)
    {
        $this->validator->validate($request->post(), [
            'password' => 'required|min:6|confirmed',
            'email' => 'required|email|unique:user',
            'nickname' => 'required|between:6,20|unique:user',
            'first_name' => 'required|between:1,50',
            'last_name' => 'required|between:1,50',
            'gender' => 'required',
            'birthday' => 'required'
        ]);

        $attributes = array_except($request->post(), 'password_confirmation');
        $attributes['password'] = password_hash($request->post('password'), PASSWORD_BCRYPT);

        $this->session->setUser(User::create($attributes));

        return $this->redirector->to('/');
    }
}