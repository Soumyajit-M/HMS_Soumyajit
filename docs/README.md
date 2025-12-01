# Hospital Management System (HMS)

A comprehensive web-based Hospital Management System built with PHP, MySQL, and modern web technologies.

## Features

### 🏥 Core Modules
- **Patient Management** - Complete patient registration, medical history, and profile management
- **Doctor Management** - Doctor profiles, specializations, schedules, and availability
- **Appointment Scheduling** - Book, manage, and track patient appointments
- **Billing System** - Generate bills, track payments, and manage financial records
- **Medical Records** - Digital medical records with diagnosis and treatment tracking
- **Reports & Analytics** - Comprehensive reporting with charts and data visualization

### 🤖 AI Assistant
- **Intelligent Chat Interface** - AI-powered assistant for hospital management queries
- **Data Analysis** - Automated insights and recommendations
- **Predictive Analytics** - Patient trends and appointment optimization

### 📊 Dashboard & Analytics
- **Real-time Statistics** - Live dashboard with key metrics
- **Interactive Charts** - Visual representation of hospital data
- **Custom Reports** - Generate and export reports in multiple formats

### 🔐 Security & Authentication
- **Role-based Access Control** - Admin, Doctor, Nurse, Receptionist roles
- **Secure Authentication** - Password hashing and session management
- **Data Encryption** - Secure data transmission and storage

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Bootstrap 5.3
- **Charts**: Chart.js
- **Icons**: Font Awesome 6.0
- **AI Integration**: OpenAI API

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependencies)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/hms-project.git
   cd hms-project
   ```

2. **Database Setup**
   - Create a MySQL database named `hms_database`
   - Import the database schema:
   ```bash
   mysql -u username -p hms_database < database/schema.sql
   ```

3. **Configuration**
   - Update `config/config.php` with your database credentials
   - Set your site URL and other configuration options
   - Configure email settings for notifications

4. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure mod_rewrite is enabled (for Apache)
   - Set proper file permissions

5. **Default Login**
   - Username: `admin`
   - Password: `password`

## Project Structure

```
hms_project/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   ├── dashboard.js       # Dashboard functionality
│   │   ├── patients.js        # Patient management
│   │   ├── appointments.js    # Appointment scheduling
│   │   ├── billing.js         # Billing system
│   │   ├── ai-assistant.js    # AI assistant
│   │   ├── reports.js         # Reports & analytics
│   │   ├── doctors.js         # Doctor management
│   │   └── settings.js        # System settings
│   └── images/
│       └── default-avatar.png # Default user avatar
├── classes/
│   ├── Auth.php              # Authentication class
│   ├── Patient.php           # Patient management
│   ├── Doctor.php            # Doctor management
│   ├── Appointment.php       # Appointment handling
│   ├── Billing.php           # Billing system
│   ├── Dashboard.php         # Dashboard statistics
│   ├── AIAssistant.php       # AI integration
│   └── PDFReport.php         # PDF generation
├── config/
│   ├── config.php            # Main configuration
│   └── database.php          # Database connection
├── database/
│   └── schema.sql            # Database schema
├── api/
│   ├── patients.php          # Patient API endpoints
│   ├── doctors.php           # Doctor API endpoints
│   ├── appointments.php      # Appointment API endpoints
│   ├── billing.php           # Billing API endpoints
│   ├── ai-assistant.php      # AI assistant API
│   ├── dashboard-stats.php   # Dashboard statistics API
│   ├── notifications.php    # Notifications API
│   ├── payments.php          # Payment processing API
│   └── settings.php          # Settings management API
├── index.php                 # Login page
├── dashboard.php             # Main dashboard
├── patients.php              # Patient management page
├── doctors.php               # Doctor management page
├── appointments.php          # Appointment scheduling page
├── billing.php               # Billing management page
├── reports.php               # Reports & analytics page
├── ai-assistant.php          # AI assistant page
├── settings.php              # System settings page
└── logout.php                # Logout handler
```

## Usage

### Getting Started
1. Access the system through your web browser
2. Login with the default admin credentials
3. Explore the dashboard to understand the system overview
4. Navigate through different modules using the sidebar

### Key Features Usage

#### Patient Management
- Add new patients with complete medical information
- Search and filter patients by various criteria
- View patient medical history and appointments
- Update patient information as needed

#### Appointment Scheduling
- Schedule appointments between patients and doctors
- View appointment calendar and availability
- Manage appointment status (scheduled, confirmed, completed, cancelled)
- Send appointment reminders

#### Billing System
- Generate bills for services and treatments
- Track payment status and outstanding amounts
- Process payments through multiple methods
- Generate financial reports

#### AI Assistant
- Ask questions about hospital operations
- Get insights on patient trends and statistics
- Receive recommendations for system optimization
- Analyze data patterns and suggest improvements

## Configuration

### Database Configuration
Update `config/database.php` with your database credentials:
```php
private $host = 'localhost';
private $db_name = 'hms_database';
private $username = 'your_username';
private $password = 'your_password';
```

### Email Configuration
Configure SMTP settings in `config/config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
```

### AI Assistant Configuration
Set up OpenAI API key in `config/config.php`:
```php
define('AI_API_KEY', 'your_openai_api_key_here');
define('AI_MODEL', 'gpt-3.5-turbo');
```

## Security Features

- **Password Hashing**: All passwords are securely hashed using PHP's password_hash()
- **SQL Injection Prevention**: All database queries use prepared statements
- **XSS Protection**: User input is properly sanitized and escaped
- **Session Security**: Secure session management with proper timeouts
- **CSRF Protection**: Cross-site request forgery protection implemented

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:
- Create an issue in the GitHub repository
- Contact the development team
- Check the documentation for common issues

## Roadmap

- [ ] Mobile app development
- [ ] Advanced AI features
- [ ] Integration with medical devices
- [ ] Telemedicine capabilities
- [ ] Multi-language support
- [ ] Advanced reporting features

## Changelog

### Version 1.0.0
- Initial release
- Core hospital management features
- AI assistant integration
- Comprehensive reporting system
- Modern responsive UI

---

**Note**: This is a demo project for educational purposes. For production use, ensure proper security measures and compliance with healthcare regulations.
