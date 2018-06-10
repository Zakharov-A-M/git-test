<?php

class ModelCatalogExchange extends Model
{
    const NOMENCLATURE = "http://86.57.128.226:8085/KA_TEST/hs/DataExchangeSite/643/testSite/nomenclature";
    const CATEGORY = "http://86.57.128.226:8085/KA_TEST/hs/DataExchangeSite/643/testSite/category";

    public function isConfigured()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);

        return ($result->num_rows > 0) ? true : false;
    }

    public function generateToken()
    {
        $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $max=32;
        $size=StrLen($chars)-1;
        $password=null;
        while($max--) {
            $password .= $chars[rand(0,$size)];
        }
        return $password;
    }

    public function saveToken()
    {
        $token = $this->generateToken();
        $query = "UPDATE " . DB_PREFIX . "exchange SET token='" . $token . "'";
        $this->db->query($query);
    }

    public function saveCredentials($login, $password)
    {
        $query = "UPDATE " . DB_PREFIX . "exchange SET login='" . $login . "', password='" . $password . "'";
        $this->db->query($query);
    }

    public function getData()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);

        return $result->row;
    }


    public function configureModule($login, $password)
    {
        $token = $this->generateToken();
        $this->db->query("INSERT INTO `" . DB_PREFIX . "exchange`  (token,login,password,route,error) values ('" . $token ."','" . $login ."','" . $password ."','" . self::MODULE_ROUTE ."',0)");
        return $this->getData();

    }

    public function moduleSettings($post)
    {
        $login = $post['ex_login'];
        $password = $post['ex_password'];
        if ($this->isConfigured()) {
            $this->saveCredentials($login, $password);
        } else {
            $this->configureModule($login, $password);
        }
    }

    public function getStatistic()
    {
        $sql = "SELECT error, updated_at from " . DB_PREFIX . "exchange";
        $result = $this->db->query($sql);

        return $result->row;
    }

    /**
     * Send request to 1C
     *
     * @param string $exchangeUrl
     * @return mixed
     * @throws Exception
     */
    public function execRequest(string $exchangeUrl)
    {
        $query = "SELECT * FROM " . DB_PREFIX . "exchange";
        $result = $this->db->query($query);
        $login = $result->row['login'];
        $password = $result->row['password'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $exchangeUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERPWD, "$login:$password");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception('Error http request');
        }

        curl_close($curl);
        $responseInfo = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception('Invalid json format');
        }

        return $responseInfo;
    }

    /**
     * Get nomenclature
     *
     * @return mixed
     * @throws Exception
     */
    public function getNomenclature()
    {
        $result = $this->execRequest(self::NOMENCLATURE);
        return $result['data'];
    }

    /**
     * Get category
     *
     * @return mixed
     * @throws Exception
     */
    public function getCategory()
    {
        $result = $this->execRequest(self::CATEGORY);
        return $result['data'];
    }

    /**
     * Parse nomenclature from 1C
     *
     * @param array $responseItem
     * @return array
     */
    public function parseNomenclatureResponse(array $responseItem)
    {
        $data = [];
        $data['GUID'] = $responseItem['GUID_Nomenclature'];
        $data['GUID_Characteristic'] = $responseItem['GUID_Characteristic'];

        $data['product_description'] = [
            1 => [
                'name' => $responseItem['nomenclatureName'],
                'description' => $responseItem['description'],
                'meta_title' => $responseItem['nomenclatureName'],
                'meta_description' => $responseItem['nameFull'],
                'meta_keyword' => $responseItem['nameS'],
                'tag' => ''
            ],
            2 => [
                'name' => $responseItem['nomenclatureName'],
                'description' => $responseItem['description'],
                'meta_title' => $responseItem['nomenclatureName'],
                'meta_description' => $responseItem['nameFull'],
                'meta_keyword' => $responseItem['nameS'],
                'tag' => ''
            ]
        ];

        switch ((int)$responseItem['currency']) {
            case 643:
                $data['currency'] = 'RUB';
                break;
            case 840:
                $data['currency'] = 'USD';
                break;
            case 933:
                $data['currency'] = 'BYN';
                break;
            case 978:
                $data['currency'] = 'EUR';
                break;
            case 978:
                $data['currency'] = 'UAH';
                break;

        }

        $data['price'] = str_replace(',', '.', $responseItem['price']);

        $this->load->model('catalog/manufacturer');

        if ($this->model_catalog_manufacturer->getTotalManufacturerByGUID($responseItem['manufacturerGUID']) > 0) {
            $data['manufacturer_id'] = $this->model_catalog_manufacturer->getManufacturerByGUID($responseItem['manufacturerGUID']);
        } else {
            $data['manufacturer_id'] =  $this->model_catalog_manufacturer->addManufacturer([
                'name' => $responseItem['manufacturer'],
                'sort_order' => 0,
                'manufacturer' => $responseItem['Marke'],
                'GUID' => $responseItem['manufacturerGUID']
            ]);
        }

        if (!empty($responseItem['vendorCode'])) {
            $data['vendorCode'] = $responseItem['vendorCode'];
        }

        $this->load->model('catalog/category');

        if ($this->model_catalog_category->getTotalCategoryByGUID($responseItem['categoryGUID']) > 0) {
            $result = $this->model_catalog_category->getCategoryByGUID($responseItem['categoryGUID']);
            $data['product_category'] = [
                $result
            ];
        } else {
            $data['product_category'] = [
               60
            ];
        }

        $data['pr'] = [
          'pr1' => $responseItem['pr1'],
          'pr2' => $responseItem['pr2'],
          'pr3' => $responseItem['pr3'],
          'pr4' => $responseItem['pr4'],
          'pr5' => $responseItem['pr5'],
          'pr6' => $responseItem['pr6'],
          'pr7' => $responseItem['pr7'],
          'pr8' => $responseItem['pr8'],
          'pr9' => $responseItem['pr9']
        ];

        $data['image'] = $responseItem['basicimage'];

        if (!empty($responseItem['images']) && isset($responseItem['images'])) {
            $data['product_image'] = [];
            foreach ($responseItem['images'] as $item) {
                if (!empty($item['image'])) {
                    array_push($data['product_image'], [
                        'image' => $item['image'],
                        'sort_order' =>  0
                    ]);
                }
            }
        }

        $data['product_discount'] = [
            [
                'customer_group_id' => 0,
                'quantity' => 100,
                'priority' => 0,
                'price' => abs((int)$responseItem['discount']) ?? 0,
                'date_start' => date("d.m.y"),
                'date_end' => date("d.m.y")
            ]
        ];

        $data['model'] = '';
        $data['sku'] = '';
        $data['upc'] = '';
        $data['ean'] = '';
        $data['jan'] = '';
        $data['isbn'] = '';
        $data['mpn'] = '';
        $data['location'] = '';
        $data['unit_measure'] = '';
        $data['tax_class_id'] = '0';
        $data['quantity'] = '1';
        $data['minimum'] = '1';
        $data['subtract'] = '1';
        $data['stock_status_id'] = '6';
        $data['shipping'] = '1';
        $data['date_available'] = date('Y-m-d');
        $data['length'] = '';
        $data['width'] = (float)$responseItem['width'];
        $data['height'] = '';
        $data['length_class_id'] = '1';
        $data['weight'] = '';
        $data['weight_class_id'] = '1';
        $data['status'] = '1';
        $data['sort_order'] = '1';
        $data['category'] = '';
        $data['filter'] = '';
        $data['product_store'] = [
            0 => '0'
        ];
        $data['download'] = '';
        $data['related'] = '';
        $data['points'] = '';
        $data['product_reward'] = [
            1 => [
                'points' => ''
            ],
        ];
        $data['product_seo_url'] = [
            0 => [
                2 => '',
                1 => '',
            ],
        ];
        $data['product_layout'] = [
            0 => ''
        ];

        $this->load->model('catalog/product');

        if ($this->model_catalog_product->getTotalProductsByGUIDCharacteristic($data['GUID'], $data['GUID_Characteristic']) > 0) {
            $this->model_catalog_product->editProduct(
                $this->model_catalog_product->getProductByGUID(
                    $data['GUID'],
                    $data['GUID_Characteristic']
                ),
                $data
            );
        } else {
            $this->model_catalog_product->addProduct($data);
        }

        return $data;
    }

    /**
     * Parse json category
     *
     * @param array $responseItem
     * @return array|bool
     */
    public function parseCategoryResponse(array $responseItem)
    {
        $this->load->model('catalog/category');
        $data = [];

        $data['GUID'] = $responseItem['categoryGUID'];

        if (!empty($responseItem['parentGUID']) && isset($responseItem['parentGUID'])) {
            $data['parent_id'] = 0;
            if ($this->model_catalog_category->getTotalCategoryByGUID($responseItem['parentGUID']) > 0) {
                $data['parent_id'] = $this->model_catalog_category->getCategoryByGUID($responseItem['parentGUID']);
            }
            $data['top'] = 0;
        } else {
            $data['top'] = 1;
            $data['parent_id'] = 0;
        }

        $data['column'] = 0;
        $data['sort_order'] = 0;
        $data['status'] = 1;
        $data['image'] = '';
        $data['category_description'] = [
            1 => [
                'name' => $responseItem['categoryName'],
                'description' => '',
                'meta_title' => '',
                'meta_description' => '',
                'meta_keyword' => ''
            ],
            2 => [
                'name' => $responseItem['categoryName'],
                'description' => '',
                'meta_title' => '',
                'meta_description' => '',
                'meta_keyword' => ''
            ],
        ];
        $data['category_store'] = [
            0
        ];

        if ($this->model_catalog_category->getTotalCategoryByGUID($data['GUID']) > 0) {
            $this->model_catalog_category->editCategory($this->model_catalog_category->getCategoryByGUID($data['GUID']), $data);
        } else {
            $this->model_catalog_category->addCategory($data);
        }

        return $data;
    }
}
