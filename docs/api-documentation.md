# Urban Renewal System API Documentation

## Overview

This document provides comprehensive documentation for the Urban Renewal Management System API. The API is built using CodeIgniter 4 framework and provides RESTful endpoints for managing urban renewal projects, meetings, voting, users, and related data.

## Base URL
```
http://localhost:9228/api
```

## Authentication

The API uses JWT (JSON Web Token) based authentication. Most endpoints require authentication.

### Authentication Headers
```
Authorization: Bearer <jwt_token>
```

### Authentication Flow
1. **Login**: `POST /auth/login`
2. **Refresh Token**: `POST /auth/refresh`
3. **Logout**: `POST /auth/logout`

---

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": {...},
  "timestamp": "2024-01-01 12:00:00"
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Error description",
  "errors": {...},
  "timestamp": "2024-01-01 12:00:00"
}
```

---

## API Endpoints

### 1. Authentication API (`/auth`)

#### Login
- **POST** `/auth/login`
- **Description**: Authenticate user and get JWT token
- **Authentication**: Not required
- **Request Body**:
```json
{
  "username": "admin",
  "password": "password123"
}
```
- **Response**:
```json
{
  "status": "success",
  "message": "登入成功",
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "role": "admin",
      "full_name": "Administrator"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 86400
  }
}
```

#### Logout
- **POST** `/auth/logout`
- **Description**: Logout user and invalidate token
- **Authentication**: Required
- **Response**:
```json
{
  "status": "success",
  "message": "登出成功"
}
```

#### Refresh Token
- **POST** `/auth/refresh`
- **Description**: Refresh JWT token using refresh token
- **Request Body**:
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

#### Get Current User
- **GET** `/auth/me`
- **Description**: Get current authenticated user information
- **Authentication**: Required

#### Forgot Password
- **POST** `/auth/forgot-password`
- **Description**: Send password reset email
- **Request Body**:
```json
{
  "email": "user@example.com"
}
```

#### Reset Password
- **POST** `/auth/reset-password`
- **Description**: Reset password using reset token
- **Request Body**:
```json
{
  "token": "reset_token",
  "password": "new_password",
  "confirm_password": "new_password"
}
```

---

### 2. User Management API (`/users`)

#### List Users
- **GET** `/users`
- **Description**: Get paginated list of users
- **Authentication**: Required (admin, chairman)
- **Query Parameters**:
  - `page` (int): Page number
  - `per_page` (int): Items per page
  - `role` (string): Filter by role
  - `urban_renewal_id` (int): Filter by urban renewal
  - `search` (string): Search keyword

#### Get User
- **GET** `/users/{id}`
- **Description**: Get user details
- **Authentication**: Required

#### Create User
- **POST** `/users`
- **Description**: Create new user
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "username": "newuser",
  "email": "user@example.com",
  "password": "password123",
  "role": "member",
  "full_name": "User Name",
  "phone": "0912345678",
  "urban_renewal_id": 1
}
```

#### Update User
- **PUT** `/users/{id}`
- **Description**: Update user information
- **Authentication**: Required

#### Delete User
- **DELETE** `/users/{id}`
- **Description**: Delete user (soft delete)
- **Authentication**: Required (admin, chairman)

#### Toggle User Status
- **PATCH** `/users/{id}/toggle-status`
- **Description**: Enable/disable user account
- **Authentication**: Required (admin, chairman)

#### Reset Login Attempts
- **PATCH** `/users/{id}/reset-login-attempts`
- **Description**: Reset failed login attempts counter
- **Authentication**: Required (admin, chairman)

#### Search Users
- **GET** `/users/search`
- **Description**: Search users by keyword
- **Query Parameters**:
  - `keyword` (string): Search keyword

#### Get Role Statistics
- **GET** `/users/role-statistics`
- **Description**: Get user count by role
- **Authentication**: Required (admin, chairman)

#### Get User Profile
- **GET** `/users/profile`
- **Description**: Get current user's profile
- **Authentication**: Required

