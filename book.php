<?php
// Booking System
include 'db.php';

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $guest_name = $_POST['guest_name'];
    $guest_email = $_POST['guest_email'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    // Validate check-out date is after check-in date
    if (strtotime($check_out) <= strtotime($check_in)) {
        echo '<script>alert("Check-out date must be after check-in date.");</script>';
    } else {
        // Check availability
        $sql_check = "SELECT * FROM bookings WHERE hotel_id = ? AND NOT (? <= check_in OR ? >= check_out)";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("iss", $hotel_id, $check_out, $check_in);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            echo '<script>alert("Room is no longer available.");</script>';
        } else {
            // Insert booking
            $sql = "INSERT INTO bookings (hotel_id, guest_name, guest_email, check_in, check_out) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issss", $hotel_id, $guest_name, $guest_email, $check_in, $check_out);
            if ($stmt->execute()) {
                echo '<script>alert("Booking confirmed!"); window.location.href = "index.php";</script>';
            } else {
                echo '<script>alert("Error: ' . $conn->error . '");</script>';
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
} else {
    // Fetch hotel details
    $sql_hotel = "SELECT * FROM hotels WHERE id = ?";
    $stmt_hotel = $conn->prepare($sql_hotel);
    $stmt_hotel->bind_param("i", $hotel_id);
    $stmt_hotel->execute();
    $result_hotel = $stmt_hotel->get_result();
    $hotel = $result_hotel->fetch_assoc();
    $stmt_hotel->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheraton Hotels - Book Room</title>
    <style>
        /* Internal CSS - Beautiful sea green and light brown theme */
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #D2B48C, #2E8B57);
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #2E8B57;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .booking-form {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: rgba(255,255,255,0.9);
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        input[type="text"], input[type="email"], input[type="date"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #2E8B57;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="date"]:focus {
            border-color: #D2B48C;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #D2B48C;
            color: #2E8B57;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s, transform 0.2s;
        }
        button:hover {
            background-color: #2E8B57;
            color: #D2B48C;
            transform: scale(1.05);
        }
        .hotel-details {
            text-align: center;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .booking-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Book Your Stay</h1>
    </header>
    <section class="booking-form">
        <?php if (isset($hotel)): ?>
            <div class="hotel-details">
                <h2><?php echo htmlspecialchars($hotel['name']); ?></h2>
                <p><?php echo htmlspecialchars($hotel['location']); ?> - From <?php echo htmlspecialchars($check_in); ?> to <?php echo htmlspecialchars($check_out); ?></p>
                <p>Price: $<?php echo $hotel['price']; ?>/night</p>
            </div>
            <form method="POST">
                <input type="text" name="guest_name" placeholder="Your Name" required>
                <input type="email" name="guest_email" placeholder="Your Email" required>
                <input type="date" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>" required readonly>
                <input type="date" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>" required readonly>
                <button type="submit">Confirm Booking</button>
            </form>
        <?php else: ?>
            <p>Invalid hotel selection.</p>
        <?php endif; ?>
    </section>
</body>
</html>
