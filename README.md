# 🎮 Games Store - Steam-Like Platform

A full-featured online game store platform built with PHP and MySQL, inspired by Steam. This web application allows users to browse, purchase, and manage their game library while providing administrators with powerful management tools.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## ✨ Features

### 👤 User Features
- **Authentication System**
  - User registration and login
  - Secure password hashing (bcrypt)
  - "Remember Me" functionality with secure token-based sessions
  - Automatic session refresh (1-hour duration)
  - Profile management with avatar upload

- **Game Browsing & Discovery**
  - Browse games catalog with pagination
  - Advanced search functionality (title, description, developer)
  - Filter by category, price range, discounts, featured games
  - Sort by various criteria (date, price, popularity)
  - Detailed game pages with screenshots and descriptions

- **Shopping Experience**
  - Add games to shopping cart
  - Wishlist management
  - Secure checkout process
  - Purchase history tracking

- **Personal Library**
  - View all owned games
  - Access purchased games anytime
  - Library management

### 🔧 Admin Features
- **Dashboard**
  - Real-time statistics (users, games, sales, revenue)
  - Recent sales tracking
  - Top-selling games analytics
  - New user registrations overview

- **Game Management**
  - Add, edit, and delete games
  - Set prices and discounts
  - Upload game images and banners
  - Manage game categories
  - Feature games on homepage

- **User Management**
  - View all registered users
  - Manage user roles (admin/user)
  - Monitor user activity

- **Category Management**
  - Create and organize game categories
  - Assign games to categories

- **Order Management**
  - View all purchases
  - Track sales history
  - Monitor revenue

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome
- **Security**: 
  - PDO with prepared statements (SQL injection prevention)
  - CSRF token protection
  - Password hashing (bcrypt)
  - XSS prevention (HTML escaping)
  - Secure session management

## 📁 Project Structure

```
games-store/
├── admin/                      # Admin panel pages
│   ├── index.php              # Admin dashboard
│   ├── games.php              # Game management
│   ├── users.php              # User management
│   ├── categories.php         # Category management
│   └── orders.php             # Order management
├── api/                       # REST API endpoints
│   ├── auth.php              # Authentication API
│   ├── auth_bootstrap.php    # Auth initialization
│   ├── games.php             # Games API
│   └── users.php             # Users API
├── assets/                    # Static assets
│   ├── images/
│   │   ├── avatars/          # User avatars
│   │   ├── banners/          # Game banners
│   │   └── [game-images]     # Game cover images
│   └── Banière.PNG           # Site banner
├── config/                    # Configuration files
│   └── database.php          # Database connection
├── css/                       # Stylesheets
│   ├── style.css             # Global styles
│   ├── index.css             # Homepage styles
│   ├── store.css             # Store page styles
│   ├── cart.css              # Shopping cart styles
│   ├── dashboard.css         # Admin dashboard styles
│   └── [other-styles]        # Page-specific styles
├── includes/                  # Reusable components
│   ├── header.php            # Site header
│   ├── footer.php            # Site footer
│   └── functions.php         # Utility functions
├── pages/                     # Frontend pages
│   ├── store.php             # Game catalog
│   ├── game-detail.php       # Individual game page
│   ├── cart.php              # Shopping cart
│   ├── wishlist.php          # User wishlist
│   ├── library.php           # User's game library
│   ├── profile.php           # User profile
│   ├── login.php             # Login page
│   └── register.php          # Registration page
├── .git/                      # Git repository
└── index.php                  # Main landing page
```

## 🚀 Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- Apache/Nginx web server with mod_rewrite enabled
- Composer (optional, for dependencies)

### Step 1: Clone or Download

```bash
# Clone the repository
git clone https://github.com/yourusername/games-store.git

# Or download and extract the ZIP file
cd games-store
```

### Step 2: Database Setup

1. Create a new MySQL database:

```sql
CREATE DATABASE games_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Create the required tables:

```sql
USE games_store;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    role ENUM('user', 'admin') DEFAULT 'user',
    remember_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Games table
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) NULL,
    image VARCHAR(255),
    banner_image VARCHAR(255),
    developer VARCHAR(100),
    publisher VARCHAR(100),
    release_date DATE,
    category_id INT,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Purchases table
CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    purchase_price DECIMAL(10, 2) NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    UNIQUE KEY unique_purchase (user_id, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlists table
CREATE TABLE wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

3. Create an admin user (replace password with your desired password):

```sql
-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@gamesstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

### Step 3: Configuration

Edit `config/database.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'games_store');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
```

### Step 4: Web Server Configuration

#### Apache (.htaccess)

Create `.htaccess` in the root directory:

```apache
RewriteEngine On
RewriteBase /

