<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "topup_game";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generateOTP() {
    return sprintf("%06d", mt_rand(0, 999999));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['sendOTP'])) {
        $phoneNumber = $_POST['phoneNumber'];
        $otp = generateOTP();
        
        // Save OTP to database
        $sql = "INSERT INTO verifications (phone_number, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $phoneNumber, $otp);
        
        if ($stmt->execute()) {
            // Send OTP via WhatsApp
            $message = urlencode("Your OTP for game top-up is: $otp. It will expire in 5 minutes.");
            $whatsappUrl = "https://wa.me/$phoneNumber?text=$message";
            
            echo json_encode(['success' => true, 'message' => 'OTP sent successfully', 'whatsappUrl' => $whatsappUrl]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
            exit();
        }
        
        $stmt->close();
    } elseif (isset($_POST['verifyOTP'])) {
        $phoneNumber = $_POST['phoneNumber'];
        $otp = $_POST['otp'];
        
        $sql = "SELECT * FROM verifications WHERE phone_number = ? AND otp = ? AND expires_at > NOW() AND verified = FALSE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $phoneNumber, $otp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // OTP is valid
            $sql = "UPDATE verifications SET verified = TRUE WHERE phone_number = ? AND otp = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $phoneNumber, $otp);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
            exit();
        }
        
        $stmt->close();
    } elseif (isset($_POST['submitOrder'])) {
        $game = $_POST['game'];
        $userId = $_POST['userId'];
        $phoneNumber = $_POST['phoneNumber'];
        $amount = $_POST['amount'];
        $paymentMethod = $_POST['paymentMethod'];
        
        // Check if phone number is verified
        $sql = "SELECT * FROM verifications WHERE phone_number = ? AND verified = TRUE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Phone number is verified, proceed with order
            $sql = "INSERT INTO orders (game, user_id, phone_number, amount, payment_method) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssds", $game, $userId, $phoneNumber, $amount, $paymentMethod);
            
            if ($stmt->execute()) {
                $orderId = $conn->insert_id;
                
                // Send order details via WhatsApp
                $message = urlencode("Terima kasih atas pesanan Anda. Detail pesanan: Game: $game, User ID: $userId, Jumlah: Rp $amount, Metode Pembayaran: $paymentMethod. Order ID: $orderId");
                $whatsappUrl = "https://wa.me/$phoneNumber?text=$message";
                
                echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'whatsappUrl' => $whatsappUrl]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to place order']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Phone number not verified']);
            exit();
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Top-up Game</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input, select {
            padding: 8px;
            margin-top: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: #45a049;
        }
        .hidden {
            display: none;
        }
        #paymentDetails, #processingPayment {
            margin-top: 20px;
            padding: 20px;
            background-color: #e9f7ef;
            border-radius: 5px;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="topupForm">
            <h1>Top-up Game Anda</h1>
            <form id="initialForm">
                <label for="game">Pilih Game:</label>
                <select id="game" name="game" required>
                    <option value="">--Pilih Game--</option>
                    <option value="Mobile Legends">Mobile Legends</option>
                    <option value="PUBG Mobile">PUBG Mobile</option>
                    <option value="Free Fire">Free Fire</option>
                </select>

                <label for="userId">ID Pengguna:</label>
                <input type="text" id="userId" name="userId" required>

                <label for="amount">Jumlah Top-up:</label>
                <select id="amount" name="amount" required>
                    <option value="">--Pilih Jumlah--</option>
                    <option value="10000">10.000 (10 Diamonds)</option>
                    <option value="20000">20.000 (50 Diamonds)</option>
                    <option value="50000">50.000 (150 Diamonds)</option>
                    <option value="100000">100.000 (300 Diamonds)</option>
                </select>

                <label for="paymentMethod">Metode Pembayaran:</label>
                <select id="paymentMethod" name="paymentMethod" required>
                    <option value="">--Pilih Metode Pembayaran--</option>
                    <option value="bankTransfer">Transfer Bank</option>
                    <option value="eWallet">E-Wallet</option>
                    <option value="creditCard">Kartu Kredit</option>
                </select>

                <label for="phoneNumber">Nomor Telepon (WhatsApp):</label>
                <input type="tel" id="phoneNumber" name="phoneNumber" required pattern="[0-9]{10,15}">
                
                <button type="button" id="sendOTP">Kirim OTP</button>
                
                <div id="otpVerification" class="hidden">
                    <label for="otp">Masukkan OTP:</label>
                    <input type="text" id="otp" name="otp" required pattern="[0-9]{6}">
                    <button type="button" id="verifyOTP">Verifikasi OTP</button>
                </div>

                <button type="submit" id="submitOrder" disabled>Lanjutkan ke Pembayaran</button>
            </form>
        </div>

        <div id="paymentDetails" class="hidden">
            <h2>Detail Pembayaran</h2>
            <p><strong>Game:</strong> <span id="selectedGame"></span></p>
            <p><strong>ID Pengguna:</strong> <span id="selectedUserId"></span></p>
            <p><strong>Jumlah Top-up:</strong> Rp <span id="selectedAmount"></span></p>
            <p><strong>Metode Pembayaran:</strong> <span id="selectedPaymentMethod"></span></p>
            <p><strong>Nomor WhatsApp:</strong> <span id="selectedPhoneNumber"></span></p>
        </div>

        <div id="processingPayment" class="hidden">
            <h2>Memproses Pembayaran</h2>
            <div class="loader"></div>
            <p>Mohon tunggu, pembayaran Anda sedang diproses...</p>
        </div>
    </div>

    <script>
        document.getElementById('sendOTP').addEventListener('click', function() {
            const phoneNumber = document.getElementById('phoneNumber').value;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `sendOTP=1&phoneNumber=${phoneNumber}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    document.getElementById('otpVerification').classList.remove('hidden');
                    window.open(data.whatsappUrl, '_blank');
                } else {
                    alert(data.message);
                }
            });
        });

        document.getElementById('verifyOTP').addEventListener('click', function() {
            const phoneNumber = document.getElementById('phoneNumber').value;
            const otp = document.getElementById('otp').value;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `verifyOTP=1&phoneNumber=${phoneNumber}&otp=${otp}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    document.getElementById('submitOrder').disabled = false;
                } else {
                    alert(data.message);
                }
            });
        });

        document.getElementById('initialForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('submitOrder', '1');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    document.getElementById('topupForm').classList.add('hidden');
                    document.getElementById('paymentDetails').classList.remove('hidden');
                    document.getElementById('selectedGame').textContent = formData.get('game');
                    document.getElementById('selectedUserId').textContent = formData.get('userId');
                    document.getElementById('selectedAmount').textContent = formData.get('amount');
                    document.getElementById('selectedPaymentMethod').textContent = formData.get('paymentMethod');
                    document.getElementById('selectedPhoneNumber').textContent = formData.get('phoneNumber');
                    
                    // Redirect to WhatsApp
                    window.open(data.whatsappUrl, '_blank');
                } else {
                    alert(data.message);
                }
            });
        });
    </script>
</body>
</html>