#### Change Password
- **POST** `/users/change-password`
- **Description**: Change user password
- **Authentication**: Required
- **Request Body**:
```json
{
  "current_password": "current_pass",
  "new_password": "new_pass",
  "confirm_password": "new_pass"
}
```

---

### 3. Meetings API (`/meetings`)

#### List Meetings
- **GET** `/meetings`
- **Description**: Get paginated list of meetings
- **Authentication**: Required
- **Query Parameters**:
  - `page` (int): Page number
  - `per_page` (int): Items per page
  - `urban_renewal_id` (int): Filter by urban renewal
  - `status` (string): Filter by status
  - `search` (string): Search keyword

#### Get Meeting
- **GET** `/meetings/{id}`
- **Description**: Get meeting details with attendance and voting info
- **Authentication**: Required

#### Create Meeting
- **POST** `/meetings`
- **Description**: Create new meeting
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "urban_renewal_id": 1,
  "meeting_name": "第一次會員大會",
  "meeting_type": "會員大會",
  "meeting_date": "2024-02-01",
  "meeting_time": "14:00",
  "meeting_location": "社區活動中心",
  "quorum_land_area_numerator": 2,
  "quorum_land_area_denominator": 3,
  "quorum_building_area_numerator": 2,
  "quorum_building_area_denominator": 3,
  "quorum_member_numerator": 1,
  "quorum_member_denominator": 2
}
```

#### Update Meeting
- **PUT** `/meetings/{id}`
- **Description**: Update meeting information
- **Authentication**: Required (admin, chairman)

#### Delete Meeting
- **DELETE** `/meetings/{id}`
- **Description**: Delete meeting (soft delete)
- **Authentication**: Required (admin, chairman)

#### Update Meeting Status
- **PATCH** `/meetings/{id}/status`
- **Description**: Update meeting status
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "status": "in_progress"
}
```

#### Get Meeting Statistics
- **GET** `/meetings/{id}/statistics`
- **Description**: Get detailed meeting statistics
- **Authentication**: Required

#### Search Meetings
- **GET** `/meetings/search`
- **Description**: Search meetings by keyword
- **Query Parameters**:
  - `keyword` (string): Search keyword

#### Get Upcoming Meetings
- **GET** `/meetings/upcoming`
- **Description**: Get upcoming meetings
- **Query Parameters**:
  - `days` (int): Number of days to look ahead

#### Get Status Statistics
- **GET** `/meetings/status-statistics`
- **Description**: Get meeting count by status
- **Authentication**: Required

---

### 4. Meeting Attendance API (`/meeting-attendance`)

#### List Attendance Records
- **GET** `/meeting-attendance`
- **Description**: Get attendance records for a meeting
- **Authentication**: Required
- **Query Parameters**:
  - `meeting_id` (int): Meeting ID (required)
  - `status` (string): Filter by attendance status

#### Check In
- **POST** `/meeting-attendance/check-in`
- **Description**: Check in to a meeting
- **Authentication**: Required
- **Request Body**:
```json
{
  "meeting_id": 1,
  "property_owner_id": 1,
  "attendance_type": "present",
  "check_in_time": "2024-02-01 14:00:00",
  "notes": "準時出席"
}
```

