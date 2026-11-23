<?php

namespace App\Controllers;

use App\Libraries\ImageUploader;
use App\Models\ProductModel;
use App\Models\SaleModel;

class Products extends BaseController
{
    private ProductModel $products;
    private ImageUploader $uploader;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->products = model(ProductModel::class);
        $this->uploader = new ImageUploader();
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $filter = in_array($this->request->getGet('stock'), ['low', 'out'], true) ? $this->request->getGet('stock') : '';
        $model  = $this->products;

        if ($search !== '') {
            $model->like('name', $search);
        }
        if ($filter === 'low') {
            $model->where('stock_quantity >', 0)->where('stock_quantity <=', 5);
        } elseif ($filter === 'out') {
            $model->where('stock_quantity', 0);
        }

        return view('products/index', [
            'products' => $model->orderBy('name')->paginate(12),
            'pager'    => $this->products->pager,
            'search'   => $search,
            'filter'   => $filter,
        ]);
    }

    public function new()
    {
        return view('products/form', ['product' => null]);
    }

    public function create()
    {
        return $this->save();
    }

    public function edit(int $id)
    {
        return view('products/form', ['product' => $this->findOrFail($this->products, $id)]);
    }

    public function update(int $id)
    {
        return $this->save($id);
    }

    public function delete(int $id)
    {
        $product = $this->findOrFail($this->products, $id);

        // Sales keep a foreign key to the product, so sold products can't be removed.
        if (model(SaleModel::class)->referencedBy('product_id', $id)) {
            return redirect()->to('/products')->with('error', "{$product['name']} has sales on record and can't be deleted. Set its stock to 0 to stop selling it.");
        }

        $this->products->delete($id);
        $this->uploader->remove('products', $product['image']);

        return redirect()->to('/products')->with('success', "{$product['name']} was deleted.");
    }

    /** Shared by create() and update(): validate, store the image, then save. */
    private function save(?int $id = null)
    {
        $existing = $id ? $this->findOrFail($this->products, $id) : null;

        $rules = [
            'name'           => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => ['required' => 'Enter the product name.'],
            ],
            'price'          => [
                'rules'  => ['required', 'regex_match[/^\d{1,8}(\.\d{1,2})?$/]'],
                'errors' => ['required' => 'Enter a price.', 'regex_match' => 'Enter a price like 49.50 (up to 2 decimals).'],
            ],
            'stock_quantity' => [
                'rules'  => 'required|is_natural|less_than[1000000]',
                'errors' => ['required' => 'Enter the stock on hand (0 or more).', 'is_natural' => 'Stock must be a whole number, 0 or more.'],
            ],
        ] + ImageUploader::rules('image');

        if (! $this->validate($rules)) {
            return $this->backWithErrors($id ? "/products/$id/edit" : '/products/new');
        }

        $data = [
            'name'           => trim((string) $this->request->getPost('name')),
            'price'          => $this->request->getPost('price'),
            'stock_quantity' => (int) $this->request->getPost('stock_quantity'),
        ];

        $file = $this->request->getFile('image');
        if ($file !== null && $file->isValid() && ! $file->hasMoved()) {
            $data['image'] = $this->uploader->store($file, 'products', 400);
            if ($existing) {
                $this->uploader->remove('products', $existing['image']);
            }
        }

        $id ? $this->products->update($id, $data) : $this->products->insert($data);

        return redirect()->to('/products')->with('success', $id ? 'Product updated.' : 'Product added.');
    }
}
