# Teaching Assistant Management System Installation Guide

## System Requirements

### Operating Systems
- Windows 11 or
- MacOS

### Development Tools
- PHP Version 8 or higher
- Laravel Framework Version 10.44.0
- Visual Studio Code
- Xampp or Laragon
- Composer
- Node.js and NPM

## Installation Process for Developers

### Installing Xampp
1. Download Xampp from https://www.apachefriends.org/download.html (PHP Version 8+)
2. Complete the installation, selecting your preferred location
3. Verify installation:
   - Open Xampp
   - Start Apache and MySQL
   - Run `php -v` in Command Prompt to check PHP version

### Installing Composer
1. Visit https://getcomposer.org/download/
2. Download and install Composer following the instructions
3. Verify installation

### Installing the Teaching Assistant Management System

#### Download Source Code
```
git clone git@github.com:sndlk01/CS2567_59_FinalProjects.git
```

#### System Setup
1. Navigate to the project folder:
   ```
   cd CS2567_59_FinalProjects
   ```

2. Install PHP Dependencies:
   ```
   composer install
   ```

3. Install Node.js Dependencies:
   ```
   npm install
   ```

4. Copy .env.example to .env:
   ```
   cp .env.example .env
   ```

5. Generate Application Key:
   ```
   php artisan key:generate
   ```

6. Configure database settings in the .env file

7. Migrate the database:
   ```
   php artisan migrate
   ```

8. Start the development server:
   ```
   php artisan serve
   ```

## About the System

This Teaching Assistant Management System was developed to address challenges faced by the College of Computing in managing numerous teaching assistants. It replaces the previous workflow that relied on Microsoft Excel, Google Forms, and manual verification, which caused delays and data entry errors.

The system improves efficiency for staff, saves time for teaching assistants, and enables effective monitoring of teaching assistant work.

## Test Users

The system has three user roles:
* Admin (Email: admin@admin.com) Password: 12345678
* Teacher (Email: pusadee@kku.ac.th) Password: 1234
* Student/TA (Email: chakit.p@kkumail.com) Password: 12345678

## Deployed Website
https://cs592567.cpkkuhost.com/