#### Batch Check In
- **POST** `/meeting-attendance/batch-check-in`
- **Description**: Batch check in multiple attendees
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "meeting_id": 1,
  "attendees": [
    {
      "property_owner_id": 1,
      "attendance_type": "present"
    },
    {
      "property_owner_id": 2,
      "attendance_type": "proxy"
    }
  ]
}
```

#### Update Attendance Status
- **PATCH** `/meeting-attendance/{id}/update-status`
- **Description**: Update attendance status
- **Authentication**: Required (admin, chairman)

#### Get Attendance Summary
- **GET** `/meeting-attendance/{meetingId}/summary`
- **Description**: Get attendance summary for a meeting
- **Authentication**: Required

#### Export Attendance
- **GET** `/meeting-attendance/{meetingId}/export`
- **Description**: Export attendance records to CSV
- **Authentication**: Required (admin, chairman)
- **Query Parameters**:
  - `format` (string): Export format (csv, excel)

#### Get Attendance Statistics
- **GET** `/meeting-attendance/{meetingId}/statistics`
- **Description**: Get detailed attendance statistics
- **Authentication**: Required

---

### 5. Voting Topics API (`/voting-topics`)

#### List Voting Topics
- **GET** `/voting-topics`
- **Description**: Get voting topics
- **Authentication**: Required
- **Query Parameters**:
  - `meeting_id` (int): Filter by meeting
  - `status` (string): Filter by voting status

#### Get Voting Topic
- **GET** `/voting-topics/{id}`
- **Description**: Get voting topic details with voting statistics
- **Authentication**: Required

#### Create Voting Topic
- **POST** `/voting-topics`
- **Description**: Create new voting topic
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "meeting_id": 1,
  "topic_number": "1",
  "topic_title": "社區管理費調整案",
  "topic_description": "將月管理費由1000元調整為1200元",
  "voting_method": "simple_majority"
}
```

#### Update Voting Topic
- **PUT** `/voting-topics/{id}`
- **Description**: Update voting topic (only draft status)
- **Authentication**: Required (admin, chairman)

#### Delete Voting Topic
- **DELETE** `/voting-topics/{id}`
- **Description**: Delete voting topic (only draft status)
- **Authentication**: Required (admin, chairman)

#### Start Voting
- **PATCH** `/voting-topics/{id}/start-voting`
- **Description**: Start voting on a topic
- **Authentication**: Required (admin, chairman)

#### Close Voting
- **PATCH** `/voting-topics/{id}/close-voting`
- **Description**: Close voting and calculate results
- **Authentication**: Required (admin, chairman)

#### Get Voting Statistics
- **GET** `/voting-topics/statistics`
- **Description**: Get voting topics statistics
- **Query Parameters**:
  - `meeting_id` (int): Filter by meeting

#### Get Upcoming Topics
- **GET** `/voting-topics/upcoming`
- **Description**: Get upcoming voting topics
- **Query Parameters**:
  - `days` (int): Number of days to look ahead

---

### 6. Voting API (`/voting`)

#### List Voting Records
- **GET** `/voting`
- **Description**: Get voting records for a topic
- **Authentication**: Required
- **Query Parameters**:
  - `topic_id` (int): Voting topic ID (required)
  - `choice` (string): Filter by vote choice

#### Cast Vote
- **POST** `/voting/vote`
- **Description**: Cast a vote
- **Authentication**: Required
- **Request Body**:
```json
{
  "topic_id": 1,
  "property_owner_id": 1,
  "choice": "agree",
  "voter_name": "張三",
  "notes": "同意此提案"
}
```

