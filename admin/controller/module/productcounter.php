<?php
class ControllerModuleProductcounter extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('module/productcounter');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_extension_module->addModule('productcounter', $this->request->post);
			} else {
				$this->model_extension_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/productcounter.tpl', $data));
	}
    
    //Пересчитываем количество товаров в категориях
    public function recount(){
        $this->load->model('extension/productcounter');
		$categories = $this->getCategories(0);

		foreach ($categories as $category) {
            $cattotal = 0;
			if ($category['category_id']) {
				$children = $this->getCategories($category['category_id']);

				foreach($children as $child) {
				    $child_total = $this->getTotalProductsByCategory($child['category_id']);
                    $cattotal += $child_total;
                    $this->model_extension_productcounter->updateTotal($child['category_id'], $child_total);
                }
			}
            
            $cattotal += $this->getTotalProductsByCategory($category['category_id']);
            $this->model_extension_productcounter->updateTotal($category['category_id'], $cattotal);
        }
    }
    
    public function getCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

		return $query->rows;
	}
    
    public function getTotalProductsByCategory($category_id) {	   
        $sql = "SELECT COUNT(p2c.product_id) AS total FROM " . DB_PREFIX . "product_to_category p2c WHERE p2c.category_id = '" .(int)$category_id. "' ";
        $query = $this->db->query($sql);

		return $query->row['total'];
	}
    
    public function install(){
        $this->load->model('extension/productcounter');
        $this->model_extension_productcounter->createTable();
        //Обработчик события
        $this->load->model('extension/event');
        $this->model_extension_event->addEvent('pcounter_recount_on_add', 'pre.admin.product.add', 'module/productcounter/recount');
        $this->model_extension_event->addEvent('pcounter_recount_on_edit', 'pre.admin.product.edit', 'module/productcounter/recount');
        $this->model_extension_event->addEvent('pcounter_recount_on_del', 'pre.admin.product.delete', 'module/productcounter/recount');
        $this->model_extension_event->addEvent('pcounter_recount_on_catadd', 'pre.admin.category.add', 'module/productcounter/recount');
        $this->model_extension_event->addEvent('pcounter_recount_on_catedit', 'pre.admin.category.edit', 'module/productcounter/recount');
        $this->model_extension_event->addEvent('pcounter_recount_on_catdel', 'pre.admin.category.delete', 'module/productcounter/recount');
        
    }
    
    public function uninstall(){
        $this->load->model('extension/productcounter');
        $this->model_extension_productcounter->dropTable();
        //Удаляем обработчики
        $this->load->model('extension/event');
        $this->model_extension_event->deleteEvent('pcounter_recount_on_add');
        $this->model_extension_event->deleteEvent('pcounter_recount_on_edit');
        $this->model_extension_event->deleteEvent('pcounter_recount_on_del');
        $this->model_extension_event->deleteEvent('pcounter_recount_on_catadd');
        $this->model_extension_event->deleteEvent('pcounter_recount_on_catedit');
        $this->model_extension_event->deleteEvent('pcounter_recount_on_catdel');
    }
}