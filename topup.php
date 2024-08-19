<?php
session_start();

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "topup_game";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

function generateOTP() {
    return sprintf("%06d", mt_rand(0, 999999));
}

function generateReceiptNumber() {
    return 'RCV' . date('YmdHis') . rand(1000, 9999);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = ['success' => false, 'message' => 'Invalid request'];

    if (isset($_POST['sendOTP'])) {
        $phoneNumber = $_POST['phoneNumber'];
        $otp = generateOTP();
<<<<<<< HEAD
       
        // Save OTP to database
=======
        
>>>>>>> a09492b5e3feec26acc2a3a4820a8873b8506bf5
        $sql = "INSERT INTO verifications (phone_number, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }
        $stmt->bind_param("ss", $phoneNumber, $otp);
       
        if ($stmt->execute()) {
            $message = urlencode("Your OTP for game top-up is: $otp. It will expire in 5 minutes.");
<<<<<<< HEAD
            $whatsappUrl = "https://wa.me/$phoneNumber?text=$message";
            $response = ['success' => true, 'message' => 'OTP sent successfully', 'whatsappUrl' => $whatsappUrl];
        } else {
            $response = ['success' => false, 'message' => 'Failed to send OTP: ' . $conn->error];
        }
       
        $stmt->close();
=======
            $whatsappUrl = "https://wa.me/" . urlencode($phoneNumber) . "?text=$message";
            
            echo json_encode(['success' => true, 'message' => 'OTP sent successfully', 'whatsappUrl' => $whatsappUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP: ' . $stmt->error]);
        }
        exit();
>>>>>>> a09492b5e3feec26acc2a3a4820a8873b8506bf5
    } elseif (isset($_POST['verifyOTP'])) {
        $phoneNumber = $_POST['phoneNumber'];
        $otp = $_POST['otp'];
       
        $sql = "SELECT * FROM verifications WHERE phone_number = ? AND otp = ? AND expires_at > NOW() AND verified = FALSE";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }
        $stmt->bind_param("ss", $phoneNumber, $otp);
        $stmt->execute();
        $result = $stmt->get_result();
       
        if ($result->num_rows > 0) {
            $sql = "UPDATE verifications SET verified = TRUE WHERE phone_number = ? AND otp = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
                exit();
            }
            $stmt->bind_param("ss", $phoneNumber, $otp);
<<<<<<< HEAD
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'OTP verified successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update verification status: ' . $conn->error];
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid or expired OTP'];
        }
       
        $stmt->close();
=======
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        }
        exit();
>>>>>>> a09492b5e3feec26acc2a3a4820a8873b8506bf5
    } elseif (isset($_POST['submitOrder'])) {
        $game = $_POST['game'];
        $userId = $_POST['userId'];
        $phoneNumber = $_POST['phoneNumber'];
        $amount = $_POST['amount'];
        $paymentMethod = $_POST['paymentMethod'];
<<<<<<< HEAD
       
        // Check if phone number is verified
=======
        
>>>>>>> a09492b5e3feec26acc2a3a4820a8873b8506bf5
        $sql = "SELECT * FROM verifications WHERE phone_number = ? AND verified = TRUE";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $result = $stmt->get_result();
       
        if ($result->num_rows > 0) {
<<<<<<< HEAD
            $receiptNumber = generateReceiptNumber();
            $sql = "INSERT INTO orders (receipt_number, game, user_id, phone_number, amount, payment_method) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssds", $receiptNumber, $game, $userId, $phoneNumber, $amount, $paymentMethod);
           
            if ($stmt->execute()) {
                $orderId = $conn->insert_id;
               
                $paymentProof = [
                    'receiptNumber' => $receiptNumber,
                    'orderId' => $orderId,
                    'game' => $game,
                    'userId' => $userId,
                    'amount' => $amount,
                    'paymentMethod' => $paymentMethod,
                    'phoneNumber' => $phoneNumber,
                    'date' => date('Y-m-d H:i:s')
                ];
               
                $message = urlencode("Terima kasih atas pesanan Anda. Detail pesanan:\n\n" .
                    "No. Kuitansi: {$paymentProof['receiptNumber']}\n" .
                    "ID Pesanan: {$paymentProof['orderId']}\n" .
                    "Game: {$paymentProof['game']}\n" .
                    "User ID: {$paymentProof['userId']}\n" .
                    "Jumlah: Rp {$paymentProof['amount']}\n" .
                    "Metode Pembayaran: {$paymentProof['paymentMethod']}\n" .
                    "Tanggal: {$paymentProof['date']}\n\n" .
                    "Ini adalah bukti pembayaran Anda. Harap simpan untuk referensi.");
                $whatsappUrl = "https://wa.me/$phoneNumber?text=$message";
               
                $response = ['success' => true, 'message' => 'Order placed successfully', 'whatsappUrl' => $whatsappUrl, 'paymentProof' => $paymentProof];
            } else {
                $response = ['success' => false, 'message' => 'Failed to place order: ' . $conn->error];
            }
        } else {
            $response = ['success' => false, 'message' => 'Phone number not verified'];
        }
       
        $stmt->close();
