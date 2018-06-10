<?php

class ModelStockroomNomenclature extends Model
{
    /**
     * @param $id
     * @return mixed
     */
    public function getNomenclature($id)
    {
        $query = $this->db->query("SELECT st.amount AS amount, st.nomenclature_id AS nomenclature_id, c2.name AS nomenclature_name  FROM " . DB_PREFIX . "stockroom_nomenclature st INNER JOIN " . DB_PREFIX . "product c1 ON (st.nomenclature_id = c1.product_id)  LEFT JOIN " . DB_PREFIX . "product_description c2 ON (st.nomenclature_id = c2.product_id) WHERE st.ID = '" . (int)$id . "' AND c2.language_id = 1");

        return $query->row;
    }

    /**
     * Get all nomenclatures in this stockroom
     *
     * @param $stockroom_id
     * @param array $data
     * @return mixed
     */
    public function getNomenclatures($stockroom_id, $data = array())
    {
        $sql = "SELECT st.ID AS id, c2.image AS image, c3.name AS name, c3.description AS description, st.amount AS amount, c1.name AS name_stockroom FROM " . DB_PREFIX . "stockroom_nomenclature st INNER JOIN " . DB_PREFIX . "stockroom c1 ON (st.stockroom_id = c1.stockroom_id) INNER JOIN " . DB_PREFIX . "product c2 ON (st.nomenclature_id = c2.product_id) LEFT JOIN " . DB_PREFIX . "product_description c3 ON (c2.product_id = c3.product_id) WHERE c3.language_id = 1 AND st.stockroom_id = '" . (int)$stockroom_id . "'";


        $sort_data = array(
            'name',
            'description',
            'amount'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY nomenclature_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Get count nomenclatures
     *
     * @param $stockroom_id
     * @return mixed
     */
    public function getTotalNomenclatures($stockroom_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom_nomenclature st WHERE st.stockroom_id = '" . (int)$stockroom_id . "'");

        return $query->row['total'];
    }

    /**
     * Delete nomenclature
     *
     * @param $id
     */
    public function deleteNomenclature($id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "stockroom_nomenclature WHERE ID = '" . (int)$id . "'");

        $this->cache->delete('stockroom_nomenclature');
    }

    /**
     * Get nomenclature for autocomplete
     *
     * @param array $data
     * @return mixed
     */
    public function getNomenclatureForAutocomplete($data = array())
    {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "product_description oc INNER JOIN " . DB_PREFIX . "product c1 ON (c1.product_id = oc.product_id) WHERE oc.language_id = 1 AND oc.name LIKE '" . $data['filter_name'] . "%'";

            $sort_data = array(
                'name',
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            } else {
                $sql .= " ORDER BY name";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        }
    }

    /**
     * Add new nomenclature
     *
     * @param $stockroom_id
     * @param $data
     * @return bool
     */
    public function addNomenclature($stockroom_id, $data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "stockroom_nomenclature SET stockroom_id = '" . (int)$stockroom_id . "', `amount` = '" . (int)$data['amount'] . "', `nomenclature_id` = '" . (int)$data['nomenclature_id'] . "'");

        $this->cache->delete('stockroom_nomenclature');

        return true;
    }

    /**
     * Edit ID nomenclature
     *
     * @param $id
     * @param $data
     */
    public function editNomenclature($id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "stockroom_nomenclature SET `amount` = '" . (int)$data['amount'] . "', `nomenclature_id` = '" . (int)$data['nomenclature_id'] . "'WHERE ID = '" . (int)$id . "'");

        $this->cache->delete('stockroom');
    }

    /**
     * Сheck to dublicate nomenclature in stockroom
     *
     * @param $stockroom_id
     * @param $data
     * @return int
     */
    public function dublicateNomenclature($stockroom_id, $data)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom_nomenclature st WHERE st.stockroom_id = '" . (int)$stockroom_id . "' AND st.nomenclature_id = '" . (int)$data['nomenclature_id'] . "'");

        return (int)$query->row['total'];
    }

    /**
     * Get ID value to nomenclature and stockroom
     *
     * @param $stockroom_id
     * @param $data
     * @return int
     */
    public function getIDNomenclature($stockroom_id, $data)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom_nomenclature st WHERE st.stockroom_id = '" . (int)$stockroom_id . "' AND nomenclature_id = '" . (int)$data['nomenclature_id'] . "'");

        return (int)$query->row['total'];
    }
}
