HyperJump - The Personalized Start Page Dashboard

A live demo is available for viewing and testing here:

https://axel.jerabek.fi/hyperjump_demo

Demo-Passwort: testpassword

![Screenshot des HyperJump Dashboards mit Link-Bubbles](assets/dashboard_preview.png)

HyperJump is a simple, highly customizable Single Page Application (SPA) built using PHP, HTML, and CSS. It serves as a central start page or dashboard, offering fast access to all your internal and external links, now fully expandable through a **Plugin System**.

The project is prepared for public release; all private URLs, passwords, and API keys have been removed or anonymized to ensure a secure and public codebase.

---

## 🛠 Technology Stack

Backend/Logic: **PHP** (Minimal and Vanilla)

Frontend/Structure: HTML5

Styling: CSS3 (Focus on responsiveness and modern design)

Interactivity: Vanilla JavaScript

Data Persistence: JSON files (`data.php`, `order.json`)

**Plugin System:** All Widgets/Status Displays are now modular PHP/JS Plugins.

---

## ✨ Key Features

* Categorized Link Bubbles: Links are organized into clear, collapsible categories (Bubbles), with each category having a defined color.
* Instant Accessibility: Optimized for speed, all links are immediately visible upon page load without delay.
* Admin Dashboard: A password-protected administration area (`admin.php`) for managing links.
* Real-time Sorting: Links and categories can be re-sorted via Drag-and-Drop, with the order saved in `order.json`.
* Responsive Design: Optimized for use on desktops, tablets, and especially mobile devices.
* **🔌 Modular Plugin System (New!):** All system status displays and special widgets are now loaded as plugins. **Any folder placed in the `./plugins` directory is automatically loaded and executed on the dashboard.**

---

## 🧩 Plugin Management & Included Demos

The new architecture allows for effortless extension of the dashboard. Simply drop a plugin folder (containing PHP, JS, or CSS files) into the `./plugins/` directory, and it will be available on the dashboard.

Demo plugins included in this repository:

| Plugin Folder | Description | Files Included |
| :--- | :--- | :--- |
| `10-cstatus` | **Critical Status Display:** Displays critical system alerts. | `cstatus.js`, `cstatus_widget.php` |
| `20-network` | **Network Status:** Displays network-related information. | `network.js`, `network_api.php`, `network_widget.php` |
| `cpu` | **CPU Monitor:** Shows current CPU load and core information. | `cpu.js`, `cpu_api.php`, `cpu_widget.php` |
| `nvidia` | **NVIDIA GPU Monitor:** Displays GPU usage and VRAM status. | `nvidia.js`, `nvidia_api.php`, `nvidia_widget.php` |
| `ram` | **RAM Status:** Visualizes current memory usage. | `ram.js`, `ram_api.php`, `ram_widget.php` |
| `weather` | **Weather Forecast Widget** | `weather.js`, `weather.php` |
| `system-widgets` | General system information widgets. | `system_status_widget.php` |

---

## ⚙️ Administration & Data Management

The core of customization is the Admin Dashboard, secured by a password stored as a SHA256 hash in `config.php`.

### File Overview

| File | Description | Note (Public Version) |
| :--- | :--- | :--- |
| `index.php` | The main view/start page. Displays the dashboard and loads plugins. | Fully released. |
| `data.php` | Contains all link categories and associated link data (URL, text, icon, color). | Anonymized. Only placeholder links and text are included. |
| `order.json` | Stores the user-defined order of categories and the links within them. | Anonymized. Stores the structure using placeholder names. |
| `config.php` | Contains session start, login status, and system-wide constants (e.g., password hash, API keys). | Cleaned. Password hash and all API keys have been removed. |
| `admin.php` | The interface for link management (Add, Edit, Delete). | Fully released. |
| `styles2.css` | All CSS styles for the dashboard and the admin panel. | Fully released. |

### Link Structure (`data.php` Format)

Each category has a unique key, a title, a default color, and an array of links.


$categories = [
    'category_key' => [
        'title' => 'Title of the Category',
        'color' => '#HEXCODE',
        'links' => [
            [
                'url' => '[https://example.com](https://example.com)',
                'text' => 'Name of the Link',
                'icon' => 'globe', // Font-Awesome icon name
                'disabled' => false,
                'color' => '#HEXCODE' // Optional individual color
            ],
            // ... more links
        ],
    ],
    // ... more categories
];

🚀 Installation & Operation
Clone the Files: Clone this repository to your web server in the desired directory.

Web Server: Ensure a web server (e.g., Apache, Nginx) with PHP support (recommended: PHP 7.4+) is running.

Configure Access Data (IMPORTANT): Before using the dashboard, you must configure the new config.php file based on config.sample.php.

🔐 Setting the Admin Password
To use the password-protected admin area (admin.php), you must enter a hashed password into config.php. We recommend using strong hashing algorithms like bcrypt.

Generate Hash: Use an online tool or a command-line utility to generate a SHA256 (or better, bcrypt) hash of your desired password.

Configure: Open config.php and insert the hash into the ADMIN_PASSWORD_HASH constant:

PHP

define('ADMIN_PASSWORD_HASH', 'INSERT_THE_GENERATED_HASH_HERE');
☁️ Setting up Weather Functionality
The optional weather feature (used by the weather plugin) requires an API key from OpenWeatherMap.

Request API Key: Register or log in to OpenWeatherMap and generate a new key.

Adjust Configuration: Open the config.php file and insert the key and your city ID:

PHP

define('WEATHER_API_KEY', 'YOUR_OPENWEATHERMAP_API_KEY');
define('WEATHER_CITY_ID', 'YOUR_CITY_ID_HERE'); 
(You can find the City ID by searching for your city on OpenWeatherMap and copying the ID from the browser URL.)

Access Admin Area: Click on "Admin" in the bottom-right widget or visit /admin.php in your browser to log in and replace the placeholder links with your actual ones.
