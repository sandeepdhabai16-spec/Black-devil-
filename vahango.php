<?php

class RCAPI {
    private $AES_KEY = "RTO@N@1V@\$U2024#";
    private $CUSTOM_RESPONSE_MESSAGE = "Fetched [ SENPAI ]";
    
    public function __construct() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
    }
    
    private function encrypt($plaintext, $key) {
        $key = substr($key, 0, 16);
        $ciphertext = openssl_encrypt($plaintext, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        return base64_encode($ciphertext);
    }
    
    private function decrypt($ciphertextBase64, $key) {
        try {
            $key = substr($key, 0, 16);
            $ciphertext = base64_decode($ciphertextBase64);
            $decrypted = openssl_decrypt($ciphertext, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
            return $decrypted;
        } catch (Exception $e) {
            error_log("Decryption error: " . $e->getMessage());
            return null;
        }
    }
    
    private function getUnmaskedData($rcNumber) {
        $url = "http://147.93.27.177:3000/rc?search=" . urlencode($rcNumber);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FAILONERROR => true
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Unmasked API error: " . $error);
            return null;
        }
        
        $data = json_decode($response, true);
        if (isset($data['code']) && $data['code'] === "SUCCESS" && isset($data['data'])) {
            return [
                'owner_name' => $data['data']['registration_details']['owner_name'] ?? null,
                'father_name' => $data['data']['ownership_details']['father_name'] ?? null,
                'vehicle_age' => $data['data']['important_dates']['vehicle_age'] ?? null
            ];
        }
        
        return null;
    }
    
    private function mergeRcData($originalData, $unmaskedData) {
        if (!isset($originalData['data']) || !is_array($originalData['data']) || empty($originalData['data'])) {
            return $originalData;
        }
        
        $mergedData = $originalData;
        $rcItem = $mergedData['data'][0];
        
        if ($unmaskedData) {
            if (!empty($unmaskedData['owner_name']) && isset($rcItem['owner_name']) && strpos($rcItem['owner_name'], '*') !== false) {
                $rcItem['owner_name'] = $unmaskedData['owner_name'];
            }
            
            if (!empty($unmaskedData['father_name']) && isset($rcItem['father_name']) && strpos($rcItem['father_name'], '*') !== false) {
                $rcItem['father_name'] = $unmaskedData['father_name'];
            }
            
            if (!empty($unmaskedData['vehicle_age'])) {
                $rcItem['vehicle_age'] = $unmaskedData['vehicle_age'];
            }
        }
        
        $mergedData['response_message'] = $this->CUSTOM_RESPONSE_MESSAGE;
        $mergedData['data'] = [$rcItem];
        
        return $mergedData;
    }
    
    private function decryptApiResponse($encryptedResponse) {
        if (is_string($encryptedResponse)) {
            $decrypted = $this->decrypt($encryptedResponse, $this->AES_KEY);
            if ($decrypted) {
                $json = json_decode($decrypted, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json;
                }
                return $decrypted;
            }
        }
        return $encryptedResponse;
    }
    
    public function getRCData($rcNumber) {
        if (empty($rcNumber)) {
            return [
                'status' => false,
                'message' => "RC number is required"
            ];
        }
        
        try {
            // Get unmasked data
            $unmaskedData = $this->getUnmaskedData($rcNumber);
            
            // ***** VAHANGO API *****
            
            // Common headers for Vahango API
            $headers = [
                'User-Agent: okhttp/4.12.0',
                'Connection: Keep-Alive',
                'Accept-Encoding: gzip',
                'number: ',
                'device_id: 4bc18d2dc62141b0',
                'fcm_token: fQNwb97qSv66takVr-PGkt:APA91bEDQPX8atVx0IbmDyLsMSk1okIkAa_95JAfR2hYMoKpF8OpkwhZxI8kjJM5zs0LQmdNBRM_XNYMIm2B1_LtuRWQqmnBbDEZPsq7ufz8ged6uv1EyxI',
                'player_id: 17e0256c-67da-4c1c-a8fb-0aee8d95b3d3',
                'language_code: en',
                'device_model: RMX3081',
                'device_manufacture: realme',
                'device_os_version: 13'
            ];
            
            // POST data for Vahango API
            $postData = [
                'reg_number' => $rcNumber,
                'param' => 'RC',
                'data' => '',
                'otp' => '',
                'skip_db' => 'false',
                'en5' => '',
                'ch5' => ''
            ];
            
            // Call Vahango API
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://rc.vahango.app/api/get_rc_details",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $vahangoResponse = curl_exec($ch);
            curl_close($ch);
            
            // Decode Vahango response
            $vahangoData = json_decode($vahangoResponse, true);
            
            // ***** MAIN API *****
            
            // Encrypt RC for main API
            $encryptedRc = $this->encrypt($rcNumber, $this->AES_KEY);
            
            // Prepare form data
            $formData = [
                'YLnoBJXFHWIb6n+vaU5Fqw===' => 'hEetH/fxDYkaiPV1O08JXGavuWKAHB7H//KqlbPQizq1sxbHamO8edqhIcOJJybWVc4wf11tUxC1uEtwt2OHiKuzQ4fSmex9pkrf6bj/yztMQT9yb5+E3V3RttX0S1WRXRiNakRvo+pOiu6k8j8M+C6aLHvrWxqTQnP9ND0xv3EQyxcgjYt5rk2qVOWP+nf8',
                'uniDRnuJvTpCyd8qqa7bmg===' => '6UcabyegT3XEmP2Mw0Jwfw==',
                'wmbVbuTELPkity3gk1FSLw===' => 'hwc6sd9eQz3sd8aZ5tWtOSO9P/8c0ruHIRUDVqC4PzmK3ZgUJ5W/1ibrOgk6+bHhGaWCca3iQ6qfy5v/zhdLXw==',
                'kqvOc7zzeKL9GQi3s97hRg===' => 'KOgloc/Wkh/JKFVr/Y5bZA==',
                '6itFonmUeG7GaEL8YAz1dw===' => 'DHKgKTb0PD667WXK14bQxQ==',
                'gaQw08ye60GZvOaEjDxwSg===' => '7Xx2UpV+mliqWirrrkrJ4A==',
                'KldjgNJiCoLPelKQK12wCg===' => 'Wg4luew+ZNYaVLvuYevUwhJMt5Q0FwINOnT3ntNuXiM=',
                '8qv0XiLt71c2Mcb7A/0ETw===' => '2femjV0XNiZlRIoza3rq/Q==',
                'zKMffadDKn74L6D8Erq/Ow===' => 'HjCiWD0aGnOHqRk+sJhmSg==',
                'aQ1IgwRQsEsftk0pG3qVOA===' => 'NDEpmB1IH3r0ZWPKlDX42g==',
                'kxBCVJqsDl1CnYYrPI+ESg===' => '6UcabyegT3XEmP2Mw0Jwfw==',
                '4svShi1T5ftaZPNNHhJzig===' => $encryptedRc,
                'lES0BMK4Gbc62W3W5/cR3Q===' => '6UcabyegT3XEmP2Mw0Jwfw==',
                '5ES5V9fBsVv2zixvup+QfGUYTXf6w2Wb7rfo1vbyiZo=' => '6UcabyegT3XEmP2Mw0Jwfw==',
                'w0dcvRNvk81864M2TM1R4w===' => '4n04akOAWVJ7qY7ccwxckA==',
                'Qh35ea+zP5C5YndUy+/5hQ===' => 'Eky3lDQXAg06dPee025eIw==',
                'zdR9T9RDHgdRB7xdozvLRNUdr4dDNKvva1aeDyqC22ASTLeUNBcCDTp0957Tbl4j=' => 'zeLxdIWt2S3VdsxhpTwY1A==',
                'eMY6P1CkF0Iya2o8nxqYGpW47fJY0qkIn/5knbV9Kos=' => 'zeLxdIWt2S3VdsxhpTwY1A=='
            ];
            
            // Call main API
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://rcdetailsapi.vehicleinfo.app/api/vasu_rc_doc_details",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($formData),
                CURLOPT_HTTPHEADER => [
                    'User-Agent: okhttp/5.0.0-alpha.11',
                    'Accept-Encoding: gzip',
                    'Content-Type: application/x-www-form-urlencoded',
                    'authorization: ',
                    'version_code: 13.39',
                    'device_type: android'
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Process response
            $rc_xhudai = $this->decryptApiResponse($response);
            
            if (is_array($rc_xhudai)) {
                $rc_xhudai = $this->mergeRcData($rc_xhudai, $unmaskedData);
            }
            
            // Prepare final response - CHALLAN INFO HATAYA
            return [
                'query' => $rcNumber,
                'rc_chudai' => $rc_xhudai,
                'vahango_data' => $vahangoData
            ];
            
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            return [
                'status' => false,
                'message' => "Failed to fetch RC details",
                'error' => $e->getMessage()
            ];
        }
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    // Command line mode
    echo "🚗 RC API Server\n";
    echo "📝 Response message: Fetched [ SENPAI ]\n";
    echo "🔐 Using AES encryption\n";
    echo "🆕 Vahango API: ADDED\n\n";
    
    // You can test with: php rc_api.php RJ35GA0955
    if (isset($argv[1])) {
        $api = new RCAPI();
        $result = $api->getRCData($argv[1]);
        print_r($result);
    } else {
        echo "Usage: php rc_api.php RC_NUMBER\n";
        echo "Example: php rc_api.php RJ35GA0955\n";
    }
} else {
    // Web server mode
    $api = new RCAPI();
    
    if (isset($_GET['query'])) {
        $rc = $_GET['query'];
        $result = $api->getRCData($rc);
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'status' => false,
            'message' => "Missing query parameter",
            'usage' => "/rc_api.php?query=RC_NUMBER",
            'example' => "/rc_api.php?query=RJ35GA0955"
        ], JSON_PRETTY_PRINT);
    }
}

?>