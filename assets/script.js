console.log("✅ script.js is loaded!");

// Function to log in the user
function loginUser(event) {
    event.preventDefault();

    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    fetch("backend/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    })
    .then(response => response.text()) // Read response as text first
    .then(text => {
        console.log("🔍 Raw Server Response:", text);

        try {
            let data = JSON.parse(text); // Try to parse JSON
            console.log("✅ Parsed JSON:", data);

            if (data.success) {
                alert("✅ Login successful!");

                if (data.userType === "vehicle_owner") {
                    localStorage.setItem("vehicle_owner_id", data.user.id);
                    localStorage.setItem("vehicle_type", data.user.vehicleType);
                    window.location.href = "search_parking.html"; // Redirect Vehicle Owner
                } else if (data.userType === "parking_owner") {
                    localStorage.setItem("parking_owner_id", data.user.id);
                    window.location.href = "dashboard.html"; // Redirect Parking Owner
                }
            } else {
                alert("❌ Login failed: " + data.message);
            }
        } catch (error) {
            console.error("❌ JSON Parse Error:", error);
            alert("❌ Server error! Check console for details.");
        }
    })
    .catch(error => {
        console.error("❌ Fetch Error:", error);
        alert("An error occurred while logging in.");
    });
}


// Attach login function to form submission
document.getElementById("loginForm")?.addEventListener("submit", loginUser);


// Function to toggle form based on user type selection
function toggleForm() {
    let userTypeElement = document.getElementById("userType");

    if (!userTypeElement) {
        console.warn("⚠️ userType dropdown not found.");
        return;
    }

    let userType = userTypeElement.value;
    let vehicleForm = document.getElementById("vehicleOwnerForm");
    let parkingForm = document.getElementById("parkingOwnerForm");

    if (vehicleForm && parkingForm) {
        vehicleForm.style.display = (userType === "vehicle_owner") ? "block" : "none";
        parkingForm.style.display = (userType === "parking_owner") ? "block" : "none";
    }
}

// Run toggleForm() after DOM loads
// Check if the userType dropdown exists before adding an event listener
document.addEventListener("DOMContentLoaded", () => {
    let userTypeElement = document.getElementById("userType");
    if (userTypeElement) {
        userTypeElement.addEventListener("change", toggleForm);
        toggleForm();  // Call only if dropdown exists
    } else {
        console.log("ℹ️ No userType dropdown found. Skipping form toggle.");
    }
});


// Add event listener to dropdown to toggle form when user selects a different type
document.getElementById("userType")?.addEventListener("change", toggleForm);


// Function to register a user (Vehicle Owner or Parking Owner)
function registerUser(userType) {
    let userData = {};

    if (userType === "vehicle_owner") {
        userData = {
            userType,
            name: document.getElementById("vName")?.value.trim(),
            vehicleType: document.getElementById("vVehicleType")?.value.trim(),
            vehicleNumber: document.getElementById("vVehicleNumber")?.value.trim(),
            licenseNumber: document.getElementById("vLicenseNumber")?.value.trim(),
            email: document.getElementById("vEmail")?.value.trim(),
            password: document.getElementById("vPassword")?.value.trim()
        };
    } else if (userType === "parking_owner") {
        userData = {
            userType,
            parkingName: document.getElementById("pParkingName")?.value.trim(),
            area: document.getElementById("pArea")?.value.trim(),
            city: document.getElementById("pCity")?.value.trim(),
            pincode: document.getElementById("pPincode")?.value.trim(),
            ownerName: document.getElementById("pOwnerName")?.value.trim(),
            email: document.getElementById("pEmail")?.value.trim(),
            password: document.getElementById("pPassword")?.value.trim(),
            totalCars: parseInt(document.getElementById("pTotalCars")?.value.trim()) || 0,
            totalBikes: parseInt(document.getElementById("pTotalBikes")?.value.trim()) || 0
        };
    } else {
        alert("❌ Invalid user type!");
        return;
    }

    // ✅ Validate required fields
    if (Object.values(userData).some(value => value === "")) {
        alert("⚠️ Please fill in all required fields!");
        return;
    }

    console.log("🚀 Sending Data:", JSON.stringify(userData));

    fetch("http://localhost/smart-parking-system/backend/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(userData)
    })
    .then(response => response.text()) // Read response as text first
    .then(text => {
        console.log("🔍 Raw Server Response:", text);

        try {
            let jsonData = JSON.parse(text);
            console.log("✅ Parsed Response:", jsonData);

            if (jsonData.success) {
                alert("✅ Registration successful!");
                window.location.href = "index.html"; // Redirect to login
            } else {
                alert("❌ Registration failed: " + jsonData.message);
            }
        } catch (error) {
            console.error("❌ JSON Parse Error:", error);
            alert("❌ Server error: Check console for details.");
        }
    })
    .catch(error => console.error("❌ Fetch Error:", error));
}

