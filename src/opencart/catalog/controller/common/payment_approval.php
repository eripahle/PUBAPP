
<?php

class ControllerCommonPaymentApproval extends Controller {

    private $error = array();

    public function index() {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', ' ', 'SSL'));
        }
        $this->load->language('payment/payment_approval');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->getList();
    }

    public function add() {

        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', ' ', 'SSL'));
        }
        $customer_id = $this->customer->getId();
        $this->load->language('payment/payment_approval');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('payment/payment_approval');

        if (($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm())) {

            $dot = '.';
            $file_name_image = $this->request->files['image']['name'];
            //$file_ext_buku = strtolower(substr(strrchr($file_name_buku,'.'),1), $file_name_buku);
            $file_size_image = $this->request->files['image']['size'];
            $lokasi_image = md5(mt_rand()) . "_" . $file_name_image;
            $lokasi_image_di_database = "image/payment/" . $lokasi_image;
            move_uploaded_file($this->request->files['image']['tmp_name'], DIR_PAYMENT . "/" . $lokasi_image);


            $data2 = array(
                'lokasi_image' => $lokasi_image,
                'lokasi_image_di_database' => $lokasi_image_di_database,
            );

            $this->model_payment_payment_approval->addPayment($this->request->post, $customer_id, $data2);

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

            $this->response->redirect($this->url->link('common/payment_approval', '', 'SSL'));
        }

        $this->getForm();
    }

    public function edit() {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', ' ', 'SSL'));
        }
        $this->load->language('product/mybooks');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/product');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if (!empty($this->request->files['book']['name'])) {
                if ($this->checkValidRequestDrafBook()) {
                    $dot = '.';
                    $file_name_buku = $this->request->files['book']['name'];
                    //$file_ext_buku = strtolower(substr(strrchr($file_name_buku,'.'),1), $file_name_buku);
                    $file_size_buku = $this->request->files['book']['size'];

                    $lokasi_book = "file/" . date("smhymd") . "_" . $file_name_buku;
                    move_uploaded_file($this->request->files['book']['tmp_name'], DIR_BOOK . "/" . $lokasi_book);
                }
            } else {
                $lokasi_book = null;
            }
            $this->model_catalog_product->editProduct($this->request->get['product_id'], $this->request->post, $lokasi_book);

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
            if (!isset($this->request->files['book']['name'])) {
                $this->response->redirect($this->url->link('common/mybooks', ' ', 'SSL'));
            } else {
                $this->response->redirect($this->url->link('common/mybooks/getEditingList', ' ', 'SSL'));
            }
        }

        $this->getForm();
    }

    public function downloadBooks() {
        $this->load->model('payment/payment_approval');
        $payments = $this->model_payment_payment_approval->getPayment($this->request->get['payment_id']);
        $data['payment'] = array();

        foreach ($payments as $payment) {
            $linkPayment = 'system/engine/upload' . $payment['image'];

            $data['linkPayment'][] = array(
                'linkPayment' => $linkPayment
            );
        }

        $this->response->redirect($this->load->view('common/payment_approval.tpl', ' ', 'SSL'));
    }

    protected function getList() {

        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', ' ', 'SSL'));
        }
        $this->load->language('payment/payment_approval');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('payment/payment_approval');

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = null;
        }

        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = null;
        }

        if (isset($this->request->get['filter_price'])) {
            $filter_price = $this->request->get['filter_price'];
        } else {
            $filter_price = null;
        }

        if (isset($this->request->get['filter_quantity'])) {
            $filter_quantity = $this->request->get['filter_quantity'];
        } else {
            $filter_quantity = null;
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'pd.name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

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

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/payment_approval', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/payment_approval', ' ', 'SSL')
        );

        $data['add'] = $this->url->link('catalog/payment_approval/add', ' ', 'SSL');

        $data['payments'] = array();

        $filter_data = array(
            'filter_name' => $filter_name,
            'filter_model' => $filter_model,
            'filter_price' => $filter_price,
            'filter_quantity' => $filter_quantity,
            'filter_status' => $filter_status,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $results = $this->model_payment_payment_approval->getPayments($filter_data, $this->customer->getId());

        foreach ($results as $result) {
            $data['payments'][] = array(
                'payment_id' => $result['payment_id'],
                'image' => $result['image'],
                'invoice' => $result['invoice'],
                'status' => $result['status'],
                'download' => $result['image'],
                'delete' => $this->url->link('common/payment_approval/delete', '&payment_id=' . $result['payment_id'], 'SSL'),
            );
        }


        $this->load->model('tool/image');
        $customer_id = $this->customer->getId();

        $product_total = $this->model_payment_payment_approval->getTotalPayment($filter_data, $customer_id);

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_no_results'] = $this->language->get('text_no_results');

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
        $data['column_total_selling'] = $this->language->get('column_total_selling');
        $data['column_invoice'] = $this->language->get('column_invoice');


        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_model'] = $this->language->get('entry_model');
        $data['entry_price'] = $this->language->get('entry_price');
        $data['entry_quantity'] = $this->language->get('entry_quantity');
        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_copy'] = $this->language->get('button_copy');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_download'] = $this->language->get('button_download');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_filter'] = $this->language->get('button_filter');



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

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('common/mybooks', '&sort=pd.name' . $url, 'SSL');
        $data['sort_model'] = $this->url->link('common/mybooks', '&sort=p.model' . $url, 'SSL');
        $data['sort_price'] = $this->url->link('common/mybooks', '&sort=p.price' . $url, 'SSL');
        $data['sort_quantity'] = $this->url->link('common/mybooks', '&sort=p.quantity' . $url, 'SSL');
        $data['sort_status'] = $this->url->link('common/mybooks', '&sort=p.status' . $url, 'SSL');
        $data['sort_order'] = $this->url->link('common/mybooks', '&sort=p.sort_order' . $url, 'SSL');
        $data['sort_total'] = $this->url->link('common/mybooks', '&sort=p.price' . $url, 'SSL');

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

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('common/payment_approval', ' ' . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

        $data['filter_name'] = $filter_name;
        $data['filter_model'] = $filter_model;
        $data['filter_price'] = $filter_price;
        $data['filter_quantity'] = $filter_quantity;
        $data['filter_status'] = $filter_status;

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('default/template/common/payment_approval.tpl', $data));
    }

    public function getEditingList() {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', ' ', 'SSL'));
        }
        $this->load->language('product/mybooks');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/productbooks');
        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = null;
        }

        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = null;
        }

        if (isset($this->request->get['filter_price'])) {
            $filter_price = $this->request->get['filter_price'];
        } else {
            $filter_price = null;
        }

        if (isset($this->request->get['filter_quantity'])) {
            $filter_quantity = $this->request->get['filter_quantity'];
        } else {
            $filter_quantity = null;
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'pd.name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

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

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/product', ' ', 'SSL')
        );

        $data['add'] = $this->url->link('catalog/product/add', ' ', 'SSL');
        $data['copy'] = $this->url->link('catalog/product/copy', ' ', 'SSL');
        $data['delete'] = $this->url->link('catalog/product/delete', ' ', 'SSL');

        $data['payments'] = array();

        $filter_data = array(
            'filter_name' => $filter_name,
            'filter_model' => $filter_model,
            'filter_price' => $filter_price,
            'filter_quantity' => $filter_quantity,
            'filter_status' => $filter_status,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $this->load->model('tool/image');
        $customer_id = $this->customer->getId();
        $editor_id = $this->customer->getEditorId();
        $product_total = $this->model_catalog_productbooks->getTotalProductsEditor($filter_data, $customer_id, $editor_id);

        $results = $this->model_payment_paymnet_approval->getPayments($filter_data, $customer_id);
        foreach ($results as $result) {
            $data['payments'][] = array(
                'payment_id' => $result['payment_id'],
                'image' => $result['image'],
                'invoice' => $result['invoice'],
                'status' => $result['status']
            );
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_no_results'] = $this->language->get('text_no_results');

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
        $data['column_total_selling'] = $this->language->get('column_total_selling');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_model'] = $this->language->get('entry_model');
        $data['entry_price'] = $this->language->get('entry_price');
        $data['entry_quantity'] = $this->language->get('entry_quantity');
        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_copy'] = $this->language->get('button_copy');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_download'] = $this->language->get('button_download');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_filter'] = $this->language->get('button_filter');



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

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('common/mybooks/getEditingList', '&sort=pd.name' . $url, 'SSL');
        $data['sort_model'] = $this->url->link('common/mybooks/getEditingList', '&sort=p.model' . $url, 'SSL');
        $data['sort_price'] = $this->url->link('common/mybooks/getEditingList', '&sort=p.price' . $url, 'SSL');
        $data['sort_quantity'] = $this->url->link('common/mybooks/getEditingList', '&sort=p.quantity' . $url, 'SSL');
        $data['sort_status'] = $this->url->link('common/mybooks/getEditingList', '&sort=p.status' . $url, 'SSL');
        $data['sort_order'] = $this->url->link('common/mybooks/getEditingList', '&sort=p.sort_order' . $url, 'SSL');

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

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('common/mybooks', ' ' . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

        $data['filter_name'] = $filter_name;
        $data['filter_model'] = $filter_model;
        $data['filter_price'] = $filter_price;
        $data['filter_quantity'] = $filter_quantity;
        $data['filter_status'] = $filter_status;

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('default/template/common/mybooks.tpl', $data));
    }

    public function getListBooks() {
        $this->load->model('catalog/product');
        $editor_id = $this->customer->getEditorId();
        $query = $this->model_catalog_product->updateEditor($editor_id, $this->request->get['product_id']);
        //$this->response->redirect($this->url->link('common/mybooks', ' ', 'SSL'));
        if ($query) {
            $this->response->redirect($this->url->link('common/mybooks/getEditingList', '', 'SSL'));
        }

        //$this->response->setOutput($this->load->view('default/template/common/mybooks/get.tpl', $data));
    }

    protected function getForm() {
        $this->load->language('payment/payment_approval');
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_agree'] = $this->language->get('text_agree');
        $data['text_disagree'] = $this->language->get('text_disagree');
        $data['text_none'] = $this->language->get('text_none');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_plus'] = $this->language->get('text_plus');
        $data['text_minus'] = $this->language->get('text_minus');
        $data['text_default'] = $this->language->get('text_default');
        $data['text_option'] = $this->language->get('text_option');
        $data['text_option_value'] = $this->language->get('text_option_value');
        $data['text_select'] = $this->language->get('text_select');
        $data['text_percent'] = $this->language->get('text_percent');
        $data['text_amount'] = $this->language->get('text_amount');
        $data['text_image'] = $this->language->get('text_image');
        $data['text_invoice'] = $this->language->get('text_invoice');

        $data['entry_invoice'] = $this->language->get('entry_invoice');
        $data['entry_image'] = $this->language->get('entry_image');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_attribute_add'] = $this->language->get('button_attribute_add');
        $data['button_option_add'] = $this->language->get('button_option_add');
        $data['button_option_value_add'] = $this->language->get('button_option_value_add');
        $data['button_discount_add'] = $this->language->get('button_discount_add');
        $data['button_special_add'] = $this->language->get('button_special_add');
        $data['button_image_add'] = $this->language->get('button_image_add');
        $data['button_remove'] = $this->language->get('button_remove');
        $data['button_recurring_add'] = $this->language->get('button_recurring_add');
        $data['button_upload'] = $this->language->get('button_upload');

        $data['tab_general'] = $this->language->get('tab_general');
        $data['tab_data'] = $this->language->get('tab_data');
        $data['tab_attribute'] = $this->language->get('tab_attribute');
        $data['tab_option'] = $this->language->get('tab_option');
        $data['tab_recurring'] = $this->language->get('tab_recurring');
        $data['tab_discount'] = $this->language->get('tab_discount');
        $data['tab_special'] = $this->language->get('tab_special');
        $data['tab_image'] = $this->language->get('tab_image');
        $data['tab_links'] = $this->language->get('tab_links');
        $data['tab_reward'] = $this->language->get('tab_reward');
        $data['tab_design'] = $this->language->get('tab_design');
        $data['tab_openbay'] = $this->language->get('tab_openbay');



        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['image'])) {
            $data['error_image'] = $this->error['image'];
        } else {
            $data['error_image'] = '';
        }

        if (isset($this->error['image'])) {
            $data['error_extension_image'] = $this->error['image'];
        } else {
            $data['error_extension_image'] = '';
        }

        if (isset($this->error['invoice'])) {
            $data['invoice'] = $this->error['invoice'];
        } else {
            $data['invoice'] = array();
        }

        if (isset($this->error['date'])) {
            $data['date'] = $this->error['date'];
        } else {
            $data['date'] = '';
        }

        if (isset($this->error['keyword'])) {
            $data['error_keyword'] = $this->error['keyword'];
        } else {
            $data['error_keyword'] = '';
        }


        $url = '';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('common/payment_approval/add', ' ', 'SSL')
        );

        if (!isset($this->request->get['payment_id'])) {
            $data['get_payment_id'] = false;
            $data['action'] = $this->url->link('common/payment_approval/add', ' ', 'SSL');
            $data['group_id'] = 0;
        } else {
            $data['get_payment_id'] = true;
            $data['group_id'] = $this->customer->getGroupId();
            $data['action'] = $this->url->link('common/payment_approval/edit', '&product_id=' . $this->request->get['product_id'] . $url, 'SSL');
        }

        $data['cancel'] = $this->url->link('common/payment_approval/add', ' ', 'SSL');

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($product_info) && is_file(DIR_IMAGE . $product_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

//		if (isset($this->request->post['model'])) {
//			$data['model'] = $this->request->post['model'];
//		} elseif (!empty($product_info)) {
//			$data['model'] = $product_info['model'];
//		} else {
//			$data['model'] = 'buku';
//		}
//
//		if (isset($this->request->files['book'])) {
//			$filebook = basename(html_entity_decode($this->request->files['book']['name'], ENT_QUOTES, 'UTF-8'));
//			$file = "file/".$filebook . '.' . md5(mt_rand());
//			move_uploaded_file($this->request->files['book']['tmp_name'], DIR_BOOK. $file);
//			$data['book']=$file;
//		} else {
//			$data['book'] = '';
//		}
//
//		if (isset($this->request->post['author'])) {
//			$data['author'] = $this->request->post['author'];
//		} elseif (!empty($product_info)) {
//			$data['author'] = $product_info['author'];
//		} else {
//			$data['author'] = '';
//		}
//
//		if (isset($this->request->post['color_page'])) {
//			$data['color_page'] = $this->request->post['color_page'];
//		} elseif (!empty($product_info)) {
//			$data['color_page'] = $product_info['color_page'];
//		} else {
//			$data['color_page'] = '';
//		}
//
//		if (isset($this->request->post['bw_page'])) {
//			$data['bw_page'] = $this->request->post['bw_page'];
//		} elseif (!empty($product_info)) {
//			$data['bw_page'] = $product_info['bw_page'];
//		} else {
//			$data['bw_page'] = '';
//		}
//
//		if (isset($this->request->post['sku'])) {
//			$data['sku'] = $this->request->post['sku'];
//		} elseif (!empty($product_info)) {
//			$data['sku'] = $product_info['sku'];
//		} else {
//			$data['sku'] = '';
//		}
//
//		if (isset($this->request->post['upc'])) {
//			$data['upc'] = $this->request->post['upc'];
//		} elseif (!empty($product_info)) {
//			$data['upc'] = $product_info['upc'];
//		} else {
//			$data['upc'] = '';
//		}
//
//		if (isset($this->request->post['ean'])) {
//			$data['ean'] = $this->request->post['ean'];
//		} elseif (!empty($product_info)) {
//			$data['ean'] = $product_info['ean'];
//		} else {
//			$data['ean'] = '';
//		}
//
//		if (isset($this->request->post['jan'])) {
//			$data['jan'] = $this->request->post['jan'];
//		} elseif (!empty($product_info)) {
//			$data['jan'] = $product_info['jan'];
//		} else {
//			$data['jan'] = '';
//		}
//
//		if (isset($this->request->post['isbn'])) {
//			$data['isbn'] = $this->request->post['isbn'];
//		} elseif (!empty($product_info)) {
//			$data['isbn'] = $product_info['isbn'];
//		} else {
//			$data['isbn'] = '';
//		}
//
//		if (isset($this->request->post['mpn'])) {
//			$data['mpn'] = $this->request->post['mpn'];
//		} elseif (!empty($product_info)) {
//			$data['mpn'] = $product_info['mpn'];
//		} else {
//			$data['mpn'] = '';
//		}
//
//		if (isset($this->request->post['location'])) {
//			$data['location'] = $this->request->post['location'];
//		} elseif (!empty($product_info)) {
//			$data['location'] = $product_info['location'];
//		} else {
//			$data['location'] = '';
//		}
//
//		$this->load->model('setting/store');
//
//		$data['stores'] = $this->model_setting_store->getStores();
//
//		if (isset($this->request->post['product_store'])) {
//			$data['product_store'] = $this->request->post['product_store'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$data['product_store'] = $this->model_catalog_product->getProductStores($this->request->get['product_id']);
//		} else {
//			$data['product_store'] = 0;
//		}
//
//		if (isset($this->request->post['keyword'])) {
//			$data['keyword'] = $this->request->post['keyword'];
//		} elseif (!empty($product_info)) {
//			$data['keyword'] = $product_info['keyword'];
//		} else {
//			$data['keyword'] = '';
//		}
//
//		if (isset($this->request->post['shipping'])) {
//			$data['shipping'] = $this->request->post['shipping'];
//		} elseif (!empty($product_info)) {
//			$data['shipping'] = $product_info['shipping'];
//		} else {
//			$data['shipping'] = 1;
//		}
//
//		if (isset($this->request->post['price'])) {
//			$data['price'] = $this->request->post['price'];
//		} elseif (!empty($product_info)) {
//			$data['price'] = $product_info['price'];
//		} else {
//			$data['price'] = '';
//		}
//
//		$this->load->model('catalog/recurring');
//
//		$data['recurrings'] = $this->model_catalog_recurring->getRecurrings();
//
//		if (isset($this->request->post['product_recurrings'])) {
//			$data['product_recurrings'] = $this->request->post['product_recurrings'];
//		} elseif (!empty($product_info)) {
//			$data['product_recurrings'] = $this->model_catalog_product->getRecurrings($product_info['product_id']);
//		} else {
//			$data['product_recurrings'] = array();
//		}
//
//		$this->load->model('localisation/tax_class');
//
//		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
//
//		if (isset($this->request->post['tax_class_id'])) {
//			$data['tax_class_id'] = $this->request->post['tax_class_id'];
//		} elseif (!empty($product_info)) {
//			$data['tax_class_id'] = $product_info['tax_class_id'];
//		} else {
//			$data['tax_class_id'] = 0;
//		}
//
//		if (isset($this->request->post['date_available'])) {
//			$data['date_available'] = $this->request->post['date_available'];
//		} elseif (!empty($product_info)) {
//			$data['date_available'] = ($product_info['date_available'] != '0000-00-00') ? $product_info['date_available'] : '';
//		} else {
//			$data['date_available'] = date('Y-m-d');
//		}
//
//		if (isset($this->request->post['quantity'])) {
//			$data['quantity'] = $this->request->post['quantity'];
//		} elseif (!empty($product_info)) {
//			$data['quantity'] = $product_info['quantity'];
//		} else {
//			$data['quantity'] = 1;
//		}
//
//		if (isset($this->request->post['minimum'])) {
//			$data['minimum'] = $this->request->post['minimum'];
//		} elseif (!empty($product_info)) {
//			$data['minimum'] = $product_info['minimum'];
//		} else {
//			$data['minimum'] = 1;
//		}
//
//		if (isset($this->request->post['subtract'])) {
//			$data['subtract'] = $this->request->post['subtract'];
//		} elseif (!empty($product_info)) {
//			$data['subtract'] = $product_info['subtract'];
//		} else {
//			$data['subtract'] = 1;
//		}
//
//		if (isset($this->request->post['sort_order'])) {
//			$data['sort_order'] = $this->request->post['sort_order'];
//		} elseif (!empty($product_info)) {
//			$data['sort_order'] = $product_info['sort_order'];
//		} else {
//			$data['sort_order'] = 1;
//		}
//
//		$this->load->model('localisation/stock_status');
//
//		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
//
//		if (isset($this->request->post['stock_status_id'])) {
//			$data['stock_status_id'] = $this->request->post['stock_status_id'];
//		} elseif (!empty($product_info)) {
//			$data['stock_status_id'] = $product_info['stock_status_id'];
//		} else {
//			$data['stock_status_id'] = 0;
//		}

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($product_info)) {
            $data['status'] = $product_info['status'];
        } else {
            $data['status'] = false;
        }

