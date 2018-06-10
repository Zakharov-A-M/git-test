<?php

class ControllerStockroomStockroom extends Controller
{
    private $error = array();

    /**
     * View all stockroom
     */
    public function index()
    {
        $this->load->language('stockroom/stockroom');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/stockroom');

        $this->getList();
    }

    /**
     * View list all stockroom
     */
    protected function getList()
    {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = '';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . $url)
        );

        $data['add'] = $this->url->link('stockroom/stockroom/add', 'user_token=' . $this->session->data['user_token'] . $url);
        $data['delete'] = $this->url->link('stockroom/stockroom/delete', 'user_token=' . $this->session->data['user_token'] . $url);

        $data['stockrooms'] = array();

        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $stockroom_total = $this->model_stockroom_stockroom->getTotalStockrooms();

        $results = $this->model_stockroom_stockroom->getStockrooms($filter_data);

        foreach ($results as $result) {
            $data['stockrooms'][] = array(
                'stockroom_id' => $result['stockroom_id'],
                'name'         => $result['name'],
                'GUID'         => $result['GUID'],
                'address'      => $result['address'],
                'country_name' => $result['country_name'],
                'edit'         => $this->url->link('stockroom/stockroom/edit', 'user_token=' . $this->session->data['user_token'] . '&stockroom_id=' . $result['stockroom_id'] . $url),
                'delete'       => $this->url->link('stockroom/stockroom/delete', 'user_token=' . $this->session->data['user_token'] . '&stockroom_id=' . $result['stockroom_id'] . $url),
                'nomenclature' => $this->url->link('stockroom/nomenclature', 'user_token=' . $this->session->data['user_token'] . '&stockroom_id=' . $result['stockroom_id'] . $url)
            );
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_sort_name'] = $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
        $data['sort_sort_country'] = $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . '&sort=country_name' . $url);
        $data['sort_sort_address'] = $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . '&sort=address' . $url);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $stockroom_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($stockroom_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($stockroom_total - $this->config->get('config_limit_admin'))) ? $stockroom_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $stockroom_total, ceil($stockroom_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('stockroom/stockroom_list', $data));
    }

    /**
     * Delete this stockroom
     */
    public function delete()
    {
        $this->load->language('stockroom/stockroom');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/stockroom');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $stockroom_id) {
                $this->model_stockroom_stockroom->deleteStockroom($stockroom_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . $url));
        }
        $this->getList();
    }

    /**
     * Validate delete process
     *
     * @return bool
     */
    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'stockroom/stockroom')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    /**
     * Added stockroom
     */
    public function add()
    {
        $this->load->language('stockroom/stockroom');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/stockroom');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_stockroom_stockroom->addStockroom($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . $url));
        }

        $this->getForm();
    }

    /**
     * Get form for stockroom
     */
    protected function getForm()
    {
        $this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
        $this->document->addStyle('view/javascript/codemirror/theme/monokai.css');
        $this->document->addStyle('view/javascript/summernote/summernote.css');

        $this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
        $this->document->addScript('view/javascript/codemirror/lib/xml.js');
        $this->document->addScript('view/javascript/codemirror/lib/formatting.js');
        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
        $this->document->addScript('view/javascript/summernote/opencart.js');

        $data['text_form'] = !isset($this->request->get['stockroom_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        if (isset($this->error['address'])) {
            $data['error_address'] = $this->error['address'];
        } else {
            $data['error_address'] = array();
        }

        if (isset($this->error['country'])) {
            $data['error_country'] = $this->error['country'];
        } else {
            $data['error_country'] = '';
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . $url)
        );

        if (!isset($this->request->get['stockroom_id'])) {
            $data['action'] = $this->url->link('stockroom/stockroom/add', 'user_token=' . $this->session->data['user_token'] . $url);
        } else {
            $data['action'] = $this->url->link('stockroom/stockroom/edit', 'user_token=' . $this->session->data['user_token'] . '&stockroom_id=' . $this->request->get['stockroom_id'] . $url);
        }

        $data['cancel'] = $this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . $url);

        if (isset($this->request->get['stockroom_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $stockroom_info = $this->model_stockroom_stockroom->getStockroom($this->request->get['stockroom_id']);
        }

        $data['user_token'] = $this->session->data['user_token'];


        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($stockroom_info)) {
            $data['name'] = $stockroom_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['address'])) {
            $data['address'] = $this->request->post['address'];
        } elseif (!empty($stockroom_info)) {
            $data['address'] = $stockroom_info['address'];
        } else {
            $data['address'] = '';
        }

        if (isset($this->request->post['country_id'])) {
            $data['country_id'] = $this->request->post['country_id'];
        } elseif (!empty($stockroom_info)) {
            $data['country_id'] = $stockroom_info['country_id'];
        } else {
            $data['country_id'] = '';
        }

        if (isset($this->request->post['country_name'])) {
            $data['country_name'] = $this->request->post['country_name'];
        } elseif (!empty($stockroom_info)) {
            $data['country_name'] = $stockroom_info['country_name'];
        } else {
            $data['country_name'] = '';
        }

        $this->load->model('design/layout');

        $data['layouts'] = $this->model_design_layout->getLayouts();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('stockroom/stockroom_form', $data));
    }

    /**
     * Edit this stockroom
     */
    public function edit()
    {
        $this->load->language('stockroom/stockroom');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('stockroom/stockroom');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_stockroom_stockroom->editStockroom($this->request->get['stockroom_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('stockroom/stockroom', 'user_token=' . $this->session->data['user_token'] . $url));
        }

        $this->getForm();
    }

    /**
     * Autocomplete for country
     */
    public function autocomplete()
    {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('stockroom/country');

            $filter_data = array(
                'filter_name' => $this->request->get['filter_name'],
                'sort'        => 'name',
                'order'       => 'ASC',
                'start'       => 0,
                'limit'       => 5
            );

            $results = $this->model_stockroom_country->getCountriesForAutocomplete($filter_data);

            foreach ($results as $result) {
                $json[] = [
                    'country_id' => $result['country_id'],
                    'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
                ];
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Validate form for stockroom
     *
     * @return bool
     */
    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'stockroom/stockroom')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['name']) && isset($this->request->post['name'])) {
            $this->error['name'] = $this->language->get('error_name');
        } else {
            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 255)) {
                $this->error['name'] = $this->language->get('error_name');
            }
        }

        if (empty($this->request->post['address']) && isset($this->request->post['address'])) {
            $this->error['address'] = $this->language->get('error_address');
        } else {
            if ((utf8_strlen($this->request->post['address']) < 3) || (utf8_strlen($this->request->post['address']) > 255)) {
                $this->error['address'] = $this->language->get('error_address');
            }
        }

        if (empty($this->request->post['country_id'] && isset($this->request->post['country_id']))) {
            $this->error['country'] = $this->language->get('error_country');
        }

        if (empty($this->request->post['country_name'] && isset($this->request->post['country_name']))) {
            $this->error['country'] = $this->language->get('error_country');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}
