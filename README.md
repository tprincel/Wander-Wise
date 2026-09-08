# Wander Wise

Wander Wise is a full-featured travel exploration and tour booking web application built with HTML, CSS, JavaScript, PHP, and MySQL.

---

## Features

- **Interactive Destinations Map**: Explore iconic tourist spots across Indian states using an interactive Leaflet.js map.
- **Curated Attractions Carousel**: Dynamic showcase of popular tourist attractions with links to detailed guides.
- **Tour Packages Dashboard**: Browse and filter cultural, adventure, wildlife, beach, and religious tour packages by state and duration.
- **Custom Package Builder**: Create tailored vacation packages based on selected destination, duration, and group size.
- **Interactive Booking System**: Modal booking flow with automated reference generation, database persistence, and booking invoice generation.
- **User Authentication**: User registration and login persisted across the frontend with dynamic navbar badges and pre-filled customer details.
- **Lead Capture & Inquiries**: Dedicated trip discovery form capturing search leads directly to the database.

---

## Project Structure

```
wanderwise/
├── index.html                   # Main landing page & attractions showcase
├── dashboard.html               # Tour packages catalog, filters & booking modal
├── Explorethelandofwonders.html # Interactive Leaflet.js map with destination pins
├── login.html                   # User login & registration portal
├── planmytrip.html              # Trip planning entry point
├── form.html                    # Trip discovery & search inquiry form
├── bill.html                    # Booking confirmation invoice
├── db.php                       # Database connection configuration (PDO & MySQLi)
├── packages.php                 # RESTful API endpoint for package listings
├── save_booking.php             # POST endpoint for booking submissions
├── auth_login.php               # POST endpoint for user authentication
├── save_inquiry.php             # POST endpoint for trip search leads
├── schema.sql                   # MySQL database schema & seed data
├── images/                      # Destination photographs & logos
└── places/                      # 62 Individual destination guide pages
```

---

## Local Setup with XAMPP

### 1. Database Setup
1. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open your browser and go to: `http://localhost/phpmyadmin`
3. Click the **Import** tab.
4. Select `schema.sql` from this repository and click **Import**.
5. This creates the database `wanderwise_db` with all necessary tables (`users`, `packages`, `bookings`, `inquiries`) and seeds default tour packages.

### 2. Deploy to XAMPP
Copy the project folder to your XAMPP web root:
```
C:\xampp\htdocs\wanderwise
```

### 3. Open in Browser
Visit the project directly at:
```
http://localhost/wanderwise/
```

---

## Backend API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `packages.php` | `GET` | Returns tour packages with optional `state` and `category` filters |
| `save_booking.php` | `POST` | Validates and inserts tour bookings into `wanderwise_db.bookings` |
| `auth_login.php` | `POST` | Authenticates existing user or registers new user in `wanderwise_db.users` |
| `save_inquiry.php` | `POST` | Records travel inquiry leads into `wanderwise_db.inquiries` |

---

## License
MIT License.
