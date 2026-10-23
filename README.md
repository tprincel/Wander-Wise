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

```text
wanderwise/
├── index.html                   # Main landing page & attractions showcase
├── dashboard.html               # Tour packages catalog, filters & booking modal
├── Explorethelandofwonders.html # Interactive Leaflet.js map with destination pins
├── login.html                   # User login & registration portal
├── planmytrip.html              # Trip planning entry point
├── form.html                    # Trip discovery & search inquiry form
├── bill.html                    # Booking confirmation invoice
├── config/
│   └── db.php                   # Database connection configuration (PDO)
├── api/
│   ├── get_places.php           # Fetch destinations with category/search filters
│   ├── book_place.php           # Booking & payment transaction endpoint
│   ├── auth.php                 # User registration, login & session handling
│   └── contact.php              # Inquiries & message submissions
├── db.php                       # Database connection configuration forwarder
├── packages.php                 # RESTful API endpoint forwarder
├── save_booking.php             # Booking submission forwarder
├── auth_login.php               # User authentication forwarder
├── save_inquiry.php             # Trip search leads forwarder
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
5. This creates the database `wanderwise_db` with all necessary tables (`users`, `places`, `bookings`, `payments`, `inquiries`) and seeds default tour packages.

### 2. Deploy to XAMPP

Copy the project folder to your XAMPP web root:

```text
C:\xampp\htdocs\wanderwise
```

---

## Project Links

- **Live Demo (SnapDeploy):** [https://wander-wise-036c8.containers.snapdeploy.app](https://wander-wise-036c8.containers.snapdeploy.app)
- **Local Frontend / Website:** [http://localhost/wanderwise/](http://localhost/wanderwise/)
- **Local Backend / Database (phpMyAdmin):** [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)

---

## Backend API Endpoints

| Endpoint | Method | Description |
| --- | --- | --- |
| `api/get_places.php` | `GET` | Fetches destinations with optional `category`, `state`, `search`, and `duration` filters |
| `api/book_place.php` | `POST` | Validates, creates user if needed, and records in `bookings` and `payments` |
| `api/auth.php` | `POST` / `GET` | User registration and login using `password_verify` with session tracking |
| `api/contact.php` | `POST` | Records user messages and inquiries into `inquiries` table |
| `packages.php` | `GET` | Legacy backward-compatible forwarder to `api/get_places.php` |
| `save_booking.php` | `POST` | Legacy backward-compatible forwarder to `api/book_place.php` |
| `auth_login.php` | `POST` | Legacy backward-compatible forwarder to `api/auth.php` |
| `save_inquiry.php` | `POST` | Legacy backward-compatible forwarder to `api/contact.php` |

---

## License

MIT License.
