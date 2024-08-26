<?php
session_start();

// Enable error reporting for debugging
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
        
        // Save OTP to database
        $sql = "INSERT INTO verifications (phone_number, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $phoneNumber, $otp);
        
        if ($stmt->execute()) {
            $message = urlencode("Your OTP for game top-up is: $otp. It will expire in 5 minutes.");
            $whatsappUrl = "https://wa.me/$phoneNumber?text=$message";
            $response = ['success' => true, 'message' => 'OTP sent successfully', 'whatsappUrl' => $whatsappUrl];
        } else {
            $response = ['success' => false, 'message' => 'Failed to send OTP: ' . $stmt->error];
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
            $sql = "UPDATE verifications SET verified = TRUE WHERE phone_number = ? AND otp = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $phoneNumber, $otp);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'OTP verified successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update verification status: ' . $stmt->error];
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid or expired OTP'];
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
                $response = ['success' => false, 'message' => 'Failed to place order: ' . $stmt->error];
            }
        } else {
            $response = ['success' => false, 'message' => 'Phone number not verified'];
        }
        
        $stmt->close();
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .game-card {
            background-color: var(--card-background);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .game-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .game-info {
            padding: 20px;
        }

        .game-title {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: #ffffff;
            font-weight: 600;
        }

        .top-up-btn {
            display: inline-block;
            background-color: var(--secondary-color);
            color: var(--background-color);
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-align: center;
        }

        .top-up-btn:hover {
            background-color: #81ecec;
            transform: scale(1.05);
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
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: var(--card-background);
            margin: 10% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        .hidden {
            display: none;
        }

        .payment-details {
            background-color: var(--card-background);
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .payment-details h2 {
            margin-bottom: 20px;
            color: #ffffff;
            font-size: 1.8rem;
        }

        form div {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #dfe6e9;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #4a69bd;
            border-radius: 5px;
            background-color: #2c3e50;
            color: #dfe6e9;
        }

        button {
            background-color: var(--secondary-color);
            color: var(--background-color);
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #81ecec;
            transform: scale(1.05);
        }

        button:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header>
        <h1>GameBoost - Top-up Game dengan Mudah</h1>
    </header>
    <div class="container">
        <div class="game-grid">
            <!-- Mobile Legends -->
            <div class="game-card">
                <img src="https://play-lh.googleusercontent.com/ha1vofCWS5lhPpp-a9WjF8qHJmGhPxQnc2jxUl8AxdLl-R9j7tHwWVBGu4MPQqjDfKM" alt="Mobile Legends" class="game-image">
                <div class="game-info">
                    <div class="game-title">Mobile Legends</div>
                    <a href="#" class="top-up-btn" data-game="Mobile Legends">Top-up Sekarang</a>
                </div>
            </div>

            <!-- PUBG Mobile -->
            <div class="game-card">
                <img src="https://play-lh.googleusercontent.com/JRd05pyBH41qjgsJuWduRJpDeZG0Hnb0yjf2nWqO7VaGKL10-G5UIygxED-WNOc3pg" alt="PUBG Mobile" class="game-image">
                <div class="game-info">
                    <div class="game-title">PUBG Mobile</div>
                    <a href="#" class="top-up-btn" data-game="PUBG Mobile">Top-up Sekarang</a>
                </div>
            </div>

            <!-- Free Fire -->
            <div class="game-card">
                <img src="https://play-lh.googleusercontent.com/WWcssdzTZvx7Fc84lfMpVuyMXg83_PwrfpgSBd0IID_IuupsYVYJ34S9R2_5x57gHQ" alt="Free Fire" class="game-image">
                <div class="game-info">
                    <div class="game-title">Free Fire</div>
                    <a href="#" class="top-up-btn" data-game="Free Fire">Top-up Sekarang</a>
                </div>
            </div>

            <!-- Call of Duty Mobile -->
            <div class="game-card">
                <img src="https://play-lh.googleusercontent.com/11nQUJrUUH0JtTTvXqPheB3A4cqoASVZaCQsweNntA7YR7RS6_zUc6oZxVbBzreNGZA" alt="Call of Duty Mobile" class="game-image">
                <div class="game-info">
                    <div class="game-title">Call of Duty Mobile</div>
                    <a href="#" class="top-up-btn" data-game="Call of Duty Mobile">Top-up Sekarang</a>
                </div>
            </div>

            <!-- Point Blank -->
            <div class="game-card">
                <img src="https://play-lh.googleusercontent.com/ONpRDWyhtAxyfcy2CLxLqBtH5dHcmKLZVSptWiiWzN03uQ-tSG5VJmdLyYfHgp6wyQ" alt="Point Blank" class="game-image">
                <div class="game-info">
                    <div class="game-title">Point Blank</div>
                    <a href="#" class="top-up-btn" data-game="Point Blank">Top-up Sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="topUpModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Top-up Game <span id="selectedGameTitle"></span></h2>
            <form id="topUpForm">
                <input type="hidden" id="game" name="game">
                <div>
                    <label for="userId">User ID:</label>
                    <input type="text" id="userId" name="userId" required>
                </div>
                <div>
                    <label for="phoneNumber">Nomor Telepon:</label>
                    <input type="text" id="phoneNumber" name="phoneNumber" required>
                </div>
                <div>
                    <label for="amount">Jumlah Top-up:</label>
                    <input type="number" id="amount" name="amount" required>
                </div>
                <div>
                    <label for="paymentMethod">Metode Pembayaran:</label>
                    <select id="paymentMethod" name="paymentMethod" required>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="OVO">OVO</option>
                        <option value="Gopay">Gopay</option>
                        <option value="DANA">DANA</option>
                        <option value="LinkAja">LinkAja</option>
                        <option value="ShopeePay">ShopeePay</option>
                        <option value="Kartu Kredit">Kartu Kredit</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
                <button type="button" id="sendOTP">Kirim OTP</button>
                <div id="otpVerification" class="hidden">
                    <label for="otp">Masukkan OTP:</label>
                    <input type="text" id="otp" name="otp" required>
                    <button type="button" id="verifyOTP">Verifikasi OTP</button>
                </div>
                <button type="submit" id="submitOrder" disabled>Submit Order</button>
            </form>
        </div>
    </div>

    <!-- Payment Details -->
    <div id="paymentDetails" class="payment-details hidden">
        <h2>Detail Pembayaran</h2>
        <p>Game: <span id="paymentGame"></span></p>
        <p>User ID: <span id="paymentUserId"></span></p>
        <p>Jumlah: <span id="paymentAmount"></span></p>
        <p>Metode Pembayaran: <span id="paymentMethod"></span></p>
        <p>Nomor Telepon: <span id="paymentPhoneNumber"></span></p>
    </div>

    <script>
       document.addEventListener('DOMContentLoaded', () => {
        // Handle OTP send button click
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

        // Handle OTP verify button click
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

        // Handle form submission
        document.getElementById('topUpForm').addEventListener('submit', function(e) {
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
                    document.getElementById('topUpModal').style.display = 'none';
                    document.getElementById('paymentDetails').classList.remove('hidden');
                    
                    document.getElementById('paymentGame').textContent = formData.get('game');
                    document.getElementById('paymentUserId').textContent = formData.get('userId');
                    document.getElementById('paymentAmount').textContent = formData.get('amount');
                    document.getElementById('paymentMethod').textContent = formData.get('paymentMethod');
                    document.getElementById('paymentPhoneNumber').textContent = formData.get('phoneNumber');
                    
                    window.open(data.whatsappUrl, '_blank');
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pembayaran');
            });
        });

        // Handle modal close button
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('topUpModal').style.display = 'none';
        });

        // Handle open modal button
        document.querySelectorAll('.top-up-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const gameTitle = this.dataset.game;
                document.getElementById('selectedGameTitle').textContent = gameTitle;
                document.getElementById('game').value = gameTitle;
                document.getElementById('topUpModal').style.display = 'block';
            });
        });

        // Handle form submission
document.getElementById('topUpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('submitOrder', '1');
    
    fetch('', { // <-- Ganti URL ini dengan endpoint server Anda
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('topUpModal').style.display = 'none';
            document.getElementById('paymentDetails').classList.remove('hidden');
            
            document.getElementById('paymentGame').textContent = formData.get('game');
            document.getElementById('paymentUserId').textContent = formData.get('userId');
            document.getElementById('paymentAmount').textContent = formData.get('amount');
            document.getElementById('paymentMethod').textContent = formData.get('paymentMethod');
            document.getElementById('paymentPhoneNumber').textContent = formData.get('phoneNumber');
            
            window.open(data.whatsappUrl, '_blank');
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses pembayaran');
    });
});

    });
    </script>
</body>x
</html>