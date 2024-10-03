<?php
class ControllerCommonAutoTranslate extends Controller {

    public function makeTranslation()
    {
        if (isset($this->request->post['products']) && isset($this->request->post['lang'])) {
            $selected_lang = $this->request->post['lang'];
            $products = $this->request->post['products'];

            $translations = [];

            foreach ($products as $product) {
                $title = $product['title'];
                $desc = $product['description'];

                $title_query = urlencode($title);
                $desc_query = urlencode($desc);

                if($this->language->get('code') !== $selected_lang){
                    $title_url = "https://api.mymemory.translated.net/get?q={$title_query}&langpair={$this->language->get('code')}|{$selected_lang}";
                    $desc_url = "https://api.mymemory.translated.net/get?q={$desc_query}&langpair={$this->language->get('code')}|{$selected_lang}";
                    $this->session->data['language'] = $selected_lang;
                    $this->load->language('default');
                } else {
                    $title_url = "https://api.mymemory.translated.net/get?q={$title_query}&langpair={$this->session->data['language']}|{$selected_lang}";
                    $desc_url = "https://api.mymemory.translated.net/get?q={$desc_query}&langpair={$this->session->data['language']}|{$selected_lang}";
                }

                $ch_title = curl_init();
                curl_setopt($ch_title, CURLOPT_URL, $title_url);
                curl_setopt($ch_title, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_title, CURLOPT_FOLLOWLOCATION, true);
                $title_response = curl_exec($ch_title);

                $ch_desc = curl_init();
                curl_setopt($ch_desc, CURLOPT_URL, $desc_url);
                curl_setopt($ch_desc, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_desc, CURLOPT_FOLLOWLOCATION, true);
                $desc_response = curl_exec($ch_desc);

                if ($title_response === false) {
                    $translations[] = ['error' => 'Curl error for title: ' . curl_error($ch_title)];
                } else {
                    $title_data = json_decode($title_response, true);
                    $translated_title = $title_data['responseData']['translatedText'] ?? 'Translation error for title';
                }

                if ($desc_response === false) {
                    $translations[] = ['error' => 'Curl error for description: ' . curl_error($ch_desc)];
                } else {
                    $desc_data = json_decode($desc_response, true);
                    $translated_desc = $desc_data['responseData']['translatedText'] ?? 'Translation error for description';
                }

                curl_close($ch_title);
                curl_close($ch_desc);

                $translations[] = [
                    'title' => $translated_title,
                    'description' => $translated_desc
                ];
            }

            echo json_encode(['translations' => $translations]);
        } else {
            echo json_encode(['error' => 'Language or products data not set']);
        }
    }
}