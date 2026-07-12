# School Management System — Requirements Document

## Introduction
This document outlines the requirements for a School Management System. The system will consist of the following 5 core modules:
1. Student Information
2. Teacher Information
3. Student Result
4. Student Tuition Fee
5. Notice Board
6. admin login

---

## 1. Student Information Module

### 1.1 Data Fields
- Student ID (Auto-generated unique ID)
- Full Name (English & native language)
- Date of Birth
- Gender
- Parent/Guardian Name (Father/Mother/Guardian)
- Parent/Guardian Mobile Number
- Permanent & Current Address
- Class & Section
- Roll Number
- Admission Date
- Blood Group
- Photo Upload
- Emergency Contact Number

### 1.2 Features
- New student registration (Add)
- Edit & update student information
- View student list by class/section
- Search & filter (by name, ID, class)
- Print/export student profile as PDF
- Generate student ID card
- Issue Transfer Certificate (TC)

---

## 2. Teacher Information Module

### 2.1 Data Fields
- Teacher ID
- Full Name
- Date of Birth
- Mobile Number & Email
- Academic Qualifications (Certificates)
- Subjects & Classes Taught
- Date of Joining
- Designation
- Salary Structure
- Address
- Photo Upload

### 2.2 Features
- Add new teacher
- Edit/update teacher information
- Subject & class assignment
- Teacher attendance tracking (optional)
- Search/filter teacher list
- Print teacher profile

---

## 3. Student Result Module

### 3.1 Data Fields
- Exam Name (e.g., First Term, Annual Exam)
- Class & Section
- Subject-wise Marks Entry
- Pass/Fail Status
- GPA/Grade Calculation
- Merit Position
- Remarks/Comments

### 3.2 Features
- Subject-wise marks entry (by teacher)
- Automatic GPA/grade calculation
- Generate & print mark sheet/result card
- Class-wise result summary/tabulation sheet
- View past result history
- Result access for parents (SMS/portal)

---

## 4. Student Tuition Fee Module

### 4.1 Data Fields
- Fee Structure (class-wise monthly/yearly fee)
- Payment History
- Due Amount
- Payment Date & Method (cash/bank/mobile banking)
- Receipt Number
- Discount/Scholarship Information

### 4.2 Features
- Generate monthly fee
- Fee collection entry
- Auto receipt generation & printing
- Due fee report
- SMS/notification for parents (payment reminders)
- Monthly/yearly fee collection report

---

## 5. Notice Board Module

### 5.1 Purpose
This module allows the school admin to publish notices (exam schedules, holidays, events, announcements, etc.) which will automatically appear on the school's public frontend/website for students, parents, and visitors to see.

### 5.2 Data Fields
- Notice ID
- Notice Title
- Notice Description/Details
- Category (Exam, Holiday, Event, General, Admission, Urgent, etc.)
- Attachment (PDF/Image, optional)
- Publish Date
- Expiry Date (auto-hide after this date, optional)
- Target Audience (All / Specific Class / Teachers Only / Parents Only)
- Posted By (Admin/Staff name)
- Status (Active/Inactive/Draft)

### 5.3 Features
- Admin panel and admin login








