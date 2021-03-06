<?php
class ModelDesignLayout extends Model {
	
        public function getLayout($route) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_route WHERE '" . $this->db->escape($route) . "' LIKE route AND store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY route DESC LIMIT 1");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return 0;
		}
	}
	
	public function getLayoutModules($layout_id, $position) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_module WHERE layout_id = '" . (int)$layout_id . "' AND position = '" . $this->db->escape($position) . "' ORDER BY sort_order");
		
		return $query->rows;
	}
        
        
    public function addLayout($data) {
        $this->event->trigger('pre.admin.layout.add', $data);

        $this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = '" . $this->db->escape($data['name']) . "'");

        $layout_id = $this->db->getLastId();

        if (isset($data['layout_route'])) {
            foreach ($data['layout_route'] as $layout_route) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int) $layout_id . "', store_id = '" . (int) $layout_route['store_id'] . "', route = '" . $this->db->escape($layout_route['route']) . "'");
            }
        }

        if (isset($data['layout_module'])) {
            foreach ($data['layout_module'] as $layout_module) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "layout_module SET layout_id = '" . (int) $layout_id . "', code = '" . $this->db->escape($layout_module['code']) . "', position = '" . $this->db->escape($layout_module['position']) . "', sort_order = '" . (int) $layout_module['sort_order'] . "'");
            }
        }

        $this->event->trigger('post.admin.layout.add', $layout_id);

        return $layout_id;
    }

    public function editLayout($layout_id, $data) {
        $this->event->trigger('pre.admin.layout.edit', $data);

        $this->db->query("UPDATE " . DB_PREFIX . "layout SET name = '" . $this->db->escape($data['name']) . "' WHERE layout_id = '" . (int) $layout_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE layout_id = '" . (int) $layout_id . "'");

        if (isset($data['layout_route'])) {
            foreach ($data['layout_route'] as $layout_route) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int) $layout_id . "', store_id = '" . (int) $layout_route['store_id'] . "', route = '" . $this->db->escape($layout_route['route']) . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "layout_module WHERE layout_id = '" . (int) $layout_id . "'");

        if (isset($data['layout_module'])) {
            foreach ($data['layout_module'] as $layout_module) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "layout_module SET layout_id = '" . (int) $layout_id . "', code = '" . $this->db->escape($layout_module['code']) . "', position = '" . $this->db->escape($layout_module['position']) . "', sort_order = '" . (int) $layout_module['sort_order'] . "'");
            }
        }

        $this->event->trigger('post.admin.layout.edit', $layout_id);
    }

    public function deleteLayout($layout_id) {
        $this->event->trigger('pre.admin.layout.delete', $layout_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "layout WHERE layout_id = '" . (int) $layout_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE layout_id = '" . (int) $layout_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "layout_module WHERE layout_id = '" . (int) $layout_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE layout_id = '" . (int) $layout_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int) $layout_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "information_to_layout WHERE layout_id = '" . (int) $layout_id . "'");

        $this->event->trigger('post.admin.layout.delete', $layout_id);
    }

    public function getLayouts($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "layout";

        $sort_data = array('name');

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

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getLayoutRoutes($layout_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_route WHERE layout_id = '" . (int) $layout_id . "'");

        return $query->rows;
    }    

    public function getTotalLayouts() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "layout");

        return $query->row['total'];
    }

    public function addModuleToLayout($code, $layout_id, $data = array()) {
        $layout_id = (int) $layout_id;
        $this->db->query("INSERT INTO " . DB_PREFIX . "layout_module SET layout_id = '" . (int) $layout_id . "', code = '" . $this->db->escape($code) . "', position = '" . $this->db->escape($data['position']) . "', sort_order = '" . (int) $data['sort_order'] . "'");
    }

}