<?php

class ControllerModulePaymentApprovementAdmin extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('module/payment_approvement_admin');

        $this->load->model('design/payment_approvement_admin');

        $this->getList();
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'module/comunity_admin')) {
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
        $this->load->language('module/comunity_admin');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/comunity_admin');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {// && $this->validateForm()) {
            $this->model_design_comunity_admin->addComunity($this->request->post,$this->request->files);
            $this->session->data['success'] = $this->language->get('text_success');
            $url = '';
            $this->response->redirect($this->url->link('module/comunity_admin', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }
        $this->getForm();
    }

    public function edit() {     
        $this->load->language('module/payment_approvement_admin');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/payment_approvement_admin');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_design_payment_approvement_admin->editPayment($this->request->get['payment_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_price'])) {
                $url .= '&filter_price=' . $this->request->get['filter_price'];
            }

            if (isset($this->request->get['filter_quantity'])) {
                $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('module/payment_approvement_admin', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('module/payment_approvement_admin');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('design/payment_approvement_admin');

        if (isset($this->request->post['selected'])) { //&& $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $calendar_event_id) {
                $this->model_design_payment_approvement_admin->deletePayment($calendar_event_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            $this->response->redirect($this->url->link('module/payment_approvement_admin', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getList();
    }

    protected function getList() {
            
        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title_sub'] = $this->language->get('heading_title_sub');                
        $data['entry_image'] = $this->language->get('entry_image');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_date'] = $this->language->get('entry_date');
        $data['entry_invoice'] = $this->language->get('entry_invoice');
        $data['entry_customer_id'] = $this->language->get('entry_customer_id');
        $data['entry_customer_name'] = $this->language->get('entry_customer_name');
        
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
            'href' => $this->url->link('module/payment_approvement_admin', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );

        $data['add'] = $this->url->link('module/payment_approvement_admin/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $data['delete'] = $this->url->link('module/payment_approvement_admin/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

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

        $results = $this->model_design_payment_approvement_admin->getPayments();
        if ($results != null) {
            foreach ($results as $result) {
                $data['payments'][] = array(
                    'payment_id' => $result['payment_id'],
                    'image' => $result['image'],
                    'customer_id' => $result['customer_id'],
                    'customer_name' => $result['firstname'],                    
                    'date' => $result['date'],
                    'status' => $result['status'],
                    'invoice' => $result['invoice'],
                    
                    'edit' => $this->url->link('module/payment_approvement_admin/edit', 'token=' . $this->session->data['token'] . '&payment_id=' . $result['payment_id'] . $url, 'SSL')
                );
            }
        } else {
            $data['payments'][] = null;
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
        $pagination->url = $this->url->link('module/payment_approvement_admin', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('module/payment_approvement_admin_list.tpl', $data));
    }

    protected function getForm() {   
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title_sub'] = $this->language->get('heading_title_sub');
        $data['heading_title_sub'] = $this->language->get('heading_title_sub');                
        $data['entry_image'] = $this->language->get('entry_image');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_date'] = $this->language->get('entry_date');
        $data['entry_invoice'] = $this->language->get('entry_invoice');
        $data['entry_customer_id'] = $this->language->get('entry_customer_id');
        $data['entry_customer_name'] = $this->language->get('entry_customer_name');
        
        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_description'] = $this->language->get('entry_description');
        $data['entry_image'] = $this->language->get('entry_image');
        $data['entry_link'] = $this->language->get('entry_link');

        $data['help_title'] = $this->language->get('help_title');
        $data['help_desc'] = $this->language->get('help_des');
        $data['help_image'] = $this->language->get('help_image');
        $data['help_status'] = $this->language->get('help_status');
        $data['entry_description'] = $this->language->get('entry_description');
        $data['entry_date'] = $this->language->get('entry_date');

        $data['help_name'] = $this->language->get('help_name');
        
        $data['button_save'] = $this->language->get('button_save');

        $data['image'] = $this->request->files;
                
        if (isset($this->request->get['payment_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {            
                $this->load->model('design/payment_approvement_admin');
		$payment_info = $this->model_design_payment_approvement_admin->getPayment($this->request->get['payment_id']);                
	}
        
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
                'href' => $this->url->link('module/payment_approvement_admin', 'token=' . $this->session->data['token'], 'SSL')
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('module/payment_approvement_admin', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
            );
        }

        if (isset($this->request->post['payment_id'])) {
            $data['payment_id'] = $this->request->post['payment_id'];
        } else if (!empty($payment_info)) {
            $data['payment_id'] = $payment_info[0]['payment_id'];
        } else {
            $data['payment_id'] = '';
        }
        
        if (isset($this->request->post['customer_id'])) {
            $data['customer_id'] = $this->request->post['customer_id'];
        } else if (!empty($payment_info)) {
            $data['customer_id'] = $payment_info[0]['customer_id'];
        } else {
            $data['customer_id'] = '';
        }
                
        if (isset($this->request->post['customer_name'])) {
            $data['customer_name'] = $this->request->post['customer_name'];
        } else if (!empty($payment_info)) {
            $data['customer_name'] = $payment_info[0]['firstname'];
        } else {
            $data['customer_name'] = '';
        }

        if (isset($this->request->post['invoice'])) {
            $data['invoice'] = $this->request->post['invoice'];
        } elseif (!empty($payment_info)) {
            $data['invoice'] = $payment_info[0]['invoice'];
        } else {
            $data['invoice'] = '';
        }
        
        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($payment_info)) {
            $data['status'] = $payment_info[0]['status'];
        } else {
            $data['status'] = '';
        }

        $url = '';
        if (!isset($this->request->get['payment_id'])) {
            $data['save'] = $this->url->link('module/payment_approvement_admin/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $data['save'] = $this->url->link('module/payment_approvement_admin/edit', 'token=' . $this->session->data['token'] . '&payment_id=' . $this->request->get['payment_id'] . $url, 'SSL');
        }


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('module/payment_approvement_admin_form.tpl', $data));
    }

    protected function validateForm() {
//		if (!$this->user->hasPermission('modify', 'module/comunity_admin')) {
//			$this->error['warning'] = $this->language->get('error_permission');
//		}
//		
//		return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'module/comunity_admin')) {
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
