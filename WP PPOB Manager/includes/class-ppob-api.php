<?php
class WPPPOB_API {
    private $username;
    private $api_key;
    private $base_url = 'https://api.digiflazz.com/v1/';
    
    public function __construct() {
        $this->username = get_option('wppob_api_username');
        $this->api_key = get_option('wppob_api_key');
    }
    
    private function generate_sign($body) {
        return md5($this->username . $this->api_key . $body);
    }
    
    public function get_price_list($type = 'prepaid') {
        $endpoint = 'price-list';
        $body = json_encode([
            'cmd' => $type,
            'username' => $this->username,
            'sign' => $this->generate_sign('pricelist')
        ]);
        
        $response = wp_remote_post($this->base_url . $endpoint, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $body,
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        return json_decode(wp_remote_retrieve_body($response), true);
    }
    
    public function topup($sku, $customer_no, $ref_id) {
        $endpoint = 'transaction';
        $body = json_encode([
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customer_no,
            'ref_id' => $ref_id,
            'sign' => $this->generate_sign($this->username . $this->api_key . $ref_id),
            'commands' => 'topup'
        ]);
        
        $response = wp_remote_post($this->base_url . $endpoint, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $body,
            'timeout'  => 45,
        ]);
        
        return json_decode(wp_remote_retrieve_body($response), true);
    }
}