=======
            $sql = "INSERT INTO orders (game, user_id, phone_number, amount, payment_method) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
                exit();
            }
            $stmt->bind_param("sssds", $game, $userId, $phoneNumber, $amount, $paymentMethod);
            
            if ($stmt->execute()) {
                $orderId = $conn->insert_id;
                
                $message = urlencode("Thank you for your order. Details: Game: $game, User ID: $userId, Amount: Rp $amount, Payment Method: $paymentMethod. Order ID: $orderId");
                $whatsappUrl = "https://wa.me/" . urlencode($phoneNumber) . "?text=$message";
                
                echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'whatsappUrl' => $whatsappUrl]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Phone number not verified']);
        }
        exit();
>>>>>>> a09492b5e3feec26acc2a3a4820a8873b8506bf5
    }

    echo json_encode($response);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameBoost - Pusat Top-up Game</title>
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #00cec9;
            --background-color: #2d3436;
            --text-color: #dfe6e9;
            --card-background: #34495e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: var(--primary-color);
            padding: 20px 0;
            text-align: center;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .game-card {
            background-color: var(--card-background);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .game-card:hover {
            transform: translateY(-5px);
        }

        .game-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .game-info {
            padding: 20px;
        }

        .game-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .top-up-btn {
            display: inline-block;
            background-color: var(--secondary-color);
            color: var(--background-color);
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .top-up-btn:hover {
            background-color: #81ecec;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: var(--card-background);
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        form {
            display: grid;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        button {
            background-color: var(--secondary-color);
            color: var(--background-color);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #81ecec;
        }

        #otpVerification, #paymentDetails, #processingPayment {
            margin-top: 20px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>GameBoost</h1>
            <p>Pusat Top-up Game Terpercaya</p>
        </div>
    </header>

    <main class="container">
        <div class="game-grid">
            <div class="game-card">
                <img src="https://play-lh.googleusercontent.com/WWcssdzTZvx7Fc84lfMpVuyMXg83_PwHBxA25WSaYHJSg58EkN8WFTJFwPpA3sYCmpk" alt="Mobile Legends" class="game-image">
                <div class="game-info">
                    <h2 class="game-title">Mobile Legends</h2>
                    <a href="#" class="top-up-btn" data-game="Mobile Legends">Top-up Sekarang</a>
                </div>
            </div>
            <div class="game-card">
                <img src="https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNOc3pg" alt="PUBG Mobile" class="game-image">
                <div class="game-info">
                    <h2 class="game-title">PUBG Mobile</h2>
                    <a href="#" class="top-up-btn" data-game="PUBG Mobile">Top-up Sekarang</a>
                </div>
            </div>
            <div class="game-card">
                <img src="https://play-lh.googleusercontent.com/WWcssdzTZvx7Fc84lfMpVuyMXg83_PwHBxA25WSaYHJSg58EkN8WFTJFwPpA3sYCmpk" alt="Free Fire" class="game-image">
                <div class="game-info">
                    <h2 class="game-title">Free Fire</h2>
                    <a href="#" class="top-up-btn" data-game="Free Fire">Top-up Sekarang</a>
                </div>
            </div>
        </div>
    </main>

    <div id="topUpModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Top-up <span id="selectedGameTitle"></span></h2>
            <form id="topUpForm">
                <input type="hidden" id="game" name="game">
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

            <div id="paymentDetails" class="hidden">
                <h3>Detail Pembayaran</h3>
                <p><strong>Game:</strong> <span id="paymentGame"></span></p>
                <p><strong>ID Pengguna:</strong> <span id="paymentUserId"></span></p>
                <p><strong>Jumlah Top-up:</strong> Rp <span id="paymentAmount"></span></p>
                <p><strong>Metode Pembayaran:</strong> <span id="paymentMethod"></span></p>
                <p><strong>Nomor WhatsApp:</strong> <span id="paymentPhoneNumber"></span></p>
            </div>

            <div id="processingPayment" class="hidden">
                <h3>Memproses Pembayaran</h3>
                <p>Mohon tunggu, pembayaran Anda sedang diproses...</p>
            </div>
        </div>
    </div>

    <script>
=======
        document.getElementById('sendOTP').addEventListener('click', function() {
            const phoneNumber = document.getElementById('phoneNumber').value;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `sendOTP=1&phoneNumber=${encodeURIComponent(phoneNumber)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Debugging
                if (data.success) {
                    alert(data.message);
                    document.getElementById('otpVerification').classList.remove('hidden');
                    window.open(data.whatsappUrl, '_blank');
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim OTP');
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
                body: `verifyOTP=1&phoneNumber=${encodeURIComponent(phoneNumber)}&otp=${encodeURIComponent(otp)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Debugging
                if (data.success) {
                    alert(data.message);
                    document.getElementById('submitOrder').disabled = false;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memverifikasi OTP');
            });
        });

        document.getElementById('initialForm').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted'); // Debugging
            
            const formData = new FormData(this);
            formData.append('submitOrder', '1');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Debugging
                if (data.success) {
                    alert(data.message);
                    document.getElementById('topupForm').classList.add('hidden');
                    document.getElementById('paymentDetails').classList.remove('hidden');
                    document.getElementById('selectedGame').textContent = formData.get('game');
                    document.getElementById('selectedUserId').textContent = formData.get('userId');
                    document.getElementById('selectedAmount').textContent = formData.get('amount');
                    document.getElementById('selectedPaymentMethod').textContent = formData.get('paymentMethod');
                    document.getElementById('selectedPhoneNumber').textContent = formData.get('phoneNumber');
                    
                    window.open(data.whatsappUrl, '_blank');
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pembayaran');
            });
>>>>>>> a09492b5e3feec26acc2a3a4820a8873b8506bf5
        });
    });
});
    </script>
</body>
</html>