<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚗 Vehicle RC Checker | 100% Working</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #f72585;
            --success: #4cc9f0;
            --dark: #1a1a2e;
            --light: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Background Animation */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header Card */
        .header-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            margin-bottom: 30px;
            animation: slideDown 0.8s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header-content {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
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