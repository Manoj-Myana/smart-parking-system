<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>QR Code Scanner</title>
  <script src="assets/html5-qrcode.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 30px;
    }

    .container {
      max-width: 500px;
      width: 100%;
      background: #fff;
      padding: 25px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      border-radius: 10px;
      text-align: center;
    }

    h2 {
      margin-bottom: 20px;
    }

    #qr-reader {
      width: 100%;
      margin: 0 auto;
    }

    .alert {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      padding: 10px;
      margin-top: 20px;
      border-radius: 5px;
    }

    #scan-result {
      margin-top: 20px;
      text-align: left;
      display: none;
    }

    button {
      margin-top: 15px;
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
      background-color: #007BFF;
      color: white;
      border: none;
      border-radius: 5px;
    }

    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>📷 Scan QR Code for Entry</h2>
    <div id="qr-reader"></div>
    <div id="status-message" class="alert" style="display:none;"></div>

    <div id="scan-result">
      <p><strong>Booking ID:</strong> <span id="booking_id"></span></p>
      <p><strong>Vehicle Owner:</strong> <span id="vehicle_owner_name"></span></p>
      <p><strong>Vehicle Number:</strong> <span id="vehicle_number"></span></p>
      <p><strong>License Number:</strong> <span id="license_number"></span></p>
      <p><strong>Parking Slot:</strong> <span id="slot_number"></span></p>
      <p><strong>Entry Time:</strong> <span id="entry_time"></span></p>
      <p><strong>Exit Time:</strong> <span id="exit_time"></span></p>
    </div>

    <button id="confirm-btn" class="hidden" onclick="confirmParking()">✅ Done</button>
  </div>

  <script>
    let scannedData = null;
    let scannerRunning = false;
    let qrScanner = null;
  
    // 🔐 Set session owner's ID from PHP
    const SESSION_OWNER_ID = <?php echo json_encode($_SESSION['owner_id'] ?? null); ?>;
  
    function onScanSuccess(decodedText) {
      if (scannerRunning) return;
      scannerRunning = true;
      qrScanner.stop().then(() => {
        console.log("📸 QR Scanner stopped.");
      });
  
      console.log("✅ Scanned Data:", decodedText);
  
      try {
        let qrData = JSON.parse(decodedText.trim());
  
        fetch("backend/fetch_booking_details.php", {
          method: "POST",
          body: JSON.stringify({ booking_id: qrData.booking_id }),
          headers: { "Content-Type": "application/json" }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const now = new Date();
            const entryTime = new Date(data.entry_time);
            const exitTime = new Date(data.exit_time);
            const isParked = data.is_parked;
  
            let message = "";
            let allowDoneButton = false;
  
            // ✅ Check if this booking belongs to the logged-in parking owner
            if (data.parking_owner_id != SESSION_OWNER_ID) {
              message = "❌ You did not book this place, please check your place.";
            } else {
              // ✅ Proceed with booking time logic
              if (now >= entryTime && now <= exitTime) {
                message = isParked == 0 ? "✅ He can enter and park vehicle." : "✅ He can exit the parking area.";
                allowDoneButton = true;
              } else {
                if (isParked == 0) {
                  message = "❌ Sorry, your booking time does not match the current time.";
                } else {
                  message = "⚠️ Pay fine and go.";
                  allowDoneButton = true;
                }
              }
            }
  
            // Display booking details
            document.getElementById("status-message").innerText = message;
            document.getElementById("status-message").style.display = "block";
            document.getElementById("booking_id").innerText = data.booking_id;
            document.getElementById("vehicle_owner_name").innerText = data.vehicle_owner_name;
            document.getElementById("vehicle_number").innerText = data.vehicle_number;
            document.getElementById("license_number").innerText = data.license_number;
            document.getElementById("slot_number").innerText = data.slot_number || 'N/A';
            document.getElementById("entry_time").innerText = data.entry_time;
            document.getElementById("exit_time").innerText = data.exit_time;
            document.getElementById("scan-result").style.display = "block";
  
            document.getElementById("confirm-btn").style.display = allowDoneButton ? "inline-block" : "none";
            scannedData = allowDoneButton ? data : null;
  
          } else {
            alert("❌ Booking not found.");
          }
        })
        .catch(error => {
          console.error("❌ Fetch error:", error);
        });
      } catch (error) {
        console.error("❌ Invalid QR Code:", error);
        alert("❌ Invalid QR Code format.");
      }
    }
  
    function confirmParking() {
      if (!scannedData) {
        alert("❌ You are not allowed to proceed.");
        return;
      }
  
      fetch("backend/store_parking_scan.php", {
        method: "POST",
        body: JSON.stringify({ slot_id: scannedData.slot_number }),
        headers: { "Content-Type": "application/json" }
      })
      .then(response => response.json())
      .then(result => {
        alert(result.message);
        window.location.href = "dashboard.html";
      })
      .catch(error => console.error("❌ Error confirming parking:", error));
    }
  
    function startScanner() {
      if (typeof Html5Qrcode === "undefined") {
        alert("QR Scanner not loaded.");
        return;
      }
  
      qrScanner = new Html5Qrcode("qr-reader");
      Html5Qrcode.getCameras().then(cameras => {
        if (cameras && cameras.length) {
          qrScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            onScanSuccess,
            (errorMessage) => {
              console.warn("Scan error:", errorMessage);
            }
          );
        } else {
          alert("❌ No camera found.");
        }
      }).catch(err => {
        console.error("❌ Camera error:", err);
        alert("❌ Camera access error.");
      });
    }
  
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(startScanner, 1000);
    });
  </script>
  
</body>
</html>
