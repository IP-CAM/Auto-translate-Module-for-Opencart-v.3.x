<?php
class ControllerExtensionModuleAutoTranslate extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/auto_translate');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            //return print_r($this->request->post);
            $data_to_save = array(
                'auto_translate_array_languages' => json_encode($this->request->post['array_languages']),
                'auto_translate_status' => $this->request->post['status']
            );

            $this->model_setting_setting->editSetting('auto_translate', $data_to_save);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['array_languages'])) {
            $data['error_array_languages'] = $this->error['array_languages'];
        } else {
            $data['error_array_languages'] = '';
        }

        $data['action'] = $this->url->link('extension/module/auto_translate', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['user_token'] = $this->session->data['user_token'];


        if (isset($this->request->post['array_languages'])) {
            $data['languages'] = $this->request->post['array_languages'];
        } else {
            $data['languages'] = json_decode($this->config->get('auto_translate_array_languages'));
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } else {
            $data['status'] = $this->config->get('auto_translate_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_translate', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/auto_translate')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['array_languages'])) {
            $this->error['array_languages'] = $this->language->get('error_array_languages');
        }

        return !$this->error;
    }

    public function getLanguage() {

        $json = array();

        $url = "https://www.translated.net/hts/?f=ll&cid=htsdemo&p=htsdemo5&of=json";

        $response = file_get_contents($url);

        if ($response !== false) {
            $data = json_decode($response, true);

            if ($data['code'] === 1) {
                for ($i = 0; $i < $data['count']; $i++) {
                    $json[] = array(
                        'rfc3066' => $data[$i]['rfc3066'],
                        'iso6391' => $data[$i]['iso6391'],
                        'name' => $data[$i]['name']
                    );
                }
            } else {
                $json[] = array(
                    'error' => 'Ошибка: ' . $data['message']
                );
            }
        } else {
            $json[] = array(
                'error' => 'Ошибка при выполнении запроса.'
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
