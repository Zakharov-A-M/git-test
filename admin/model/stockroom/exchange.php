<?php

class ModelStockroomExchange extends Model
{
    const AMOUNT = "http://86.57.128.226:8085/KA_TEST/hs/DataExchangeSite/643/testSite/amount";
    const STOCKROOM = "http://86.57.128.226:8085/KA_TEST/hs/DataExchangeSite/643/testSite/stockroom";

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
     * Get amount nomenclatures from 1C
     *
     * @return mixed|string
     * @throws Exception
     */
    public function getAmount()
    {
        $result = $this->execRequest(self::AMOUNT);
        return $result['balance'];
    }

    /**
     * Get stockroom from 1C
     *
     * @return mixed
     * @throws Exception
     */
    public function getStockroom()
    {
        $result = $this->execRequest(self::STOCKROOM);
        return $result['stockrooms'];
    }

    /**
     * Parse and save or edit Stockroom from 1C
     *
     * @param array $responseItem
     * @return array
     */
    public function parseStockroomResponse(array $responseItem)
    {
        $data = [];
        $data['GUID'] =    $responseItem['GUID'];
        $data['country_id'] = (int)$responseItem['country'];
        $data['name'] =    $responseItem['name'];
        $data['address'] = $responseItem['address'] ?? NULL;
        $this->load->model('stockroom/stockroom');

        if ($this->model_stockroom_stockroom->getTotalStockroomsGUID($data['GUID']) > 0) {
            $this->model_stockroom_stockroom->editStockroom($this->model_stockroom_stockroom->getStockroomGUID($data['GUID']), $data);
        } else {
            $this->model_stockroom_stockroom->addStockroom($data);
        }

        return $data;
    }

    /**
     * Parse amount response from 1C
     *
     * @param array $responseItem
     * @return array
     */
    public function parseAmountResponse(array $responseItem)
    {
        $this->load->model('stockroom/stockroom');
        $data = [];
        $data['amount'] = (int)$responseItem['amount'] ?? 0;
        if ($this->model_stockroom_stockroom->getTotalStockroomsGUID($responseItem['stockroom']) > 0) {
            $stockroom_id = $this->model_stockroom_stockroom->getStockroomGUID($responseItem['stockroom']);

            $this->load->model('catalog/product');
            if ($this->model_catalog_product->getTotalProductsByGUIDCharacteristic($responseItem['GUID_Nomenclature'], $responseItem['GUID_Characteristic']) > 0) {
                $data['nomenclature_id'] = $this->model_catalog_product->getProductByGUID($responseItem['GUID_Nomenclature'], $responseItem['GUID_Characteristic']);

                $this->load->model('stockroom/nomenclature');

                if ($this->model_stockroom_nomenclature->dublicateNomenclature($stockroom_id, $data) > 0) {
                    $this->model_stockroom_nomenclature->editNomenclature($this->model_stockroom_nomenclature->getIDNomenclature($stockroom_id, $data['nomenclature_id']), $data);
                } else {
                    $this->model_stockroom_nomenclature->addNomenclature($stockroom_id, $data);
                }
            }
        }

        return $data;
    }
}