//		if (isset($this->request->post['weight'])) {
//			$data['weight'] = $this->request->post['weight'];
//		} elseif (!empty($product_info)) {
//			$data['weight'] = $product_info['weight'];
//		} else {
//			$data['weight'] = '';
//		}
//
//		$this->load->model('localisation/weight_class');
//
//		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
//
//		if (isset($this->request->post['weight_class_id'])) {
//			$data['weight_class_id'] = $this->request->post['weight_class_id'];
//		} elseif (!empty($product_info)) {
//			$data['weight_class_id'] = $product_info['weight_class_id'];
//		} else {
//			$data['weight_class_id'] = $this->config->get('config_weight_class_id');
//		}
//
//		if (isset($this->request->post['length'])) {
//			$data['length'] = $this->request->post['length'];
//		} elseif (!empty($product_info)) {
//			$data['length'] = $product_info['length'];
//		} else {
//			$data['length'] = '';
//		}
//
//		if (isset($this->request->post['width'])) {
//			$data['width'] = $this->request->post['width'];
//		} elseif (!empty($product_info)) {
//			$data['width'] = $product_info['width'];
//		} else {
//			$data['width'] = '';
//		}
//
//		if (isset($this->request->post['height'])) {
//			$data['height'] = $this->request->post['height'];
//		} elseif (!empty($product_info)) {
//			$data['height'] = $product_info['height'];
//		} else {
//			$data['height'] = '';
//		}
//
//		$this->load->model('localisation/length_class');
//
//		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();
//
//		if (isset($this->request->post['length_class_id'])) {
//			$data['length_class_id'] = $this->request->post['length_class_id'];
//		} elseif (!empty($product_info)) {
//			$data['length_class_id'] = $product_info['length_class_id'];
//		} else {
//			$data['length_class_id'] = $this->config->get('config_length_class_id');
//		}
//
//		$this->load->model('catalog/manufacturer');
//
//		if (isset($this->request->post['manufacturer_id'])) {
//			$data['manufacturer_id'] = $this->request->post['manufacturer_id'];
//		} elseif (!empty($product_info)) {
//			$data['manufacturer_id'] = $product_info['manufacturer_id'];
//		} else {
//			$data['manufacturer_id'] = 0;
//		}
//
//		if (isset($this->request->post['manufacturer'])) {
//			$data['manufacturer'] = $this->request->post['manufacturer'];
//		} elseif (!empty($product_info)) {
//			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);
//
//			if ($manufacturer_info) {
//				$data['manufacturer'] = $manufacturer_info['name'];
//			} else {
//				$data['manufacturer'] = '';
//			}
//		} else {
//			$data['manufacturer'] = '';
//		}
//
//		// Categories
//		$this->load->model('catalog/category');
//
//		if (isset($this->request->post['product_category'])) {
//			$categories = $this->request->post['product_category'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$categories = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
//		} else {
//			$categories = array();
//		}
//
//		$data['product_categories'] = array();
//
//		foreach ($categories as $category_id) {
//			$category_info = $this->model_catalog_category->getCategory($category_id);
//
//			if ($category_info) {
//				$data['product_categories'][] = array(
//					'category_id' => $category_info['category_id'],
//					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
//				);
//			}
//		}
//
//		// Filters
//		$this->load->model('catalog/filter');
//
//		if (isset($this->request->post['product_filter'])) {
//			$filters = $this->request->post['product_filter'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$filters = $this->model_catalog_product->getProductFilters($this->request->get['product_id']);
//		} else {
//			$filters = array();
//		}
//
//		$data['product_filters'] = array();
//
//		foreach ($filters as $filter_id) {
//			$filter_info = $this->model_catalog_filter->getFilter($filter_id);
//
//			if ($filter_info) {
//				$data['product_filters'][] = array(
//					'filter_id' => $filter_info['filter_id'],
//					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
//				);
//			}
//		}
//
//		// Attributes
//		$this->load->model('catalog/attribute');
//
//		if (isset($this->request->post['product_attribute'])) {
//			$product_attributes = $this->request->post['product_attribute'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$product_attributes = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
//		} else {
//			$product_attributes = array();
//		}
//
//		$data['product_attributes'] = array();
//
//		foreach ($product_attributes as $product_attribute) {
//			$attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);
//
//			if ($attribute_info) {
//				$data['product_attributes'][] = array(
//					'attribute_id'                  => $product_attribute['attribute_id'],
//					'name'                          => $attribute_info['name'],
//					'product_attribute_description' => $product_attribute['product_attribute_description']
//				);
//			}
//		}
//
//		// Options
//		$this->load->model('catalog/option');
//
//		if (isset($this->request->post['product_option'])) {
//			$product_options = $this->request->post['product_option'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);
//		} else {
//			$product_options = array();
//		}
//
//		$data['product_options'] = array();
//
//		foreach ($product_options as $product_option) {
//			$product_option_value_data = array();
//
//			if (isset($product_option['product_option_value'])) {
//				foreach ($product_option['product_option_value'] as $product_option_value) {
//					$product_option_value_data[] = array(
//						'product_option_value_id' => $product_option_value['product_option_value_id'],
//						'option_value_id'         => $product_option_value['option_value_id'],
//						'quantity'                => $product_option_value['quantity'],
//						'subtract'                => $product_option_value['subtract'],
//						'price'                   => $product_option_value['price'],
//						'price_prefix'            => $product_option_value['price_prefix'],
//						'points'                  => $product_option_value['points'],
//						'points_prefix'           => $product_option_value['points_prefix'],
//						'weight'                  => $product_option_value['weight'],
//						'weight_prefix'           => $product_option_value['weight_prefix']
//					);
//				}
//			}
//
//			$data['product_options'][] = array(
//				'product_option_id'    => $product_option['product_option_id'],
//				'product_option_value' => $product_option_value_data,
//				'option_id'            => $product_option['option_id'],
//				'name'                 => $product_option['name'],
//				'type'                 => $product_option['type'],
//				'value'                => isset($product_option['value']) ? $product_option['value'] : '',
//				'required'             => $product_option['required']
//			);
//		}
//
//		$data['option_values'] = array();
//
//		foreach ($data['product_options'] as $product_option) {
//			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
//				if (!isset($data['option_values'][$product_option['option_id']])) {
//					$data['option_values'][$product_option['option_id']] = $this->model_catalog_option->getOptionValues($product_option['option_id']);
//				}
//			}
//		}
//
//		$this->load->model('sale/customer_group');
//
//		$data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
//
//		if (isset($this->request->post['product_discount'])) {
//			$product_discounts = $this->request->post['product_discount'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$product_discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);
//		} else {
//			$product_discounts = array();
//		}
//
//		$data['product_discounts'] = array();
//
//		foreach ($product_discounts as $product_discount) {
//			$data['product_discounts'][] = array(
//				'customer_group_id' => $product_discount['customer_group_id'],
//				'quantity'          => $product_discount['quantity'],
//				'priority'          => $product_discount['priority'],
//				'price'             => $product_discount['price'],
//				'date_start'        => ($product_discount['date_start'] != '0000-00-00') ? $product_discount['date_start'] : '',
//				'date_end'          => ($product_discount['date_end'] != '0000-00-00') ? $product_discount['date_end'] : ''
//			);
//		}
//
//		if (isset($this->request->post['product_special'])) {
//			$product_specials = $this->request->post['product_special'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$product_specials = $this->model_catalog_product->getProductSpecials_edit($this->request->get['product_id']);
//		} else {
//			$product_specials = array();
//		}
//
//		$data['product_specials'] = array();
//
//		foreach ($product_specials as $product_special) {
//			$data['product_specials'][] = array(
//				'customer_group_id' => $product_special['customer_group_id'],
//				'priority'          => $product_special['priority'],
//				'price'             => $product_special['price'],
//				'date_start'        => ($product_special['date_start'] != '0000-00-00') ? $product_special['date_start'] : '',
//				'date_end'          => ($product_special['date_end'] != '0000-00-00') ? $product_special['date_end'] :  ''
//			);
//		}
//
//		// Images
//		if (isset($this->request->post['product_image'])) {
//			$product_images = $this->request->post['product_image'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
//		} else {
//			$product_images = array();
//		}
//
//		$data['product_images'] = array();
//
//		foreach ($product_images as $product_image) {
//			if (is_file(DIR_IMAGE . $product_image['image'])) {
//				$image = $product_image['image'];
//				$thumb = $product_image['image'];
//			} else {
//				$image = '';
//				$thumb = 'no_image.png';
//			}
//
//			$data['product_images'][] = array(
//				'image'      => $image,
//				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
//				'sort_order' => $product_image['sort_order']
//			);
//		}
        // Downloads
