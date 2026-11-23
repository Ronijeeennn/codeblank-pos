<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['form', 'url', 'pos'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    /**
     * Fetch a row or stop with a 404. Keeps the "record doesn't exist" check in one place.
     */
    protected function findOrFail(\CodeIgniter\Model $model, $id): array
    {
        $row = $model->find($id);

        if ($row === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $row;
    }

    /**
     * Redirect to a form with validation errors and the old input (Post/Redirect/Get).
     * Unlike redirect()->withInput(), password fields are never flashed into the session.
     */
    protected function backWithErrors(string $to)
    {
        $post = array_diff_key($this->request->getPost() ?? [], array_flip(['password', 'password_confirm']));

        session()->setFlashdata('_ci_old_input', ['get' => [], 'post' => $post]);
        session()->setFlashdata('_ci_validation_errors', service('validation')->getErrors());

        return redirect()->to($to);
    }
}
