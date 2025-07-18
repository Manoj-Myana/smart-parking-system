# smart-parking-system
# 🚗 Smart Parking System

A web-based Smart Parking System that allows vehicle owners to search, book, and pay for parking slots, while parking owners can manage their slots and bookings. Built using core web technologies like HTML, CSS, JavaScript, PHP, and MySQL.

---

## 🔧 Technologies Used

| Layer         | Technology              | Why Used?                                                                 |
|---------------|--------------------------|--------------------------------------------------------------------------|
| Frontend      | HTML, CSS, JavaScript   | Simple, fast, and customizable for static UI and dynamic interactions    |
| Backend       | PHP                     | Easy integration with MySQL and rapid server-side scripting              |
| Database      | MySQL                   | Reliable, open-source relational database with good PHP support          |
| Server        | XAMPP (Apache + MySQL)  | Easy local development environment with built-in Apache and MySQL        |
| QR Code       | phpqrcode Library       | To generate booking-specific QR codes for entry/exit                     |
| Authentication| PHP Session             | Session-based login system for secure access for users and owners        |

---

## 🧠 Why Not React / Next.js?

This project focuses on lightweight deployment and easier backend integration, which is why we chose:
- ✅ PHP instead of Node/React for easy MySQL connectivity
- ✅ Simple JS/HTML instead of React to reduce complexity and dependencies
- Ideal for hackathons, small teams, and fast implementation

---

## 📁 Folder Structure
smart-parking-system/
├── assets/
│ ├── script.js # Client-side JavaScript logic
│ └── styles.css # UI styles
├── backend/
│ ├── login.php # Login processing
│ ├── register.php # Registration logic
│ ├── create_booking.php# Booking creation logic
│ ├── fetch_slots.php # Fetch available slots by area
│ ├── generate_qr.php # QR code generation
│ └── ... # Other server-side handlers
├── index.html # Login page
├── register.html # Registration page
├── search_parking.html # Slot search & booking
├── payment.html # Payment confirmation + QR code
├── dashboard.html # Admin dashboard
├── qr_scan.html # QR scanning logic
├── smart_parking.sql # Database file
└── README.md # This file


---

## 🔐 Authentication

- Two types of users: **Vehicle Owners** and **Parking Owners**
- PHP session used to manage login sessions
- On successful login, relevant `owner_id` or `vehicle_owner_id` is stored using `localStorage`

---

## 📲 Features

- 🔍 **Search Parking** by area
- 🅿️ **Book a Slot** with custom entry/exit times
- 💰 **Payment Processing**
- 🧾 **QR Code Generation** (contains booking_id)
- 📱 **QR Scan System** to allow vehicle entry/exit
- 📊 **Dashboard** to view total/available slots

---

## 🖥️ Running the Project (Localhost Setup)

1. **Install XAMPP** (Apache + MySQL)
2. Place the folder in:  
   `C:\xampp\htdocs\smart-parking-system\`
3. Import `smart_parking.sql` into phpMyAdmin
4. Start **Apache** and **MySQL** in XAMPP Control Panel
5. Open your browser and visit:  
   `http://localhost/smart-parking-system/index.html`

---

## 📌 Future Enhancements

- 📧 Email or SMS notifications
- 📍 GPS-based real-time parking tracking
- 📱 Mobile-friendly interface
- 🔒 Role-based admin authentication
- 💬 Feedback and rating system for parking slots

---

## 📷 Screenshots

<img width="1920" height="1200" alt="Screenshot 2025-05-01 215009" src="https://github.com/user-attachments/assets/4cf8f747-d7d8-4c4d-acb4-3527aa3aa9f1" />


---

## 📜 License

This project is for academic/demo purposes. You are free to modify and improve it for personal or educational use.

---

## 🤝 Contributing

Pull requests and feedback are welcome! If you spot any issues or bugs, feel free to open an issue or submit a fix.

---

## 🌐 Live Deployment (Optional)

To host this project online:
- Use platforms like **InfinityFree**, **000WebHost**, or **Vercel + Backend on Railway**
- Configure `.htaccess` if needed
- Set up MySQL credentials in `config.php`

---

### 👨‍💻 Built by:
**Myana Manoj Kumar**  
Computer Science & Engineering Student  
[Vignana Bharathi Institute of Technology]

---






