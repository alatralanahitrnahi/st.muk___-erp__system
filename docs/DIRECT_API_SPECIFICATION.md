# Direct API Specification

## Overview
Production-ready REST API bypassing Laravel framework issues. Uses JWT authentication, role-based access control, and department context.

## Base URL
```
http://localhost:8000/direct-api.php
```

## Authentication

### Login
```bash
POST /api/login
Content-Type: application/json

{
  "email": "admin@pvgs.edu",
  "password": "password123"
}

Response:
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1Q...",
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "admin@pvgs.edu",
      "role": "super-admin",
      "user_type": "admin"
    }
  },
  "meta": {
    "timestamp": "2026-01-16T06:50:00Z"
  }
}
```

### Using Token
```bash
GET /api/user
Authorization: Bearer eyJ0eXAiOiJKV1Q...
```

## Endpoints

### User Management
- `GET /api/user` - Get current user profile
- `POST /api/login` - Authenticate user
- `POST /api/logout` - Logout user

### Department Management
- `GET /api/departments` - List all departments
- `GET /api/departments/{id}` - Get department details

### Student Management
- `GET /api/students?department_id=1` - List students (department-scoped)
- `GET /api/students/{id}` - Get student details
- `POST /api/students` - Create student (Registrar only)
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student (Super Admin only)

### Attendance Management
- `GET /api/attendance?department_id=1&date_from=2026-01-01&date_to=2026-01-31`
- `POST /api/attendance` - Mark attendance (Faculty only)

## Role Permissions

| Role | Permissions |
|------|-------------|
| super-admin | Full access (*) |
| principal | view_all, manage_all, approve_all |
| registrar | view_department, manage_department, approve_department |
| faculty | view_students, mark_attendance, enter_results |
| student | view_own, view_results, pay_fees |

## Security
- JWT tokens expire in 15 minutes
- Rate limit: 60 requests/minute per user
- All inputs sanitized
- Department isolation enforced

## Error Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 429: Rate Limit Exceeded
- 500: Server Error
