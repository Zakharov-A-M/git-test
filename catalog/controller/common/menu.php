<?php
class ControllerCommonMenu extends Controller
{
    const MENU = [
        'about',
        'howorder',
        'howpay',
        'delivery',
        'articles',
        'news',
        'contacts',
        'discount'
    ];

    /**
     * View menu in header
     *
     * @return string
     */
    public function index()
    {
        $this->load->language('common/menu');

        // Menu
        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $data['categories'] = array();

        $data['categories'][] = array(
            'text'     => $this->language->get('category'),
            'href'     => $this->url->link('product/'. 'category')
        );

        foreach (self::MENU as $menu) {
            $data['categories'][] = array(
                'text'     => $this->language->get($menu),
                'href'     => $this->url->link('information/'. $menu)
            );
        }

        return $this->load->view('common/menu', $data);
    }
}
