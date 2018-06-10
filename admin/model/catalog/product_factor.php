<?php

class ModelCatalogProductFactor extends Model
{
    /**
     * Added factor for product
     *
     * @param $productId
     * @param $data
     * @return mixed
     */
    public function addProductFactor($productId, $data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_factor SET product_id = '" . (int)$productId . "', pr1 = '" . $this->db->escape((string)$data['pr1']) . "', pr2 = '" . $this->db->escape((string)$data['pr2']) . "', pr3 = '" . $this->db->escape((string)$data['pr3']) . "', pr4 = '" . $this->db->escape((string)$data['pr4']) . "', pr5 = '" . $this->db->escape((string)$data['pr5']) . "', pr6 = '" . $this->db->escape((string)$data['pr6']) . "', pr7 = '" . $this->db->escape((string)$data['pr7']) . "', pr8 = '" . $this->db->escape((string)$data['pr8']) . "', pr9 = '" . $this->db->escape((string)$data['pr9']) . "'");
        return  $productId;
    }

    /**
     * Edited factor product
     *
     * @param $productId
     * @param $data
     * @return mixed
     */
    public function editProductFactor($productId, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product_factor SET pr1 = '" . $this->db->escape((string)$data['pr1']) . "', pr2 = '" . $this->db->escape((string)$data['pr2']) . "', pr3 = '" . $this->db->escape((string)$data['pr3']) . "', pr4 = '" . $this->db->escape((string)$data['pr4']) . "', pr5 = '" . $this->db->escape((string)$data['pr5']) . "', pr6 = '" . $this->db->escape((string)$data['pr6']) . "', pr7 = '" . $this->db->escape((string)$data['pr7']) . "', pr8 = '" . $this->db->escape((string)$data['pr8']) . "', pr9 = '" . $this->db->escape((string)$data['pr9']) . "' WHERE product_id = '" . (int)$productId . "'");
        return  $productId;
    }

}