# Redirect to HTTPS (optional)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove .php extension
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteRule ^(.*)$ $1.php [L]

# Prevent direct access to includes
RewriteRule ^(includes|config)/ - [F,L]
```

#### Nginx

Add to your server block:

```nginx
location / {
    try_files $uri $uri/ $uri.php?$query_string;
}

location ~ ^/(includes|config)/ {
    deny all;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### Step 5: Set Permissions

```bash
# Make assets directory writable
chmod -R 755 assets/
chmod -R 777 assets/images/avatars/
```

### Step 6: Launch

```bash
# Using PHP built-in server (development only)
php -S localhost:8000

# Or configure your Apache/Nginx virtual host
```

Visit `http://localhost:8000` (or your configured domain)

## 🔐 Default Credentials

**Admin Account:**
- Email: `admin@gamesstore.com`
- Password: `admin123`

⚠️ **Important**: Change the default admin password immediately after first login!

## 📖 Usage

### For Users

1. **Register**: Create a new account at `/pages/register.php`
2. **Browse**: Explore games at `/pages/store.php`
3. **Search**: Use filters and search to find games
4. **Purchase**: Add games to cart and checkout
5. **Library**: Access purchased games at `/pages/library.php`
6. **Profile**: Manage your profile at `/pages/profile.php`

### For Administrators

1. **Login**: Use admin credentials
2. **Dashboard**: Access admin panel at `/admin/index.php`
3. **Manage Games**: Add/edit/delete games at `/admin/games.php`
4. **Manage Users**: View and manage users at `/admin/users.php`
5. **View Orders**: Track sales at `/admin/orders.php`
6. **Categories**: Organize games at `/admin/categories.php`

## 🔒 Security Features

- **SQL Injection Prevention**: PDO with prepared statements
- **XSS Protection**: HTML escaping for all user inputs
- **CSRF Protection**: Token-based validation for forms
- **Password Security**: Bcrypt hashing with salt
- **Session Security**: 
  - Secure token-based "Remember Me"
  - Automatic session regeneration
  - 1-hour token expiration
- **Role-Based Access Control**: Admin/User separation
- **Input Validation**: Server-side validation for all forms

## 🌐 API Endpoints

### Authentication
- `POST /api/auth.php?action=login` - User login
- `POST /api/auth.php?action=register` - User registration
- `POST /api/auth.php?action=logout` - User logout
- `GET /api/auth.php?action=check` - Check authentication status

### Games
- `GET /api/games.php` - List all games
- `GET /api/games.php?id={id}` - Get game details
- `POST /api/games.php` - Add new game (admin only)
- `PUT /api/games.php?id={id}` - Update game (admin only)
- `DELETE /api/games.php?id={id}` - Delete game (admin only)

### Users
- `GET /api/users.php` - List all users (admin only)
- `GET /api/users.php?id={id}` - Get user details
- `PUT /api/users.php?id={id}` - Update user profile

## 🎨 Customization

### Adding Games
1. Login as admin
2. Go to Admin Dashboard → Games
3. Click "Add New Game"
4. Fill in game details
5. Upload cover image and banner
6. Set price and discount (optional)
7. Select category and save

### Styling
- Global styles: `css/style.css`
- Page-specific styles in `css/` directory
- Modify header/footer in `includes/` directory

## 🚧 Future Enhancements

- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] User reviews and ratings
- [ ] Game recommendations algorithm
- [ ] Social features (friends, sharing)
- [ ] Email notifications
- [ ] Download management for purchased games
- [ ] Refund system
- [ ] Coupon/promo code system
- [ ] Multi-language support
- [ ] Dark mode theme
- [ ] Advanced analytics dashboard
- [ ] Game screenshots gallery
- [ ] Forum/Community section

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error:**
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database name exists

**Images Not Loading:**
- Verify file permissions on `assets/images/`
- Check image paths in database
- Ensure images exist in correct directories

**Session Issues:**
- Clear browser cookies
- Check PHP session settings in `php.ini`
- Verify `session_start()` is called

**Admin Access Denied:**
- Verify user role is set to 'admin' in database
- Check `$_SESSION['user_role']` value
- Re-login after role changes

## 📄 License

This project is licensed under the MIT License - feel free to use it for personal or commercial projects.

## 👨‍💻 Credits

Developed with ❤️ using PHP and MySQL

---

**Need Help?** Open an issue or contact support.

**Contribute:** Pull requests are welcome!
