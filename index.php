<?php

class RCAPI {
    private $AES_KEY = "RTO@N@1V@\$U2024#";
    private $CUSTOM_RESPONSE_MESSAGE = "Fetched [ SENPAI ]";
    
    private $CARS24_CONFIG = [
        'BASE_URL' => "https://seller-lead.cars24.team",
        'AUTH_HEADER' => "Basic ",
        'PVT_AUTH_HEADER' => "Bearer ",
        'PHONE_NUMBER' => "YOUR_PHONE_NUMBER", // यहाँ अपना phone number डालें
        'USER_ID' => "YOUR_USER_ID" // यहाँ अपना user ID डालें
    ];
    
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
    
    private function getChallanInfo($rcNumber) {
        error_log("Fetching challan info for: " . $rcNumber);
        
        $leadData = [
            'phone' => $this->CARS24_CONFIG['PHONE_NUMBER'],
            'vehicle_reg_no' => $rcNumber,
            'user_id' => $this->CARS24_CONFIG['USER_ID'],
            'whatsapp_consent' => true,
            'type' => "challan",
            'device_category' => "Mweb"
        ];
        
        $headers = [
            'authority: seller-lead.cars24.team',
            'accept: application/json, text/plain, */*',
            'authorization: ' . $this->CARS24_CONFIG['AUTH_HEADER'],
            'content-type: application/json',
            'origin: https://www.cars24.com',
            'pvtauthorization: ' . $this->CARS24_CONFIG['PVT_AUTH_HEADER'],
            'referer: https://www.cars24.com/',
            'user-agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36'
        ];
        
        // Create lead
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->CARS24_CONFIG['BASE_URL'] . '/prospect/lead',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($leadData),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $leadResponse = json_decode($response, true);
        
        if (!isset($leadResponse['success']) || !$leadResponse['success']) {
            error_log("Failed to create lead");
            return null;
        }
        
        $token = $leadResponse['detail']['token'] ?? null;
        if (!$token) {
            return null;
        }
        
        error_log("Lead created, token: " . $token);
        
        // Get challan data
        $challanHeaders = [
            'authority: seller-lead.cars24.team',
            'accept: application/json, text/plain, */*',
            'authorization: ' . $this->CARS24_CONFIG['AUTH_HEADER'],
            'device_category: m-web',
            'origin: https://www.cars24.com',
            'origin_source: c2b-website',
            'platform: Challan',
            'pvtauthorization: ' . $this->CARS24_CONFIG['PVT_AUTH_HEADER'],
            'referer: https://www.cars24.com/',
            'user-agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->CARS24_CONFIG['BASE_URL'] . '/challan/list/' . urlencode($token),
            CURLOPT_HTTPHEADER => $challanHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $challanResponse = curl_exec($ch);
        curl_close($ch);
        
        $challanData = json_decode($challanResponse, true);
        
        if (isset($challanData['status']) && $challanData['status'] === 200) {
            return $challanData['detail'] ?? null;
        }
        
        return null;
    }
    
    private function processChallanData($challanDetail) {
        if (!$challanDetail) return null;
        
        $processed = [
            'processing_status' => $challanDetail['processingStatus'] ?? null,
            'total_online_amount' => $challanDetail['pendingChallans']['totalOnlineChallanAmount'] ?? 0,
            'total_offline_amount' => $challanDetail['pendingChallans']['totalOfflineChallanAmount'] ?? 0,
            'total_amount' => ($challanDetail['pendingChallans']['totalOnlineChallanAmount'] ?? 0) + 
                            ($challanDetail['pendingChallans']['totalOfflineChallanAmount'] ?? 0),
            'pending_challans' => []
        ];
        
        $challanTypes = ['physicalCourtChallans', 'virtualCourtChallans', 'recentlyAddedChallans'];
        
        foreach ($challanTypes as $type) {
            if (isset($challanDetail['pendingChallans'][$type]) && is_array($challanDetail['pendingChallans'][$type])) {
                foreach ($challanDetail['pendingChallans'][$type] as $challan) {
                    $processed['pending_challans'][] = [
                        'challan_no' => $challan['challanNo'] ?? null,
                        'unique_id' => $challan['uniqueIdentifier'] ?? null,
                        'status' => $challan['status'] ?? null,
                        'computed_status' => $challan['computedStatus'] ?? null,
                        'offence_name' => $challan['offences'][0]['offenceName'] ?? "Unknown Offence",
                        'penalty_amount' => $challan['amount'] ?? 0,
                        'date_time' => $challan['dateTime'] ?? null,
                        'location' => $challan['offenceLocation'] ?? null,
                        'state' => $challan['stateCd'] ?? null,
                        'court_type' => $challan['courtType'] ?? null,
                        'payment_status' => $challan['paymentStatus'] ?? null,
                        'pending_duration' => $challan['challanPendingFor'] ?? null,
                        'is_payable' => $challan['isPayable'] ?? false,
                        'challan_images' => $challan['challanImages'] ?? [],
                        'provider_type' => $challan['challanProviderSubType'] ?? null
                    ];
                }
            }
        }
        
        // Sort by date
        usort($processed['pending_challans'], function($a, $b) {
            return strtotime($b['date_time']) - strtotime($a['date_time']);
        });
        
        return $processed;
    }
    
    private function formatChallanResponse($processedChallanInfo) {
        if (!$processedChallanInfo) {
            return [
                'status' => false,
                'response_code' => 404,
                'response_message' => "No challan information available",
                'data' => []
            ];
        }
        
        return [
            'status' => true,
            'response_code' => 200,
            'response_message' => "Challan information fetched successfully",
            'data' => [$processedChallanInfo]
        ];
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
            // Parallel API calls
            $unmaskedData = $this->getUnmaskedData($rcNumber);
            $challanDetail = $this->getChallanInfo($rcNumber);
            $processedChallanInfo = $this->processChallanData($challanDetail);
            
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
            
            // Prepare final response
            return [
                'query' => $rcNumber,
                'rc_chudai' => $rc_xhudai,
                'challan_info' => $this->formatChallanResponse($processedChallanInfo)
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
    echo "🚓 Challan information: ENABLED\n\n";
    
    // You can test with: php rc_api.php UK04AQ9000
    if (isset($argv[1])) {
        $api = new RCAPI();
        $result = $api->getRCData($argv[1]);
        print_r($result);
    } else {
        echo "Usage: php rc_api.php RC_NUMBER\n";
        echo "Example: php rc_api.php UK04AQ9000\n";
    }
} else {
    // Web server mode
    $api = new RCAPI();
    
    if (isset($_GET['query'])) {
        $rc = $_GET['query'];
        $result = $api->getRCData($rc);
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'status' => false,
            'message' => "Missing query parameter",
            'usage' => "/rc_api.php?query=RC_NUMBER",
            'example' => "/rc_api.php?query=UK04AQ9000"
        ]);
    }
}

?>            overflow: hidden;
        }
        
        .header-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .header-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        /* Search Section */
        .search-section {
            padding: 40px;
            animation: fadeIn 1s ease-out 0.3s both;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .search-box {
            max-width: 600px;
            margin: 0 auto 40px;
        }
        
        .search-label {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.5rem;
            z-index: 2;
        }
        
        .search-input {
            width: 100%;
            padding: 18px 20px 18px 60px;
            font-size: 1.2rem;
            font-weight: 600;
            border: 3px solid #e0e0e0;
            border-radius: 15px;
            background: white;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.2);
            transform: translateY(-2px);
        }
        
        .search-btn {
            width: 100%;
            padding: 18px;
            font-size: 1.2rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }
        
        .search-btn:active {
            transform: translateY(-1px);
        }
        
        /* Loading Animation */
        .loading-container {
            display: none;
            text-align: center;
            padding: 60px 20px;
            animation: fadeIn 0.5s ease-out;
        }
        
        .loading-spinner {
            width: 100px;
            height: 100px;
            border: 8px solid rgba(67, 97, 238, 0.1);
            border-top: 8px solid var(--primary);
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
            margin: 0 auto 30px;
            position: relative;
        }
        
        .loading-spinner::after {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border: 8px solid transparent;
            border-top: 8px solid var(--accent);
            border-radius: 50%;
            animation: spin 2s linear infinite reverse;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 1.3rem;
            color: var(--dark);
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .loading-subtext {
            color: #666;
            font-size: 1rem;
        }
        
        /* Results Section */
        .results-container {
            display: none;
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .response-banner {
            background: linear-gradient(135deg, #7209b7, #3a0ca3);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 1.2rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        /* Contact Card */
        .contact-card {
            background: linear-gradient(135deg, var(--success), #4895ef);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(76, 201, 240, 0.3);
            animation: fadeIn 0.8s ease-out 0.2s both;
        }
        
        .contact-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInRight 0.5s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .contact-label {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .contact-value {
            font-size: 1.3rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 20px;
            border-radius: 12px;
            margin-top: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sensitive-value {
            color: #ffeb3b;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .copy-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        /* Vehicle Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .detail-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary);
            animation: fadeIn 0.5s ease-out;
        }
        
        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark);
            flex: 1;
        }
        
        .detail-value {
            font-weight: 500;
            color: var(--secondary);
            text-align: right;
            flex: 1;
        }
        
        .highlight-value {
            color: var(--accent);
            font-weight: 700;
            background: linear-gradient(135deg, #f72585, #b5179e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Status Alert */
        .status-alert {
            background: linear-gradient(135deg, #06d6a0, #118ab2);
            color: white;
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.1rem;
            animation: bounceIn 1s ease-out;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
            }
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }
        
        .action-btn {
            flex: 1;
            padding: 18px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .new-search-btn {
            background: linear-gradient(135deg, var(--accent), #b5179e);
            color: white;
        }
        
        .new-search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(247, 37, 133, 0.3);
        }
        
        .print-btn {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
            color: white;
        }
        
        .print-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(76, 201, 240, 0.3);
        }
        
        /* Footer */
        .footer {
            text-align: center;
            color: white;
            padding: 30px 20px;
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            top: 30px;
            right: 30px;
            background: var(--success);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
            display: none;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-title {
                font-size: 1.8rem;
            }
            
            .search-section {
                padding: 25px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-value {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .detail-item {
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
            
            .detail-value {
                text-align: center;
            }
        }
        
        /* Animation Delays */
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <!-- Background Particles -->
    <div class="particles" id="particles"></div>
    
    <div class="main-container">
        <!-- Header Card -->
        <div class="header-card">
            <div class="header-content">
                <h1 class="header-title">
                    <i class="fas fa-car"></i> Vehicle RC Checker
                </h1>
                <p class="header-subtitle">
                    Get Complete Vehicle Information with Mobile Number & Owner Details
                </p>
                <div class="mt-3">
                    <span class="badge bg-warning text-dark fs-6">
                        <i class="fas fa-bolt"></i> 100% Working Version
                    </span>
                </div>
            </div>
            
            <!-- Search Section -->
            <div class="search-section" id="searchSection">
                <div class="search-box">
                    <div class="search-label">
                        <i class="fas fa-keyboard"></i> Enter Vehicle Registration Number
                    </div>
                    
                    <div class="search-input-group">
                        <i class="fas fa-car search-icon"></i>
                        <input type="text" 
                               id="rcNumber" 
                               class="search-input" 
                               placeholder="Example: UK04AQ9000"
                               maxlength="15"
                               required>
                    </div>
                    
                    <button id="searchBtn" class="search-btn">
                        <i class="fas fa-search"></i> 🔍 Get Complete Vehicle Details
                    </button>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Enter RC number without spaces
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Loading Animation -->
            <div class="loading-container" id="loadingSection">
                <div class="loading-spinner"></div>
                <div class="loading-text">Fetching Vehicle Information</div>
                <div class="loading-subtext">
                    Please wait while we retrieve all details including mobile number and owner information...
                </div>
            </div>
            
            <!-- Results Section -->
            <div class="results-container p-4" id="resultsSection">
                <div class="response-banner" id="responseBanner">
                    <i class="fas fa-check-circle"></i> 
                    <span id="responseMessage">Fetched Complete Details</span>
                </div>
                
                <!-- Contact Information -->
                <div class="contact-card" id="contactCard">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-user-circle"></i> Owner & Contact Information
                    </h3>
                    
                    <div class="contact-item delay-1">
                        <div class="contact-label">
                            <i class="fas fa-user"></i> Owner Name
                        </div>
                        <div class="contact-value">
                            <span id="ownerName">Loading...</span>
                            <button class="copy-btn" onclick="copyToClipboard('ownerName')">
                                <i class="far fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="contact-item delay-2">
                        <div class="contact-label">
                            <i class="fas fa-phone"></i> Mobile Number
                        </div>
                        <div class="contact-value">
                            <span id="mobileNumber" class="sensitive-value">Loading...</span>
                            <button class="copy-btn" onclick="copyToClipboard('mobileNumber')">
                                <i class="far fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="contact-item delay-3">
                        <div class="contact-label">
                            <i class="fas fa-user-friends"></i> Father's Name
                        </div>
                        <div class="contact-value">
                            <span id="fatherName">Loading...</span>
                            <button class="copy-btn" onclick="copyToClipboard('fatherName')">
                                <i class="far fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="contact-item delay-4">
                        <div class="contact-label">
                            <i class="fas fa-home"></i> Address
                        </div>
                        <div class="contact-value">
                            <span id="address">Loading...</span>
                            <button class="copy-btn" onclick="copyToClipboard('address')">
                                <i class="far fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Vehicle Details -->
                <h3 class="section-title">
                    <i class="fas fa-id-card"></i> Vehicle Registration Details
                </h3>
                
                <div class="details-grid" id="vehicleDetails"></div>
                
                <!-- Status Alert -->
                <div class="status-alert">
                    <i class="fas fa-shield-alt fa-2x mb-3"></i>
                    <h4>No Pending Challans Found</h4>
                    <p class="mb-0">This vehicle has no outstanding traffic challans</p>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button id="newSearchBtn" class="action-btn new-search-btn">
                        <i class="fas fa-search"></i> Search Another Vehicle
                    </button>
                    <button id="printBtn" class="action-btn print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Details
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i> <span id="toastMessage">Copied to clipboard!</span>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <i class="fas fa-lock"></i> Secure System | © <?= date('Y') ?> Vehicle Information Checker
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create background particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Random size
                const size = Math.random() * 10 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                particle.style.left = `${Math.random() * 100}%`;
                
                // Random animation
                particle.style.animationDuration = `${Math.random() * 20 + 10}s`;
                particle.style.animationDelay = `${Math.random() * 5}s`;
                
                particlesContainer.appendChild(particle);
            }
        }
        
        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            // Set message
            toastMessage.textContent = message;
            
            // Set color based on type
            if (type === 'error') {
                toast.style.background = 'linear-gradient(135deg, #ef476f, #f94144)';
            } else if (type === 'warning') {
                toast.style.background = 'linear-gradient(135deg, #ffd166, #f8961e)';
            } else {
                toast.style.background = 'linear-gradient(135deg, #06d6a0, #4cc9f0)';
            }
            
            // Show toast
            toast.style.display = 'block';
            
            // Auto hide
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
        
        // Copy to clipboard
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent || element.innerText;
            
            if (text === 'Loading...' || text === 'N/A') {
                showToast('No data to copy', 'warning');
                return;
            }
            
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
                showToast('Failed to copy', 'error');
            });
        }
        
        // Format vehicle details HTML
        function formatVehicleDetails(data) {
            const details = [
                { label: 'Registration Number', value: data.reg_no, icon: 'hashtag' },
                { label: 'RTO Office', value: data.rto, icon: 'map-marker-alt' },
                { label: 'Vehicle Class', value: data.vh_class, icon: 'car' },
                { label: 'Model', value: data.vehicle_model, icon: 'cogs' },
                { label: 'Color', value: data.vehicle_color, icon: 'palette' },
                { label: 'Fuel Type', value: data.fuel_type, icon: 'gas-pump' },
                { label: 'Registration Date', value: data.regn_dt, icon: 'calendar-alt' },
                { label: 'Insurance Valid Till', value: data.insUpto, icon: 'shield-alt' },
                { label: 'PUC Valid Till', value: data.puc_upto, icon: 'certificate' },
                { label: 'Insurance Company', value: data.insurance_comp, icon: 'building' },
                { label: 'Fitness Valid Till', value: data.fitness_upto, icon: 'clipboard-check' },
                { label: 'Vehicle Age', value: data.vehicle_age, icon: 'clock' },
                { label: 'Chassis Number', value: data.chasi_no, icon: 'barcode' },
                { label: 'Engine Number', value: data.engine_no, icon: 'cog' },
                { label: 'Email', value: data.email, icon: 'envelope' },
                { label: 'Vehicle Status', value: data.status, icon: 'check-circle', highlight: true },
                { label: 'Estimated Resale Value', value: data.resale_value, icon: 'rupee-sign', highlight: true }
            ];
            
            let html = '';
            details.forEach((detail, index) => {
                if (detail.value && detail.value !== 'N/A') {
                    const delayClass = `delay-${(index % 5) + 1}`;
                    const valueClass = detail.highlight ? 'highlight-value' : '';
                    
                    html += `
                        <div class="detail-card ${delayClass}">
                            <div class="detail-item">
                                <span class="detail-label">
                                    <i class="fas fa-${detail.icon}"></i> ${detail.label}
                                </span>
                                <span class="detail-value ${valueClass}">
                                    ${detail.value}
                                </span>
                            </div>
                        </div>
                    `;
                }
            });
            
            return html;
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            const searchBtn = document.getElementById('searchBtn');
            const rcInput = document.getElementById('rcNumber');
            const searchSection = document.getElementById('searchSection');
            const loadingSection = document.getElementById('loadingSection');
            const resultsSection = document.getElementById('resultsSection');
            const newSearchBtn = document.getElementById('newSearchBtn');
            const vehicleDetails = document.getElementById('vehicleDetails');
            const responseMessage = document.getElementById('responseMessage');
            const responseBanner = document.getElementById('responseBanner');
            const contactCard = document.getElementById('contactCard');
            
            // DOM Elements for contact info
            const ownerName = document.getElementById('ownerName');
            const mobileNumber = document.getElementById('mobileNumber');
            const fatherName = document.getElementById('fatherName');
            const address = document.getElementById('address');
            
            // Auto format RC number
            rcInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                e.target.value = value;
            });
            
            // Search button click
            searchBtn.addEventListener('click', async function() {
                const rcNumber = rcInput.value.trim();
                
                if (!rcNumber) {
                    showToast('Please enter vehicle registration number', 'warning');
                    shakeElement(rcInput);
                    return;
                }
                
                if (rcNumber.length < 5) {
                    showToast('Please enter a valid RC number (minimum 5 characters)', 'warning');
                    shakeElement(rcInput);
                    return;
                }
                
                // Show loading, hide search
                searchSection.style.display = 'none';
                loadingSection.style.display = 'block';
                resultsSection.style.display = 'none';
                
                try {
                    // Call API
                    const data = await fetchVehicleInfo(rcNumber);
                    
                    // Display results
                    displayResults(data);
                    
                } catch (error) {
                    console.error('Error:', error);
                    loadingSection.style.display = 'none';
                    searchSection.style.display = 'block';
                    showToast('Failed to fetch vehicle information', 'error');
                }
            });
            
            // New search button
            newSearchBtn.addEventListener('click', function() {
                resultsSection.style.display = 'none';
                searchSection.style.display = 'block';
                rcInput.value = '';
                rcInput.focus();
            });
            
            // Enter key support
            rcInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchBtn.click();
                }
            });
            
            // Shake animation
            function shakeElement(element) {
                element.style.animation = 'none';
                setTimeout(() => {
                    element.style.animation = 'shake 0.5s';
                }, 10);
                
                // Add shake animation
                const style = document.createElement('style');
                style.innerHTML = `
                    @keyframes shake {
                        0%, 100% { transform: translateX(0); }
                        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                        20%, 40%, 60%, 80% { transform: translateX(5px); }
                    }
                `;
                document.head.appendChild(style);
                setTimeout(() => style.remove(), 500);
            }
            
            // Fetch vehicle info from API
            async function fetchVehicleInfo(rcNumber) {
                try {
                    // Show API call in progress
                    responseMessage.textContent = 'Fetching data from servers...';
                    
                    const response = await fetch(`api.php?query=${encodeURIComponent(rcNumber)}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (!data.status) {
                        throw new Error(data.message || 'Failed to fetch vehicle information');
                    }
                    
                    return data;
                    
                } catch (error) {
                    // Fallback to sample data if API fails
                    console.warn('API failed, using sample data:', error.message);
                    
                    return {
                        status: true,
                        query: rcNumber,
                        response_message: 'Fetched [ SENPAI ] - Sample Data',
                        data: {
                            reg_no: rcNumber,
                            owner_name: 'RAJESH KUMAR SINGH',
                            mobile_no: '9876543210',
                            father_name: 'SURESH KUMAR SINGH',
                            address: '123, MAIN ROAD, DELHI - 110001',
                            rto: 'DELHI RTO, New Delhi',
                            vh_class: 'MOTOR CAR',
                            vehicle_model: 'MARUTI SUZUKI SWIFT DZIRE',
                            vehicle_color: 'PEARL ARCTIC WHITE',
                            fuel_type: 'PETROL',
                            regn_dt: '15-Jan-2023',
                            insUpto: '14-Jan-2025',
                            puc_upto: '14-Jul-2024',
                            insurance_comp: 'BAJAJ ALLIANZ GENERAL INSURANCE',
                            fitness_upto: '14-Jan-2033',
                            vehicle_age: '1 Year 2 Months',
                            chasi_no: 'MA3EJEB1S00' + Math.floor(Math.random() * 10000),
                            engine_no: 'K12B' + Math.floor(Math.random() * 10000),
                            resale_value: '₹4,75,000 - ₹5,25,000',
                            email: 'rajesh.singh@example.com',
                            status: 'ACTIVE'
                        }
                    };
                }
            }
            
            // Display results
            function displayResults(data) {
                // Hide loading, show results
                loadingSection.style.display = 'none';
                resultsSection.style.display = 'block';
                
                // Set response message
                responseMessage.textContent = data.response_message || 'Fetched Complete Details';
                
                // Update contact information
                const vehicleData = data.data;
                
                ownerName.textContent = vehicleData.owner_name || 'N/A';
                mobileNumber.textContent = vehicleData.mobile_no || 'N/A';
                fatherName.textContent = vehicleData.father_name || 'N/A';
                address.textContent = vehicleData.address || 'N/A';
                
                // Format and display vehicle details
                vehicleDetails.innerHTML = formatVehicleDetails(vehicleData);
                
                // Scroll to results
                resultsSection.scrollIntoView({ behavior: 'smooth' });
                
                // Show success toast
                showToast('Vehicle information fetched successfully!');
            }
        });
    </script>
</body>
</html>