function searchParking() {
    let searchValue = document.getElementById("searchArea").value.trim();

    if (searchValue === "") {
        alert("Please enter an area, city, parking name, or pincode to search.");
        return;
    }

    fetch("backend/search_parking.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ search: searchValue })
    })
    .then(response => response.json())
    .then(data => {
        let resultsDiv = document.getElementById("parkingResults");
        resultsDiv.innerHTML = ""; // Clear previous results

        if (!data.success) {
            resultsDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
            return;
        }

        let resultsContainer = document.createElement("div");

        data.parkings.forEach((parking) => {
            let table = document.createElement("table");
            table.border = "1";
            table.style.marginBottom = "20px";
            table.style.cursor = "pointer"; // Clickable
            table.classList.add("parking-option");

            let headerRow = table.insertRow();
            headerRow.innerHTML = `<th>Parking Name</th><th>Area</th><th>City</th><th>Pincode</th>`;

            let dataRow = table.insertRow();
            dataRow.innerHTML = `<td>${parking.parking_name}</td>
                                 <td>${parking.area}</td>
                                 <td>${parking.city}</td>
                                 <td>${parking.pincode}</td>`;

            // ✅ Click event to select parking
            table.onclick = function () {
                document.querySelectorAll(".parking-option").forEach(t => t.style.backgroundColor = "");

                table.style.backgroundColor = "#d1f7d1"; // Highlight selected

                document.getElementById("selectedParkingId").value = parking.owner_id;

                document.getElementById("bookingSection").style.display = "block";

                document.getElementById("selectedParkingDetails").innerHTML =
                    `<strong>Selected Parking:</strong> ${parking.parking_name}, ${parking.area}, ${parking.city}, ${parking.pincode}`;
            };

            resultsContainer.appendChild(table);
        });

        resultsDiv.appendChild(resultsContainer);
    })
    .catch(error => console.error("❌ Error fetching parking slots:", error));
}

// ✅ Proceed to Payment
function proceedToPayment() {
    let vehicleOwnerId = localStorage.getItem("vehicle_owner_id");
    let vehicleType = localStorage.getItem("vehicle_type");
    let parkingOwnerId = document.getElementById("selectedParkingId")?.value; // ✅ Get parking owner ID
    let entryTime = document.getElementById("entryTime").value;
    let exitTime = document.getElementById("exitTime").value;

    console.log("📌 Selected Parking Owner ID:", parkingOwnerId); // ✅ Debugging

    if (!vehicleOwnerId || !vehicleType) {
        alert("❌ Please log in as a vehicle owner before booking.");
        return;
    }

    if (!parkingOwnerId) {
        alert("❌ Please select a parking area.");
        return;
    }

    if (!entryTime || !exitTime) {
        alert("❌ Please enter both entry and exit time.");
        return;
    }

    let bookingData = {
        parking_owner_id: parkingOwnerId,
        vehicle_owner_id: vehicleOwnerId,
        entry_time: entryTime,
        exit_time: exitTime,
        vehicle_type: vehicleType.toLowerCase()
    };

    fetch("backend/book_parking.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bookingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log("📌 Booking Successful! Booking ID:", data.booking_id);
            localStorage.setItem("booking_id", data.booking_id);
            localStorage.setItem("parking_owner_id", parkingOwnerId); // ✅ Store Parking Owner ID
            localStorage.setItem("vehicle_type", vehicleType);

            // ✅ Pass parking_owner_id in URL
            window.location.href = `payment.html?cost=${data.amount}&booking_id=${data.booking_id}&parking_owner_id=${parkingOwnerId}`;
        } else {
            alert("❌ Booking failed: " + data.message);
        }
    })
    .catch(error => {
        console.error("❌ Error:", error);
        alert("An error occurred. Please try again.");
    });
}







function bookParking() {
    let parkingOwnerId = document.getElementById("selectedParkingId").value;
    let vehicleOwnerId = localStorage.getItem("vehicle_owner_id");
    let vehicleType = localStorage.getItem("vehicle_type");

    if (!vehicleOwnerId || !vehicleType) {
        alert("Missing user details! Please log in again.");
        window.location.href = "login.html"; // Redirect to login
        return;
    }

    let entryTime = document.getElementById("entryTime").value;
    let exitTime = document.getElementById("exitTime").value;

    if (!parkingOwnerId || !entryTime || !exitTime) {
        alert("Please select a parking area and enter all details.");
        return;
    }

    let bookingData = {
        parking_owner_id: parkingOwnerId,
        vehicle_owner_id: vehicleOwnerId,
        entry_time: entryTime,
        exit_time: exitTime,
        vehicle_type: vehicleType
    };

    console.log("🚀 Sending Booking Data:", bookingData);

    fetch("backend/book_parking.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bookingData)
    })
    .then(response => response.json())
    .then(data => {
        console.log("🔍 Server Response:", data);

        if (data.success) {
            alert("Booking successful! Proceed to payment.");
            window.location.href = "payment.html?cost=" + data.amount;
        } else {
            alert("Booking failed: " + data.message);
        }
    })
    .catch(error => console.error("❌ Error booking slot:", error));
}







