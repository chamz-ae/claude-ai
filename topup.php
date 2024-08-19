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
        
        // Save OTP to database
        $sql = "INSERT INTO verifications (phone_number, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }
        $stmt->bind_param("ss", $phoneNumber, $otp);
       
        if ($stmt->execute()) {
            $message = urlencode("Your OTP for game top-up is: $otp. It will expire in 5 minutes.");
            $whatsappUrl = "https://wa.me/" . urlencode($phoneNumber) . "?text=$message";
            
            echo json_encode(['success' => true, 'message' => 'OTP sent successfully', 'whatsappUrl' => $whatsappUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP: ' . $stmt->error]);
        }
        $stmt->close();
        exit();
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
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        }
        $stmt->close();
        exit();
    } elseif (isset($_POST['submitOrder'])) {
        $game = $_POST['game'];
        $userId = $_POST['userId'];
        $phoneNumber = $_POST['phoneNumber'];
        $amount = $_POST['amount'];
        $paymentMethod = $_POST['paymentMethod'];
       
        // Check if phone number is verified
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
                $whatsappUrl = "https://wa.me/" . urlencode($phoneNumber) . "?text=$message";
               
                echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'whatsappUrl' => $whatsappUrl, 'paymentProof' => $paymentProof]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Phone number not verified']);
        }
        $stmt->close();
        exit();
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
            background-color: var(--background-color);
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
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

        .hidden {
            display: none;
        }

        .payment-details {
            background-color: var(--card-background);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .payment-details h2 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>GameBoost - Top-up Game dengan Mudah</h1>
    </header>
    <div class="container">
        <div class="game-grid">
            <!-- Game 1 -->
            <div class="game-card">
                <img src="game1.jpg" alt="Game 1" class="game-image">
                <div class="game-info">
                    <div class="game-title">Game 1</div>
                    <a href="#" class="top-up-btn" data-game="Game 1">Top-up Sekarang</a>
                </div>
            </div>

            <!-- Game 2 -->
            <div class="game-card">
                <img src="game2.jpg" alt="Game 2" class="game-image">
                <div class="game-info">
                    <div class="game-title">Game 2</div>
                    <a href="#" class="top-up-btn" data-game="Game 2">Top-up Sekarang</a>
                </div>
            </div>

            <!-- Game 3 -->
            <div class="game-card">
                <img src="game3.jpg" alt="Game 3" class="game-image">
                <div class="game-info">
                    <div class="game-title">Game 3</div>
                    <a href="#" class="top-up-btn" data-game="Game 3">Top-up Sekarang</a>
                </div>
            </div>

            <!-- Game 4 -->
            <div class="game-card">
                <img src="game4.jpg" alt="Game 4" class="game-image">
                <div class="game-info">
                    <div class="game-title">Game 4</div>
                    <a href="#" class="top-up-btn" data-game="Game 4">Top-up Sekarang</a>
                </div>
            </div>

            <!-- Game 5 -->
            <div class="game-card">
                <img src="game5.jpg" alt="Game 5" class="game-image">
                <div class="game-info">
                    <div class="game-title">Game 5</div>
                    <a href="#" class="top-up-btn" data-game="Game 5">Top-up Sekarang</a>
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
</body>
</html>
