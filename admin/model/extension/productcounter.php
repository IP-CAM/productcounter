<?php
class ModelExtensionProductcounter extends Model {
	public function createTable(){
	   $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pcounter` (
                              `pcounter_id` int(11) NOT NULL AUTO_INCREMENT,
                              `category_id` int(11) NOT NULL,
                              `total` int(11) NOT NULL,
                              PRIMARY KEY (`pcounter_id`),
                              KEY `category_id` (`category_id`)
                        )");
	}
    
    public function dropTable(){
	   $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "pcounter`");
	}
    
    public function updateTotal($category_id, $total){
        //Затираем старую запись если есть
        $this->db->query("DELETE FROM `" . DB_PREFIX . "pcounter` WHERE `category_id` = '" . (int)$category_id . "'");
        //Добавляем новую запись о количестве
        $this->db->query("INSERT INTO `" . DB_PREFIX . "pcounter` SET `category_id` = '" . (int)$category_id . "', `total` = '" . (int)$total . "'");
    }    
}