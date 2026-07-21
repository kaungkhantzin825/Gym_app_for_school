# GYM APP - Project Overview

## 📱 Executive Summary

**GYM APP** is a comprehensive AI-powered fitness and nutrition tracking mobile application designed to help users achieve their health and fitness goals through intelligent meal tracking, workout management, and personalized coaching.

### Key Highlights
- **Platform**: Cross-platform mobile app (iOS & Android) built with Flutter
- **Backend**: RESTful API built with Laravel 13 (PHP 8.4+)
- **AI Integration**: Meal photo recognition, workout generation, and personalized coaching with Mistral AI ( Laravel 13 AI SDK)
- **Authentication**: Multi-method (Email) 
- **Real-time**: Push notifications via Firebase Cloud Messaging

---

## 🎯 Project Vision

To create an all-in-one fitness companion that leverages artificial intelligence to simplify nutrition tracking, optimize workout planning, and provide personalized guidance for users at any fitness level.


## 📦 Core Features

### 1. User Management & Authentication
- **Authentication**
  - Email/Password registration and login
  - Password reset functionality
- **User Profiles**
  - Personal information (age, gender, height, weight)
  - Fitness goals (lose weight, gain weight, maintain, build muscle)
  - Activity level tracking
  - Profile photo upload
- **Role-based Access Control**
  - User role (standard access)
  - Admin role (management dashboard)

### 2. Nutrition Tracking
- **Meal Logging**
  - Create meals by type (breakfast, lunch, dinner, snack)
  - AI-powered meal photo analysis
  - Manual food entry with search
  - Meal history and soft delete
- **Food Database Integration**
  - FatSecret API integration
  - Search by food name
  - Barcode scanning support
  - Autocomplete suggestions
  - Brand name recognition
- **Nutritional Analysis**
  - Calorie tracking
  - Macronutrients (protein, carbs, fat)
  - Micronutrients (fiber, sugar, sodium)
  - Daily target monitoring
  - Serving size customization

### 3. Workout Management
- **Exercise Tracking**
  - Multiple exercise types (cardio, strength, flexibility, sports)
  - Duration and calorie tracking
  - Sets, reps, and weight logging
  - Exercise notes and timestamps
- **Structured Workouts**
  - Create workout sessions
  - Track start/end times
  - Set-by-set logging
  - RPE (Rate of Perceived Exertion) tracking
  - Rest time monitoring
  - Superset support
  - Total volume calculation
- **AI Workout Generation**
  - Personalized workout plans
  - Quick workout generation
  - Goal-based recommendations

### 4. Progress Monitoring
- **Weight Tracking**
  - Historical weight logs
  - Progress visualization
  - Notes and context
- **Photo Gallery**
  - Progress photos with dates
  - Before/after comparisons
  - Caption support
- **Analytics Dashboard**
  - Daily/weekly summaries
  - Calorie intake vs. target
  - Workout frequency
  - Weight trends
  - Macro distribution

### 5. Educational Content
- **Tutorial Videos**
  - Exercise demonstrations
  - Gender-specific content
  - Muscle group categorization
  - Video thumbnails
  - Public access (no login required)

### 6. AI Coach Features
- **Meal Photo Analysis**
  - Automatic food recognition
  - Nutritional estimation
  - Multi-food detection
- **Workout Generation**
  - Personalized plans based on goals
  - Equipment availability consideration
  - Fitness level adaptation
- **Chat Assistant** (Placeholder)
  - Fitness advice
  - Nutrition guidance

### 7. Push Notifications
- **Device Registration**
  - FCM token management
  - iOS/Android support
- **Notification Types**
  - Meal reminders
  - Workout reminders
  - Progress updates
  - Admin announcements

### 8. Support System
- **Help & Contact**
  - Submit support tickets
  - View message history
  - Admin reply system
  - Status tracking (pending, read, replied)

### 9. Admin Dashboard
- **User Management**
  - View all users
  - Create/update/delete users
  - Toggle user status
  - User details and statistics
- **Content Management**
  - Manage tutorial videos
  - Moderate user meals/posts
  - Update app settings
- **Analytics & Reports**
  - Generate usage reports
  - Export data
  - Platform statistics
- **App Settings**
  - About page content
  - Privacy policy management

---

## 🔐 Security Features

### Authentication & Authorization
- **Laravel Sanctum** for API token management
- **Password hashing** using bcrypt
- **Role-based access control** (user/admin)
- **Token expiration** and refresh

### Data Protection
- **HTTPS/TLS** encryption for all API communication
- **Input validation** and sanitization
- **SQL injection prevention** via Eloquent ORM
- **XSS protection** built into Laravel
- **CSRF protection** for web routes
- **Rate limiting** on API endpoints

### Privacy
- **Soft deletes** for data recovery
- **User data isolation** via foreign key constraints
- **Profile photo access control**
- **Privacy policy** management

---

## 📊 Database Design

### Core Tables (12 Total)
1. **users** - Authentication and user accounts
2. **user_profiles** - Health metrics and goals
3. **weight_logs** - Historical weight tracking
4. **photo_galleries** - Progress photos
5. **meals** - Meal entries with aggregated nutrition
6. **food_logs** - Individual food items
7. **exercises** - Exercise records
8. **workouts** - Structured workout sessions
9. **workout_sets** - Individual sets within workouts
10. **tutorial_videos** - Educational content
11. **support_messages** - Help desk tickets
12. **app_settings** - Configuration key-value store

