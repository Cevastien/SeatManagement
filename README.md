# 🍽️ Digital Waitlist System for Restaurants

A comprehensive Laravel-based digital waitlist management system designed for restaurants to efficiently manage customer queues, prioritize special guests, and provide real-time queue updates.

## ✨ Features

- **📱 Touch-Friendly Kiosk Interface** - Modern, responsive design for easy customer interaction
- **🎯 Priority Queue Management** - Special handling for Senior Citizens, PWD, and Pregnant guests
- **🆔 ID Verification System** - Secure verification workflow for priority guests
- **📊 Real-Time Queue Updates** - Live queue position and estimated wait time
- **🍽️ Table Suggestion System** - Smart table recommendations based on availability
- **📱 SMS Notifications** - Customer notifications when table is ready
- **👥 Admin Dashboard** - Staff management and queue monitoring
- **⚙️ Dynamic Settings** - Configurable party size limits and restaurant details
- **📋 Terms & Conditions** - Legal compliance with consent tracking
- **🔄 Session Management** - Secure session handling with timeout protection

## 🛠️ Technology Stack

- **Backend:** Laravel 11.x
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Database:** SQLite (development), MySQL/PostgreSQL (production)
- **Icons:** Font Awesome 6
- **Styling:** Custom CSS with modern animations

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2+** with extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- **Composer** (PHP dependency manager)
- **Node.js 18+** and **NPM**
- **Git**
- **Web Server** (Apache/Nginx) or **Laravel Valet/Sail**

## 🚀 Installation Guide

### Step 1: Clone the Repository

```bash
git clone https://github.com/Cevastien/SeatManagement.git
cd SeatManagement
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install Node.js Dependencies

```bash
npm install
```

### Step 4: Environment Configuration

```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Database

Edit your `.env` file and configure the database:

```env
# For SQLite (Development - Default)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# For MySQL (Production)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=seatmanagement
# DB_USERNAME=your_username
# DB_PASSWORD=your_password
```

**If using SQLite:** Ensure the database file exists:
```bash
touch database/database.sqlite
```

### Step 6: Run Database Migrations

```bash
php artisan migrate
```

### Step 7: Seed the Database

```bash
php artisan db:seed
```

This will populate your database with:
- Default settings (party size limits, restaurant info)
- Sample staff accounts
- Test data for development

### Step 8: Build Frontend Assets

```bash
# Build for development
npm run dev

# Or build for production
npm run build
```

### Step 9: Start the Development Server

```bash
php artisan serve
```

Your application will be available at: **http://127.0.0.1:8000**

## 🔧 Configuration

### Default Admin Account

After seeding, you can log in with:
- **Email:** `admin@restaurant.com`
- **Password:** `password`

### Key Settings

Configure these in the admin panel or database:

- **Party Size Limits:** Minimum and maximum party size (default: 1-50)
- **Restaurant Information:** Name, address, phone number
- **Queue Settings:** Average dining duration, grace period
- **Table Settings:** Table suggestion time window

## 📱 Usage Guide

### For Customers (Kiosk Mode)

1. **Welcome Screen:** Touch anywhere to start registration
2. **Registration:** Enter name, party size, contact number
3. **Priority Selection:** Choose if you're a priority guest (Senior/PWD/Pregnant)
4. **ID Verification:** If priority, complete verification process
5. **Queue Position:** Get your queue number and estimated wait time
6. **Notifications:** Receive SMS when table is ready

### For Staff (Admin Dashboard)

1. **Login:** Use admin credentials to access dashboard
2. **Queue Management:** View current queue and customer details
3. **Priority Verification:** Process ID verification requests
4. **Settings Management:** Configure system parameters
5. **Analytics:** Monitor queue performance and customer flow

## 🗂️ Project Structure

```
SeatManagement/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Http/Controllers/     # API and web controllers
│   ├── Jobs/                 # Background job processing
│   ├── Livewire/             # Livewire components
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   └── View/Components/      # Blade components
├── database/
│   ├── migrations/           # Database schema migrations
│   └── seeders/             # Database seeders
├── public/
│   ├── images/              # Restaurant images and logos
│   └── js/                  # Frontend JavaScript
├── resources/
│   ├── views/
│   │   ├── admin/           # Admin dashboard views
│   │   ├── kiosk/           # Customer kiosk views
│   │   └── components/      # Reusable Blade components
│   └── css/                 # Custom stylesheets
└── routes/
    ├── api.php              # API routes
    └── web.php              # Web routes
```

## 🔑 Key Routes

- **Kiosk:** `/` - Customer registration interface
- **Admin:** `/admin` - Staff dashboard
- **API:** `/api/*` - REST API endpoints
- **Settings:** `/admin/settings` - System configuration

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/KioskTest.php
```

## 📦 Production Deployment

### 1. Environment Setup

```bash
# Set environment to production
APP_ENV=production
APP_DEBUG=false

# Configure database for production
DB_CONNECTION=mysql
DB_HOST=your_production_host
DB_DATABASE=your_production_db
DB_USERNAME=your_production_user
DB_PASSWORD=your_production_password
```

### 2. Optimize for Production

```bash
# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build production assets
npm run build

# Run migrations
php artisan migrate --force
```

### 3. Web Server Configuration

Configure your web server to point to the `public` directory:

```apache
# Apache .htaccess (already included)
DocumentRoot /path/to/SeatManagement/public
```

```nginx
# Nginx configuration
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/SeatManagement/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🐛 Troubleshooting

### Common Issues

1. **Permission Errors:**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

2. **Database Connection Issues:**
   - Verify database credentials in `.env`
   - Ensure database server is running
   - Check database exists

3. **Asset Loading Issues:**
   ```bash
   npm run build
   php artisan config:clear
   ```

4. **Queue Not Updating:**
   - Check if JavaScript is enabled
   - Verify API routes are accessible
   - Check browser console for errors

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -am 'Add new feature'`
4. Push to branch: `git push origin feature/new-feature`
5. Submit a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👨‍💻 Author

**Cevastien** - [GitHub Profile](https://github.com/Cevastien)

## 📞 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/Cevastien/SeatManagement/issues) page
2. Create a new issue with detailed information
3. Include error messages and steps to reproduce

---

**🎯 Ready to streamline your restaurant's waitlist management? Get started with the installation guide above!**
