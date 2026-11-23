<?php

namespace App\Controllers;

use App\Libraries\ImageUploader;
use App\Models\SaleModel;
use App\Models\UserModel;

class Staff extends BaseController
{
    private UserModel $users;
    private ImageUploader $uploader;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->users    = model(UserModel::class);
        $this->uploader = new ImageUploader();
    }

    public function index()
    {
        return view('staff/index', ['staff' => $this->users->orderBy('full_name')->paginate(10), 'pager' => $this->users->pager]);
    }

    public function new()
    {
        return view('staff/form', ['member' => null]);
    }

    public function create()
    {
        return $this->save();
    }

    public function edit(int $id)
    {
        return view('staff/form', ['member' => $this->findOrFail($this->users, $id)]);
    }

    public function update(int $id)
    {
        return $this->save($id);
    }

    public function delete(int $id)
    {
        $member = $this->findOrFail($this->users, $id);

        if ($id === (int) session()->get('user_id')) {
            return redirect()->to('/staff')->with('error', "You can't delete the account you're signed in with.");
        }

        if (model(SaleModel::class)->referencedBy('sold_by', $id)) {
            return redirect()->to('/staff')->with('error', "{$member['full_name']} has recorded sales, so the account can't be deleted.");
        }

        $this->users->delete($id);
        $this->uploader->remove('avatars', $member['avatar']);

        return redirect()->to('/staff')->with('success', "{$member['full_name']} was deleted.");
    }

    private function save(?int $id = null)
    {
        $existing = $id ? $this->findOrFail($this->users, $id) : null;

        // On create the password is required; on edit it's optional (blank = keep the current one).
        $passwordRules = $id ? 'permit_empty|min_length[8]|max_length[72]' : 'required|min_length[8]|max_length[72]';

        $rules = [
            'username'         => [
                'rules'  => 'required|alpha_dash|min_length[3]|max_length[50]|is_unique[users.username,id,' . (int) $id . ']',
                'errors' => ['is_unique' => 'That username is taken.', 'alpha_dash' => 'Use letters, numbers, dashes and underscores only.'],
            ],
            'full_name'        => 'required|min_length[2]|max_length[100]',
            'password'         => ['rules' => $passwordRules, 'errors' => ['min_length' => 'Use at least 8 characters.']],
            'password_confirm' => ['rules' => 'matches[password]', 'errors' => ['matches' => "The passwords don't match."]],
        ] + ImageUploader::rules('avatar');

        if (! $this->validate($rules)) {
            return $this->backWithErrors($id ? "/staff/$id/edit" : '/staff/new');
        }

        $data = [
            'username'  => trim((string) $this->request->getPost('username')),
            'full_name' => trim((string) $this->request->getPost('full_name')),
        ];

        if ((string) $this->request->getPost('password') !== '') {
            $data['password'] = $this->request->getPost('password'); // hashed by UserModel
        }

        $file = $this->request->getFile('avatar');
        if ($file !== null && $file->isValid() && ! $file->hasMoved()) {
            $data['avatar'] = $this->uploader->store($file, 'avatars', 200);
            if ($existing) {
                $this->uploader->remove('avatars', $existing['avatar']);
            }
        }

        $id ? $this->users->update($id, $data) : $this->users->insert($data);

        return redirect()->to('/staff')->with('success', $id ? 'Staff account updated.' : 'Staff account created.');
    }
}