//		$this->load->model('catalog/download');
//
//		if (isset($this->request->post['product_download'])) {
//			$product_downloads = $this->request->post['product_download'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$product_downloads = $this->model_catalog_product->getProductDownloads($this->request->get['product_id']);
//		} else {
//			$product_downloads = array();
//		}
//
//		$data['product_downloads'] = array();
//
//		foreach ($product_downloads as $download_id) {
//			$download_info = $this->model_catalog_download->getDownload($download_id);
//
//			if ($download_info) {
//				$data['product_downloads'][] = array(
//					'download_id' => $download_info['download_id'],
//					'name'        => $download_info['name']
//				);
//			}
//		}
//
//		if (isset($this->request->post['product_related'])) {
//			$products = $this->request->post['product_related'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
//		} else {
//			$products = array();
//		}
//
//		$data['product_relateds'] = array();
//
//		foreach ($products as $product_id) {
//			$related_info = $this->model_catalog_product->getProduct($product_id);
//
//			if ($related_info) {
//				$data['product_relateds'][] = array(
//					'product_id' => $related_info['product_id'],
//					'name'       => $related_info['name']
//				);
//			}
//		}
//
//		if (isset($this->request->post['points'])) {
//			$data['points'] = $this->request->post['points'];
//		} elseif (!empty($product_info)) {
//			$data['points'] = $product_info['points'];
//		} else {
//			$data['points'] = '';
//		}
//
//		if (isset($this->request->post['product_reward'])) {
//			$data['product_reward'] = $this->request->post['product_reward'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$data['product_reward'] = $this->model_catalog_product->getProductRewards($this->request->get['product_id']);
//		} else {
//			$data['product_reward'] = array();
//		}
//
//		if (isset($this->request->post['product_layout'])) {
//			$data['product_layout'] = $this->request->post['product_layout'];
//		} elseif (isset($this->request->get['product_id'])) {
//			$data['product_layout'] = $this->model_catalog_product->getProductLayouts($this->request->get['product_id']);
//		} else {
//			$data['product_layout'] = array();
//		}
//
//		if (isset($this->request->post['paper_size'])) {
//			$data['paper_size'] = $this->request->post['paper_size'];
//		} else {
//			$data['paper_size'] = 0;
//		}
//
//		if (isset($this->request->post['paper_type'])) {
//			$data['paper_type'] = $this->request->post['paper_type'];
//		} else {
//			$data['paper_type'] = 0;
//		}
//
//		if ($this->config->get('config_account_id')) {
//			$this->load->model('catalog/information');
//
//			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
//
//			if ($information_info) {
//				$data['text_agreement'] = sprintf($this->language->get('text_agreement'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), 'SSL'), $information_info['title'], $information_info['title']);
//			} else {
//				$data['text_agreement'] = '';
//			}
//		} else {
//			$data['text_agreement'] = '';
//		}
//
//		if (isset($this->request->post['agreement'])) {
//			$data['agreement'] = $this->request->post['agreement'];
//		} else {
//			$data['agreement'] = false;
//		}


        $this->load->model('design/layout');
        $this->load->model('catalog/papersize');

        $data['paper_size'] = $this->model_catalog_papersize->getSize();
        $data['paper_type'] = $this->model_catalog_papersize->getType();
        $data['layouts'] = $this->model_design_layout->getLayouts();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('default/template/payment/form_payment_approval.tpl', $data));
    }

    public function checkValidRequestDrafBook() {
        $dot = '.';
        $allowed_ext_buku = array('doc', 'docx', 'pdf');
        $file_name_buku = $this->request->files['book']['name'];
        $file_name_sample = $this->request->files['sample_script']['name'];
        //$file_ext_buku = strtolower(substr(strrchr($file_name_buku,'.'),1), $file_name_buku);
        $file_size_buku = $this->request->files['book']['size'];

        $lokasi_book = "file/" . date("smhymd") . "_" . $file_name_buku;

        if (in_array(strtolower(substr(strrchr($file_name_buku, '.'), 1)), $allowed_ext_buku) && in_array(strtolower(substr(strrchr($file_name_sample, '.'), 1)), $allowed_ext_buku)) {
            // if (in_array($file_ext_buku, $allowed_ext_buku) === true) {
            if ($file_size_buku < 1044070000) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function checkValidRequestDrafimage() {
        $dot = '.';
        $allowed_ext_image = array('png', 'jpeg', 'jpg');
        $file_name_image = $this->request->files['image']['name'];
        $file_name_cover = $this->request->files['design_cover']['name'];
        //$file_ext_image = strtolower(end(explode($dot, $file_name_image)));
        $file_size_image = $this->request->files['image']['size'];
        $lokasi_cover = "cover/" . date("smhymd") . "_" . $file_name_image;

        if (in_array(strtolower(substr(strrchr($file_name_image, '.'), 1)), $allowed_ext_image) && in_array(strtolower(substr(strrchr($file_name_cover, '.'), 1)), $allowed_ext_image)) {
            //if (in_array($file_ext_image, $allowed_ext_image) === true) {
            if ($file_size_image < 1044070000) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function validateForm() {
        if (empty($this->request->files['image']['name'])) {
            $this->error['image'] = $this->language->get('error_image');
            return $this->error;
        }
        if ($this->request->post['invoice'] == null) {
            $this->error['invoice'] = $this->language->get('error_author');
        }
        return !$this->error;
    }

    public function delete() {
        $this->load->language('payment/payment_approvement');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('payment/payment_approval');

        $this->model_payment_payment_approval->deletePayment($this->request->get['payment_id']);

        $this->session->data['success'] = $this->language->get('text_success');

        $this->response->redirect($this->url->link('common/payment_approval',"", 'SSL'));

        $this->getList();
    }

    protected function validateCopy() {
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function autocomplete() {
        $json = array();

        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
            $this->load->model('catalog/product');
            $this->load->model('catalog/option');

            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['filter_model'])) {
                $filter_model = $this->request->get['filter_model'];
            } else {
                $filter_model = '';
            }

            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = 5;
            }

            $filter_data = array(
                'filter_name' => $filter_name,
                'filter_model' => $filter_model,
                'start' => 0,
                'limit' => $limit
            );

            $results = $this->model_catalog_product->getProducts($filter_data);

            foreach ($results as $result) {
                $option_data = array();

                $product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

                foreach ($product_options as $product_option) {
                    $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                    if ($option_info) {
                        $product_option_value_data = array();

                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

                            if ($option_value_info) {
                                $product_option_value_data[] = array(
                                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                                    'option_value_id' => $product_option_value['option_value_id'],
                                    'name' => $option_value_info['name'],
                                    'price' => (float) $product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
                                    'price_prefix' => $product_option_value['price_prefix']
                                );
                            }
                        }

                        $option_data[] = array(
                            'product_option_id' => $product_option['product_option_id'],
                            'product_option_value' => $product_option_value_data,
                            'option_id' => $product_option['option_id'],
                            'name' => $option_info['name'],
                            'type' => $option_info['type'],
                            'value' => $product_option['value'],
                            'required' => $product_option['required']
                        );
                    }
                }

                $json[] = array(
                    'product_id' => $result['product_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'model' => $result['model'],
                    'option' => $option_data,
                    'price' => $result['price']
                );
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function upload() {
        $this->load->language('catalog/download');

        $json = array();

        // Check user has permission
        /* if (!$this->user->hasPermission('modify', 'catalog/download')) {
          $json['error'] = $this->language->get('error_permission');
          } */

        if (!$json) {
            if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
                // Sanitize the filename
                $filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

                // Validate the filename length
                if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
                    $json['error'] = $this->language->get('error_filename');
                }

                // Allowed file extension types
                $allowed = array();

                $extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

                $filetypes = explode("\n", $extension_allowed);

                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }

                if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Allowed file mime types
                $allowed = array();

                $mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

                $filetypes = explode("\n", $mime_allowed);

                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }

                if (!in_array($this->request->files['file']['type'], $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Check to see if any PHP files are trying to be uploaded
                $content = file_get_contents($this->request->files['file']['tmp_name']);

                if (preg_match('/\<\?php/i', $content)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Return any upload error
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_upload');
            }
        }

        if (!$json) {
            $file = $filename . '.' . md5(mt_rand());

            move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);

            $json['filename'] = $file;
            $json['mask'] = $filename;

            $json['success'] = $this->language->get('text_upload');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function checkValidRequestPaymentApprovement() {
        $dot = '.';
        $allowed_ext_image = array('png', 'jpeg', 'jpg');
        $file_name_image = $this->request->files['image']['name'];
        //$file_ext_image = strtolower(end(explode($dot, $file_name_image)));
        $file_size_image = $this->request->files['image']['size'];
        $lokasi_cover = "cover/" . date("smhymd") . "_" . $file_name_image;

        if (in_array(strtolower(substr(strrchr($file_name_image, '.'), 1)), $allowed_ext_image)) {
            //if (in_array($file_ext_image, $allowed_ext_image) === true) {
            if ($file_size_image < 1044070000) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

}