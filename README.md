# 📚 Attendance Management System

A complete web-based attendance management system for schools and colleges to track, manage, and report student attendance.

## ✨ Features

- **Student Registration**: Add and manage student information
- **Mark Attendance**: Daily attendance marking by subject/course
- **View Records**: Complete attendance history with filters
- **Reports & Analytics**: Attendance percentage, statistics, and summaries
- **Responsive Design**: Works on desktop, tablet, and mobile devices

## 🚀 Quick Start

### Option 1: Standalone HTML (No Installation)

1. Open `index.html` in any web browser
2. Start adding students and marking attendance
3. Data is stored in browser memory

### Option 2: With PHP & MySQL Database

1. **Install XAMPP**
   - Download from: https://www.apachefriends.org/
   - Install and start Apache & MySQL services

2. **Set Up Database**
   - Open http://localhost/phpmyadmin
   - Create database: `attendance_management`
   - Import `database/schema.sql`

3. **Configure Project**
   - Copy all files to `C:\xampp\htdocs\attendance-system\`
   - Edit `php/config.php` with your database credentials
   - Open http://localhost/attendance-system/

## 📋 Requirements

### Standalone Version
- Any modern web browser (Chrome, Firefox, Edge, Safari)

### PHP + MySQL Version
- XAMPP/WAMP/LAMP
- PHP 7.4 or higher
- MySQL 5.7 or higher

## 🎯 How to Use

### Step 1: Register Students
1. Click on "Students" tab
2. Fill in student details (Roll Number, Name, Class, Section)
3. Click "Add Student"

### Step 2: Mark Attendance
1. Go to "Mark Attendance" tab
2. Select Date, Class, and Subject
3. Click "Load Students"
4. Mark attendance status for each student
5. Click "Submit Attendance"

### Step 3: View Records
- Click "View Records" to see all attendance entries
- Records show date, student, course, and status

### Step 4: Generate Reports
- Click "Reports" tab to view:
  - Total students count
  - Today's present/absent count
  - Overall attendance percentage
  - Subject-wise attendance for each student

## 📊 Database Schema

### Tables
- `students` - Student information
- `courses` - Subject/Course details
- `attendance` - Daily attendance records

## 🔧 Configuration

Edit `php/config.php`:
```php
$host = "localhost";
$username = "root";
$password = "";
$database = "attendance_management";
```

## 📱 Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

## 🐛 Troubleshooting

**Problem**: Data disappears after page refresh
**Solution**: Use the PHP + MySQL version for persistent storage

**Problem**: Can't connect to database
**Solution**: Check if MySQL service is running in XAMPP

**Problem**: Students not loading for attendance
**Solution**: Make sure students are registered with the correct class

## 📄 License

This project is open source and available for educational purposes.

## 👨‍💻 Author

Created for school/college attendance management

## 📞 Support

For issues and questions, please check the documentation in the `docs/` folder.

---

**Version**: 1.0.0  
**Last Updated**: 9 Nov 2025
