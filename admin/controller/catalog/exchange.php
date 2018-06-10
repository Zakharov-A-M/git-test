<?php
class ControllerCatalogExchange extends Controller {
	private $error = array();
    const WITH_ERROR = true;
    const NO_ERROR = false;

    public function index()
    {
        $this->load->model('catalog/exchange');
        $this->load->language('catalog/exchange');
        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_catalog_exchange->moduleSettings($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token']));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/exchange', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action'] = $this->url->link('catalog/exchange', 'user_token=' . $this->session->data['user_token']);

        $data['getproducts'] = $this->url->link('catalog/exchange/getproducts', 'user_token=' . $this->session->data['user_token']);

        $data['cancel'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        if (isset($this->request->post['module_account_status'])) {
            $data['module_account_status'] = $this->request->post['module_account_status'];
        } else {
            $data['module_account_status'] = $this->config->get('module_account_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $moduleData =  $this->model_catalog_exchange->getData();

        $data['login'] = $moduleData['login'] ?? '';
        $data['password'] = $moduleData['password'] ?? '';

        $this->response->setOutput($this->load->view('catalog/exchange', $data));
    }

    protected function validate() {

        return true;
    }

    /**
     * Get nomenclature and category from 1C
     */
    public function getProducts()
    {
        $this->load->model('catalog/exchange');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product_factor');
        $response = $this->model_catalog_exchange->getCategory();
        if ($response) {
            foreach ($response as $category) {
               $this->model_catalog_exchange->parseCategoryResponse($category);
            }
        }

        $response = $this->model_catalog_exchange->getNomenclature();
        if ($response) {
            foreach ($response as $product) {
                $this->model_catalog_exchange->parseNomenclatureResponse($product);
            }
        }
        $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true));
    }

}