#### Batch Vote
- **POST** `/voting/batch-vote`
- **Description**: Cast multiple votes (proxy voting)
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "votes": [
    {
      "topic_id": 1,
      "property_owner_id": 1,
      "choice": "agree"
    },
    {
      "topic_id": 1,
      "property_owner_id": 2,
      "choice": "disagree"
    }
  ]
}
```

#### Get My Vote
- **GET** `/voting/my-vote/{topicId}`
- **Description**: Get current user's vote for a topic
- **Authentication**: Required

#### Remove Vote
- **DELETE** `/voting/remove-vote`
- **Description**: Remove/withdraw a vote
- **Authentication**: Required
- **Request Body**:
```json
{
  "topic_id": 1,
  "property_owner_id": 1
}
```

#### Get Voting Statistics
- **GET** `/voting/statistics/{topicId}`
- **Description**: Get detailed voting statistics
- **Authentication**: Required

#### Export Voting Records
- **GET** `/voting/export/{topicId}`
- **Description**: Export voting records to CSV
- **Authentication**: Required (admin, chairman)
- **Query Parameters**:
  - `format` (string): Export format (csv)

#### Get Detailed Records
- **GET** `/voting/detailed/{topicId}`
- **Description**: Get detailed voting records with owner info
- **Authentication**: Required

---

### 7. System Settings API (`/system-settings`)

#### List Settings (Admin)
- **GET** `/system-settings`
- **Description**: Get all system settings for management
- **Authentication**: Required (admin)
- **Query Parameters**:
  - `category` (string): Filter by category

#### Get Public Settings
- **GET** `/system-settings/public`
- **Description**: Get public system settings
- **Authentication**: Not required

#### Get Category Settings
- **GET** `/system-settings/category/{category}`
- **Description**: Get settings by category
- **Authentication**: Required

#### Get Setting Value
- **GET** `/system-settings/get/{key}`
- **Description**: Get specific setting value
- **Authentication**: Required

#### Set Setting
- **POST** `/system-settings/set`
- **Description**: Update a setting value
- **Authentication**: Required (admin)
- **Request Body**:
```json
{
  "key": "app.name",
  "value": "Urban Renewal System"
}
```

#### Batch Set Settings
- **POST** `/system-settings/batch-set`
- **Description**: Update multiple settings
- **Authentication**: Required (admin)
- **Request Body**:
```json
{
  "settings": {
    "app.name": "Urban Renewal System",
    "mail.enabled": true,
    "system.debug": false
  }
}
```

#### Reset Setting
- **PATCH** `/system-settings/reset/{key}`
- **Description**: Reset setting to default value
- **Authentication**: Required (admin)

#### Get Categories
- **GET** `/system-settings/categories`
- **Description**: Get list of setting categories
- **Authentication**: Required (admin)

#### Clear Cache
- **DELETE** `/system-settings/clear-cache`
- **Description**: Clear settings cache
- **Authentication**: Required (admin)
- **Query Parameters**:
  - `key` (string): Specific setting key to clear

#### Validate Setting
- **POST** `/system-settings/validate`
- **Description**: Validate setting value
- **Authentication**: Required (admin)
- **Request Body**:
```json
{
  "key": "app.name",
  "value": "New App Name"
}
```

#### Get System Info
- **GET** `/system-settings/system-info`
- **Description**: Get system information
- **Authentication**: Required (admin)

---

### 8. Notifications API (`/notifications`)

#### List Notifications
- **GET** `/notifications`
- **Description**: Get user's notifications
- **Authentication**: Required
- **Query Parameters**:
  - `page` (int): Page number
  - `per_page` (int): Items per page
  - `is_read` (boolean): Filter by read status
  - `type` (string): Filter by notification type
  - `priority` (string): Filter by priority

#### Get Notification
- **GET** `/notifications/{id}`
- **Description**: Get notification details (auto marks as read)
- **Authentication**: Required

#### Create Notification
- **POST** `/notifications`
- **Description**: Create notification (admin/chairman only)
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "title": "會議通知",
  "message": "即將舉行會員大會",
  "notification_type": "meeting_notice",
  "priority": "high",
  "user_ids": [1, 2, 3],
  "expires_at": "2024-02-01 23:59:59"
}
```

#### Mark as Read
- **PATCH** `/notifications/{id}/mark-read`
- **Description**: Mark notification as read
- **Authentication**: Required

#### Mark Multiple as Read
- **PATCH** `/notifications/mark-multiple-read`
- **Description**: Mark multiple notifications as read
- **Authentication**: Required
- **Request Body**:
```json
{
  "notification_ids": [1, 2, 3]
}
```

#### Mark All as Read
- **PATCH** `/notifications/mark-all-read`
- **Description**: Mark all notifications as read
- **Authentication**: Required

#### Delete Notification
- **DELETE** `/notifications/{id}`
- **Description**: Delete notification
- **Authentication**: Required

#### Get Unread Count
- **GET** `/notifications/unread-count`
- **Description**: Get unread notifications count
- **Authentication**: Required

#### Get Statistics
- **GET** `/notifications/statistics`
- **Description**: Get notification statistics
- **Authentication**: Required

