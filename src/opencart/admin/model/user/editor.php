<?php
class ModelUserEditor extends Model {

	public function getEditor(){
		return $this->db->query("SELECT * FROM ".DB_PREFIX."editor ")->rows;
	}

	public function getCustomer(){
		return $this->db->query("SELECT * FROM ".DB_PREFIX."customer ")->rows;
	}
		
}