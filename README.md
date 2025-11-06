HyperJump - The Personalized Start Page Dashboard

A live demo is available for viewing and testing here:

https://axel.jerabek.fi/hyperjump_demo/

Demo-Passwort: jabittetest

![Screenshot des HyperJump Dashboards mit Link-Bubbles](assets/dashboard_preview.png)

HyperJump is a simple, highly customizable Single Page Application (SPA) built using PHP, HTML, and CSS. It serves as a central start page or dashboard to provide quick access to your most important links and internal services. The design emphasizes a clean, modern aesthetic and fast loading times.

The project is prepared for public release; all private URLs, passwords, and API keys have been removed or anonymized to ensure a secure and public codebase.

🛠 Technology Stack

Backend/Logic: PHP (Minimal and Vanilla)

Frontend/Structure: HTML5

Styling: CSS3 (Focus on responsiveness and modern design)

Interactivity: Vanilla JavaScript

Data Persistence: JSON files (data.php, order.json)

✨ Key Features

Categorized Link Bubbles: Links are organized into clear, collapsible categories (Bubbles), with each category having a defined color.

Instant Accessibility: Optimized for speed, all links are immediately visible upon page load without delay.

Admin Dashboard: A password-protected administration area (admin.php) for managing links.

Real-time Sorting: Links and categories can be re-sorted via Drag-and-Drop, with the order saved in order.json.

Responsive Design: Optimized for use on desktops, tablets, and especially mobile devices.

⚙️ Administration & Data Management

The core of customization is the Admin Dashboard, secured by a password stored as a SHA256 hash in config.php.

File Overview

File

Description

Note (Public Version)

index.php

The main view/start page. Displays the dashboard.

Fully released.

data.php

Contains all link categories and associated link data (URL, text, icon, color).

Anonymized. Only placeholder links and text are included.

order.json

Stores the user-defined order of categories and the links within them.

Anonymized. Stores the structure using placeholder names.

config.php

Contains session start, login status, and system-wide constants (e.g., password hash, API keys).

Cleaned. Password hash and all API keys have been removed.

admin.php

The interface for link management (Add, Edit, Delete).

Fully released.

styles2.css

All CSS styles for the dashboard and the admin panel.

Fully released.

Link Structure (data.php Format)

Each category has a unique key, a title, a default color, and an array of links:

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

Configure Access Data (IMPORTANT): Before using the dashboard, you must edit the config.php file.

🔐 3.1 Setting the Admin Password

To use the password-protected admin area (admin.php), you must enter a hashed password into config.php. We use SHA256 to securely store passwords.

Generate Hash: Change the password in hash_generator.php and access the file /hash_generator.php in your browser (e.g., http://localhost/hyperjump/hash_generator.php).

Copy the Hash: Copy the generated SHA256 hash.

Configure: Open config.php and insert the hash into the ADMIN_PASSWORD constant:

define('ADMIN_PASSWORD', 'INSERT_THE_COPIED_HASH_HERE');


☁️ 3.2 Setting up Weather Functionality

The optional weather feature (which uses get_weather.php and weather_cache.json) requires an API key from OpenWeatherMap.

Request API Key:

Register or log in to OpenWeatherMap.

Navigate to My API keys (under your profile) and generate a new key.

Adjust Configuration:

Open the config.php file.

Insert the key into the WEATHER_API_KEY constant and adjust your city ID:

define('WEATHER_API_KEY', 'YOUR_OPENWEATHERMAP_API_KEY');
define('WEATHER_CITY_ID', 'YOUR_CITY_ID_HERE');


You can find the City ID (WEATHER_CITY_ID) by searching for your city on OpenWeatherMap and copying the ID from the browser URL.

Access Admin Area: Click on "Admin" in the bottom-right widget or visit /admin.php in your browser to log in and replace the placeholder links with your actual ones.
