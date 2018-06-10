<?php

class ModelStockroomStockroom extends Model
{
    /**
     * Add stockroom
     *
     * @param $data
     * @return mixed
     */
	public function addStockroom($data)
    {
		$this->db->query("INSERT INTO " . DB_PREFIX . "stockroom SET name = '" . $data['name'] . "', `address` = '" . $data['address'] . "', `country_id` = '" . (int)$data['country_id'] . "'");

        $stockroom_id = $this->db->getLastId();

		if (isset($data['GUID']) && !empty($data['GUID'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "stockroom SET GUID = '" . $data['GUID'] . "' WHERE stockroom_id = '" . (int)$stockroom_id . "'");
        }

		$this->cache->delete('stockroom');

		return true;
	}

    /**
     * Edit stockroom
     *
     * @param $stockroom_id
     * @param $data
     */
	public function editStockroom($stockroom_id, $data)
    {
		$this->db->query("UPDATE " . DB_PREFIX . "stockroom SET name = '" . $data['name'] . "', `address` = '" . $data['address'] . "', `country_id` = '" . (int)$data['country_id'] . "'WHERE stockroom_id = '" . (int)$stockroom_id . "'");

		$this->cache->delete('stockroom');
	}

    /**
     * Delete stockroom
     *
     * @param $stockroom_id
     */
	public function deleteStockroom($stockroom_id)
    {
		$this->db->query("DELETE FROM " . DB_PREFIX . "stockroom WHERE stockroom_id = '" . (int)$stockroom_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "stockroom_nomenclature WHERE stockroom_id = '" . (int)$stockroom_id . "'");

		$this->cache->delete('stockroom');
	}

    /**
     * Get this stockroom
     *
     * @param $stockroom_id
     * @return mixed
     */
	public function getStockroom($stockroom_id)
    {
		$query = $this->db->query("SELECT st.stockroom_id AS stockroom_id, st.name AS name, st.GUID AS GUID, st.country_id AS country_id, c1.name AS country_name, st.address AS address FROM " . DB_PREFIX . "stockroom st LEFT JOIN " . DB_PREFIX . "stockroom_country c1 ON (st.country_id = c1.country_id) WHERE st.stockroom_id = '" . (int)$stockroom_id . "'");
		
		return $query->row;
	}

    /**
     * Get name stockroom
     *
     * @param $stockroom_id
     * @return mixed
     */
    public function getStockroomName($stockroom_id)
    {
        $query = $this->db->query("SELECT st.name AS name FROM " . DB_PREFIX . "stockroom st WHERE st.stockroom_id = '" . (int)$stockroom_id . "'");

        return $query->row['name'];
    }

    /**
     * Get address stockroom
     *
     * @param $stockroom_id
     * @return mixed
     */
    public function getStockroomAddress($stockroom_id)
    {
        $query = $this->db->query("SELECT  st.address AS address FROM " . DB_PREFIX . "stockroom st WHERE st.stockroom_id = '" . (int)$stockroom_id . "'");

        return $query->row['address'];
    }

	public function getStockrooms($data = array())
    {
        $sql = "SELECT st.stockroom_id AS stockroom_id, st.name AS name, st.GUID AS GUID, st.country_id AS country_id, c1.name AS country_name, st.address AS address FROM " . DB_PREFIX . "stockroom st LEFT JOIN " . DB_PREFIX . "stockroom_country c1 ON (st.country_id = c1.country_id)";


		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
		}

		//$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'name',
			'country_name',
			'address'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			//$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			//$sql .= " ASC";
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
     * Get count stockroom
     *
     * @return mixed
     */
	public function getTotalStockrooms()
    {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom");

		return $query->row['total'];
	}

    /**
     * Get count stockroom from GUID
     *
     * @param $guid
     * @return mixed
     */
    public function getTotalStockroomsGUID($guid)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stockroom WHERE GUID = '" .$guid . "'");

        return (int)$query->row['total'];
    }

    /**
     * Get this stockroom from GUID
     *
     * @param $guid
     * @return mixed
     */
    public function getStockroomGUID($guid)
    {
        $query = $this->db->query("SELECT st.stockroom_id AS stockroom_id FROM " . DB_PREFIX . "stockroom st WHERE st.GUID = '" . $guid . "'");

        return (int)$query->row['stockroom_id'];
    }
}
