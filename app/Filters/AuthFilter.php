<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Blocks every management page unless a staff member is logged in.
 * It re-checks the user row, so deleting an account also ends that person's session.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $user    = $session->get('isLoggedIn') ? model(UserModel::class)->find($session->get('user_id')) : null;

        if ($user === null) {
            $session->remove(['isLoggedIn', 'user_id', 'username', 'full_name', 'avatar']);

            return redirect()->to('/login')->with('error', 'Please sign in to continue.');
        }

        // Keep the header (name + avatar) in sync with the database.
        $session->set(['full_name' => $user['full_name'], 'avatar' => $user['avatar']]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
