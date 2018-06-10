<?php
class ControllerInformationArticles extends Controller
{

    /**
     * View page with all articles
     */
    public function index()
    {
        $this->load->language('catalog/articles');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/information');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/articles')
        );

        $data['informations'] = array();

        $results = $this->model_catalog_information->getInformations();

        foreach ($results as $result) {
            $data['informations'][] = array(
                'information_id'   => $result['information_id'],
                'title'            => $result['title'],
                'description'      => $result['description'],
                'meta_title'       => $result['meta_title'],
                'meta_description' => $result['meta_description'],
                'meta_keyword'     => $result['meta_keyword'],
                'view'             => $this->url->link('information/articles/view', '&article_id=' . $result['information_id'])
            );
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['catalog_menu'] = $this->load->controller('catalog/menu');

        $this->response->setOutput($this->load->view('information/articles', $data));
    }

    /**
     * View page with article to id
     */
    public function view()
    {
        $this->load->language('catalog/articles');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/information');

        if (!empty($this->request->get['article_id'])) {
            $result = $this->model_catalog_information->getInformation($this->request->get['article_id']);
            $data['information'] = array(
                'information_id'   => $result['information_id'],
                'title'            => $result['title'],
                'description'      => html_entity_decode($result['description']),
                'meta_title'       => $result['meta_title'],
                'meta_description' => $result['meta_description'],
                'meta_keyword'     => $result['meta_keyword'],
            );


            $data['breadcrumbs'] = [];
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('information/articles')
            ];

            $data['breadcrumbs'][] = [
                'text' => $result['title'],
                'href' => $this->url->link('information/articles/view', '&article_id=' . $result['information_id'])
            ];

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            $data['catalog_menu'] = $this->load->controller('catalog/menu');

            $this->response->setOutput($this->load->view('information/article_view', $data));
        }

    }

}
