<?php

class ControllerModuleGallery extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('module/gallery');

        $this->load->model('design/gallery');

        $this->getList();
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'module/gallery')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!$this->request->post['width']) {
            $this->error['width'] = $this->language->get('error_width');
        }

        if (!$this->request->post['height']) {
            $this->error['height'] = $this->language->get('error_height');
        }

        return !$this->error;
    }

    public function add() {
        $this->load->language('module/gallery');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/gallery');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {// && $this->validateForm()) {
            $this->model_design_gallery->addGallery($this->request->post, $this->request->files);
            $this->session->data['success'] = $this->language->get('text_success');
            $url = '';
            //$this->response->redirect($this->url->link('module/gallery', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }
        $this->getForm();
    }

    public function edit() {
        $this->load->language('module/gallery');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/gallery');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_design_galleru->editGallery($this->request->get['gallery_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';
            $this->response->redirect($this->url->link('module/gallery', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('module/gallery');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/gallery');

        if (isset($this->request->post['selected'])) { //&& $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $galerry_id) {
                $this->model_design_gallery->deleteGallery($galerry_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            $this->response->redirect($this->url->link('module/gallery', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getList();
    }

    protected function getList() {
        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title_sub'] = $this->language->get('heading_title_sub');
        $data['entry_title'] = $this->language->get('entry_title');
        $data['entry_desc'] = $this->language->get('entry_desc');
        $data['entry_image'] = $this->language->get('entry_image');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_enable'] = $this->language->get('entry_enable');
        $data['entry_disable'] = $this->language->get('entry_disable');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/gallery', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );

        $data['add'] = $this->url->link('module/gallery/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $data['delete'] = $this->url->link('module/gallery/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $data['products'] = array();

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');

        $data['column_image'] = $this->language->get('column_image');
        $data['column_name'] = $this->language->get('column_name');
        $data['column_model'] = $this->language->get('column_model');
        $data['column_price'] = $this->language->get('column_price');
        $data['column_quantity'] = $this->language->get('column_quantity');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_status_author'] = $this->language->get('column_status_author');
        $data['column_status_management'] = $this->language->get('column_status_management');
        $data['column_status_editor'] = $this->language->get('column_status_editor');
        $data['column_action'] = $this->language->get('column_action');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_copy'] = $this->language->get('button_copy');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_filter'] = $this->language->get('button_filter');

        $data['token'] = $this->session->data['token'];

        $results = $this->model_design_gallery->getGallerys();
        if ($results != null) {
           foreach ($results as $result) {
                $data['gallerys'][] = array(
                    'gallery_id' => $result['gallery_id'],
                    'title' => $result['title'],
                    'desc' => $result['description'],
                    'status' => $result['status'],
                    'image' => $result['image'],
                    'edit' => $this->url->link('module/gallery/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $result['gallery_id'] . $url, 'SSL')
                );
            }
        }else{
            $data['gallerys'][] = null;
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $url = '';

        $pagination = new Pagination();

        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('module/gallery', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('module/gallery_list.tpl', $data));
    }

    protected function getForm() {
        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title_sub'] = $this->language->get('heading_title_sub');
        $data['entry_title'] = $this->language->get('entry_title');
        $data['entry_desc'] = $this->language->get('entry_desc');
        $data['entry_image'] = $this->language->get('entry_image');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_enable'] = $this->language->get('entry_enable');
        $data['entry_disable'] = $this->language->get('entry_disable');

        $data['help_title'] = $this->language->get('help_title');
        $data['help_desc'] = $this->language->get('help_des');
        $data['help_image'] = $this->language->get('help_image');
        $data['help_status'] = $this->language->get('help_status');

        $data['button_save'] = $this->language->get('button_save');

        $data['image'] = $this->request->files;

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['width'])) {
            $data['error_width'] = $this->error['width'];
        } else {
            $data['error_width'] = '';
        }

        if (isset($this->error['height'])) {
            $data['error_height'] = $this->error['height'];
        } else {
            $data['error_height'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
        );

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/gallery', 'token=' . $this->session->data['token'], 'SSL')
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/gallery', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
            );
        }

        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } elseif (!empty($module_info)) {
            $data['title'] = $module_info['title'];
        } else {
            $data['title'] = '';
        }

        if (isset($this->request->post['desc'])) {
            $data['desc'] = $this->request->post['desc'];
        } elseif (!empty($module_info)) {
            $data['desc'] = $module_info['desc'];
        } else {
            $data['desc'] = 5;
        }

        if (isset($this->request->file['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($module_info)) {
            $data['image'] = $module_info['image'];
        } else {
            $data['image'] = 5;
        }


        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '';
        }

        $url = '';
        if (!isset($this->request->get['gellery_id'])) {
            $data['save'] = $this->url->link('module/gallery/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $data['save'] = $this->url->link('module/gallery/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['gallery_id'] . $url, 'SSL');
        }


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('module/gallery_form.tpl', $data));
    }

    protected function validateForm() {
//		if (!$this->user->hasPermission('modify', 'module/gallery')) {
//			$this->error['warning'] = $this->language->get('error_permission');
//		}
//		
//		return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'module/gallery')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

//
//	public function autocomplete() {
//		$json = array();
//
//		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
//			$this->load->model('catalog/product');
//			$this->load->model('catalog/option');
//
//			if (isset($this->request->get['filter_name'])) {
//				$filter_name = $this->request->get['filter_name'];
//			} else {
//				$filter_name = '';
//			}
//
//			if (isset($this->request->get['filter_model'])) {
//				$filter_model = $this->request->get['filter_model'];
//			} else {
//				$filter_model = '';
//			}
//
//			if (isset($this->request->get['limit'])) {
//				$limit = $this->request->get['limit'];
//			} else {
//				$limit = 5;
//			}
//
//			$filter_data = array(
//				'filter_name'  => $filter_name,
//				'filter_model' => $filter_model,
//				'start'        => 0,
//				'limit'        => $limit
//			);
//
//			$results = $this->model_catalog_product->getProducts($filter_data);
//
//			foreach ($results as $result) {
//				$option_data = array();
//
//				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);
//
//				foreach ($product_options as $product_option) {
//					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);
//
//					if ($option_info) {
//						$product_option_value_data = array();
//
//						foreach ($product_option['product_option_value'] as $product_option_value) {
//							$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);
//
//							if ($option_value_info) {
//								$product_option_value_data[] = array(
//									'product_option_value_id' => $product_option_value['product_option_value_id'],
//									'option_value_id'         => $product_option_value['option_value_id'],
//									'name'                    => $option_value_info['name'],
//									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
//									'price_prefix'            => $product_option_value['price_prefix']
//								);
//							}
//						}
//
//						$option_data[] = array(
//							'product_option_id'    => $product_option['product_option_id'],
//							'product_option_value' => $product_option_value_data,
//							'option_id'            => $product_option['option_id'],
//							'name'                 => $option_info['name'],
//							'type'                 => $option_info['type'],
//							'value'                => $product_option['value'],
//							'required'             => $product_option['required']
//						);
//					}
//				}
//
//				$json[] = array(
//					'product_id' => $result['product_id'],
//					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
//					'model'      => $result['model'],
//					'option'     => $option_data,
//					'price'      => $result['price']
//				);
//			}
//		}
//
//		$this->response->addHeader('Content-Type: application/json');
//		$this->response->setOutput(json_encode($json));
//	}
}
