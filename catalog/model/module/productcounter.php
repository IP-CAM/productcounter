<?php
class ModelModuleProductcounter extends Model {
    /**
     * Getting count products by cat from DB
     * */
    public function getTotalByCat($cat_id){
       $query = $this->db->query("SELECT total FROM " . DB_PREFIX . "pcounter WHERE category_id = '" .(int)$cat_id. "'");

       if ($query->num_rows) { 
          return $query->row['total'];
       }else{
          return 0;  
       }
    }
}