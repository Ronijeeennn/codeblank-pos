<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class Customers extends BaseController
{
    private CustomerModel $customers;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->customers = model(CustomerModel::class);
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $model  = $search !== ''
            ? $this->customers->groupStart()->like('full_name', $search)->orLike('email', $search)->groupEnd()
            : $this->customers;

        return view('customers/index', [
            'customers' => $model->orderBy('full_name')->paginate(10),
            'pager'     => $this->customers->pager,
            'search'    => $search,
        ]);
    }

    public function new()
    {
        return view('customers/form', ['customer' => null]);
    }

    public function create()
    {
        return $this->save();
    }

    public function edit(int $id)
    {
        return view('customers/form', ['customer' => $this->findOrFail($this->customers, $id)]);
    }

    public function update(int $id)
    {
        return $this->save($id);
    }

    public function delete(int $id)
    {
        $customer = $this->findOrFail($this->customers, $id);
        $this->customers->deleteKeepingSales($id);

        return redirect()->to('/customers')->with('success', "{$customer['full_name']} was deleted. Their past purchases stay in the sales history.");
    }

    private function save(?int $id = null)
    {
        if ($id) {
            $this->findOrFail($this->customers, $id);
        }

        $rules = [
            'full_name' => ['rules' => 'required|min_length[2]|max_length[100]', 'errors' => ['required' => "Enter the customer's full name."]],
            'email'     => ['rules' => 'required|valid_email|max_length[100]', 'errors' => ['required' => 'Enter an email address.']],
            'phone'     => ['rules' => ['permit_empty', 'regex_match[/^[0-9+\-\s()]{7,20}$/]'], 'errors' => ['regex_match' => 'Enter a phone number with 7 to 20 digits (spaces, +, -, ( ) allowed).']],
        ];

        if (! $this->validate($rules)) {
            return $this->backWithErrors($id ? "/customers/$id/edit" : '/customers/new');
        }

        $phone = trim((string) $this->request->getPost('phone'));
        $data  = [
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'email'     => trim((string) $this->request->getPost('email')),
            'phone'     => $phone === '' ? null : $phone,
        ];

        $id ? $this->customers->update($id, $data) : $this->customers->insert($data);

        return redirect()->to('/customers')->with('success', $id ? 'Customer updated.' : 'Customer added.');
    }
}