#### Create Meeting Notification
- **POST** `/notifications/create-meeting-notification`
- **Description**: Create meeting-related notification
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "meeting_id": 1,
  "type": "meeting_reminder",
  "send_email": true,
  "send_sms": false
}
```

#### Create Voting Notification
- **POST** `/notifications/create-voting-notification`
- **Description**: Create voting-related notification
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "topic_id": 1,
  "type": "voting_start",
  "send_email": true
}
```

#### Clean Expired
- **DELETE** `/notifications/clean-expired`
- **Description**: Clean expired notifications
- **Authentication**: Required (admin)

#### Get Notification Types
- **GET** `/notifications/types`
- **Description**: Get list of notification types

---

### 9. Documents API (`/documents`)

#### List Documents
- **GET** `/documents`
- **Description**: Get documents list
- **Authentication**: Required
- **Query Parameters**:
  - `meeting_id` (int): Filter by meeting
  - `type` (string): Filter by document type
  - `search` (string): Search keyword

#### Get Document
- **GET** `/documents/{id}`
- **Description**: Get document details
- **Authentication**: Required

#### Upload Document
- **POST** `/documents/upload`
- **Description**: Upload document file
- **Authentication**: Required (admin, chairman, member)
- **Content-Type**: `multipart/form-data`
- **Form Data**:
  - `file`: Document file
  - `meeting_id`: Meeting ID
  - `document_type`: Document type (agenda, minutes, attendance, notice, other)
  - `document_name`: Document name

#### Download Document
- **GET** `/documents/download/{id}`
- **Description**: Download document file
- **Authentication**: Required

#### Update Document
- **PUT** `/documents/{id}`
- **Description**: Update document information
- **Authentication**: Required (admin, chairman)
- **Request Body**:
```json
{
  "document_name": "Updated Document Name",
  "document_type": "minutes"
}
```

#### Delete Document
- **DELETE** `/documents/{id}`
- **Description**: Delete document and file
- **Authentication**: Required (admin, chairman)

#### Get Statistics
- **GET** `/documents/statistics`
- **Description**: Get document statistics
- **Query Parameters**:
  - `meeting_id` (int): Filter by meeting

#### Get Recent Documents
- **GET** `/documents/recent`
- **Description**: Get recently uploaded documents
- **Query Parameters**:
  - `limit` (int): Number of documents to return

#### Get Document Types
- **GET** `/documents/types`
- **Description**: Get list of document types

#### Get Storage Usage
- **GET** `/documents/storage-usage`
- **Description**: Get storage usage statistics
- **Authentication**: Required

#### Clean Orphan Files
- **DELETE** `/documents/clean-orphan-files`
- **Description**: Clean orphaned files
- **Authentication**: Required (admin)

#### Batch Upload
- **POST** `/documents/batch-upload`
- **Description**: Upload multiple documents
- **Authentication**: Required (admin, chairman)
- **Content-Type**: `multipart/form-data`

---

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 201  | Created |
| 204  | No Content |
| 400  | Bad Request |
| 401  | Unauthorized |
| 403  | Forbidden |
| 404  | Not Found |
| 422  | Validation Error |
| 500  | Server Error |

---

## User Roles and Permissions

### Admin
- Full access to all endpoints
- Can manage all urban renewals
- System administration features

### Chairman
- Manage own urban renewal
- Create and manage meetings
- Create voting topics
- Manage users in same urban renewal

### Member
- View meetings and voting topics
- Participate in voting
- Upload meeting documents
- Manage own profile

### Observer
- Read-only access to meetings
- Cannot vote
- View documents

---

## Rate Limiting

The API implements rate limiting to prevent abuse:
- **Authentication endpoints**: 5 requests per minute
- **Other endpoints**: 100 requests per minute per user

---

## Versioning

The API uses URL-based versioning. Current version is v1 (implicit).
Future versions will use `/api/v2/` pattern.

---

## Support

For API support and questions, please contact the development team or refer to the system documentation.