<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        return view('auth/login');
    }

    public function login()
    {
        // Slow down password guessing: 5 attempts per minute per IP address.
        if (! service('throttler')->check(md5($this->request->getIPAddress()), 5, MINUTE)) {
            return redirect()->to('/login')->with('error', 'Too many sign-in attempts. Wait a minute and try again.');
        }

        $rules = ['username' => 'required|max_length[50]', 'password' => 'required|max_length[72]'];

        if (! $this->validate($rules)) {
            return $this->backWithErrors('/login');
        }

        $username = (string) $this->request->getPost('username');
        $user     = model(UserModel::class)->where('username', $username)->first();

        // One message for both cases, so the form never reveals which usernames exist.
        if ($user === null || ! password_verify((string) $this->request->getPost('password'), $user['password'])) {
            return redirect()->to('/login')->with('error', 'Wrong username or password.')->with('last_username', $username);
        }

        session()->regenerate(); // new session id after login prevents session fixation
        session()->set([
            'isLoggedIn' => true,
            'user_id'    => (int) $user['id'],
            'username'   => $user['username'],
            'full_name'  => $user['full_name'],
            'avatar'     => $user['avatar'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login?signed_out=1');
    }
}
