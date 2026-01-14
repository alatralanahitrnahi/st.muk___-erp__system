# API Documentation - Department-Aware Endpoints v1.0

## Overview

Department-aware API endpoints with standardized responses, security enhancements, and backward compatibility for 3 months.

---

## Base URL

```
Production: https://erp.pvgs.edu/api
Staging: https://staging-erp.pvgs.edu/api
Development: http://localhost/api
```

---

## Authentication

All endpoints require Bearer token authentication:

```http
Authorization: Bearer {your_token_here}
```

---

## Versioned Endpoints (v1)

### Students

#### GET /v1/departments/{departmentId}/students

Retrieve all students for a specific department.

**Parameters**:
- `departmentId` (path, required): Department ID
- `per_page` (query, optional): Results per page (default: 15)

**Response**:
```json
{
  "success": true,
  "message": "Students retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "program_name": "B.Sc Computer Science"
    }
  ],
  "meta": {
    "department_id": 1,
    "department_name": "Computer Science",
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10,
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

**Status Codes**:
- `200`: Success
- `403`: Access denied to department
- `404`: Department not found

---

#### GET /v1/departments/{departmentId}/students/{studentId}

Retrieve a specific student from a department.

**Response**:
```json
{
  "success": true,
  "message": "Student retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "program_name": "B.Sc Computer Science"
  },
  "meta": {
    "department_id": 1,
    "department_name": "Computer Science",
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

---

### Attendance

#### GET /v1/departments/{departmentId}/attendance

Retrieve attendance records for a department.

**Parameters**:
- `date_from` (query, optional): Start date (default: 30 days ago)
- `date_to` (query, optional): End date (default: today)
- `per_page` (query, optional): Results per page (default: 15)

**Response**:
```json
{
  "success": true,
  "message": "Attendance retrieved successfully",
  "data": [...],
  "meta": {
    "department_id": 1,
    "department_name": "Computer Science",
    "current_page": 1,
    "total": 500,
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

---

#### GET /v1/departments/{departmentId}/attendance/report

Generate attendance summary report.

**Response**:
```json
{
  "success": true,
  "message": "Attendance report generated successfully",
  "data": {
    "total_records": 500,
    "present_count": 450,
    "absent_count": 50,
    "attendance_rate": 90.00
  },
  "meta": {
    "department_id": 1,
    "department_name": "Computer Science",
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

---

### Results

#### GET /v1/departments/{departmentId}/results

Retrieve exam results for a department.

**Parameters**:
- `academic_year` (query, optional): e.g., "2023-2024"
- `semester` (query, optional): 1-8
- `per_page` (query, optional): Results per page (default: 15)

**Response**:
```json
{
  "success": true,
  "message": "Results retrieved successfully",
  "data": [...],
  "meta": {
    "department_id": 1,
    "department_name": "Computer Science",
    "current_page": 1,
    "total": 300,
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

---

### Fees

#### GET /v1/departments/{departmentId}/fees

Retrieve fee records for a department.

**Parameters**:
- `status` (query, optional): pending, paid, overdue
- `per_page` (query, optional): Results per page (default: 15)

**Response**:
```json
{
  "success": true,
  "message": "Fees retrieved successfully",
  "data": [...],
  "meta": {
    "department_id": 1,
    "department_name": "Computer Science",
    "current_page": 1,
    "total": 200,
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

---

#### GET /v1/departments/{departmentId}/fees/summary

Generate fee summary for a department.

**Response**:
```json
{
  "success": true,
  "message": "Fee summary generated successfully",
  "data": {
    "total_records": 200,
    "total_amount": 5000000,
    "paid_amount": 4500000,
    "pending_amount": 500000
  },
  "meta": {
    "department_id": 1,
    "department_name": "Computer Science",
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

---

## Legacy Endpoints (Deprecated)

### ⚠️ Deprecation Notice

Legacy endpoints will be removed on **2024-04-25** (3 months). Migrate to v1 endpoints.

---

#### GET /students

**Deprecated**: Use `/v1/departments/{id}/students`

**Parameters**:
- `department_id` (query, optional): Filter by department

**Response includes deprecation notice**:
```json
{
  "success": true,
  "message": "Students retrieved successfully",
  "data": [...],
  "meta": {
    "timestamp": "2024-01-25T10:00:00Z",
    "deprecation_notice": "This endpoint is deprecated. Use /api/v1/departments/{id}/students"
  }
}
```

**Headers**:
```
X-API-Deprecation: Use /api/v1/departments/{id}/students instead
```

---

## Error Responses

### Standard Error Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  },
  "meta": {
    "department_id": 1,
    "department_name": "Computer Science",
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

### Common Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad Request |
| 401 | Unauthenticated |
| 403 | Access Denied |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

## Rate Limiting

**Limits**: 60 requests per minute per user per department

**Headers**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
```

**Rate Limit Exceeded Response**:
```json
{
  "success": false,
  "message": "Too many requests",
  "errors": {
    "rate_limit": ["Too many requests. Please try again in 30 seconds."]
  },
  "meta": {
    "retry_after": 30,
    "timestamp": "2024-01-25T10:00:00Z"
  }
}
```

---

## Security

### Department Access Control

- Users can only access departments they are assigned to
- Super-admins and principals have access to all departments
- Access validation occurs at middleware level
- All access attempts are logged for audit

### Audit Logging

All API requests are logged with:
- User ID
- Department ID
- Endpoint accessed
- Timestamp
- IP address
- User agent

---

## Migration Guide

### From Legacy to V1

**Before** (Legacy):
```javascript
GET /api/students?department_id=1
```

**After** (V1):
```javascript
GET /api/v1/departments/1/students
```

### Response Format Changes

**Legacy**:
```json
{
  "data": [...],
  "current_page": 1,
  "total": 100
}
```

**V1**:
```json
{
  "success": true,
  "message": "...",
  "data": [...],
  "meta": {
    "department_id": 1,
    "department_name": "...",
    "current_page": 1,
    "total": 100,
    "timestamp": "..."
  }
}
```

---

## Testing

### Postman Collection

Import `postman/PVGS_ERP_API_Collection.json` for complete API testing.

### cURL Examples

**Get Department Students**:
```bash
curl -X GET "http://localhost/api/v1/departments/1/students" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Get Attendance Report**:
```bash
curl -X GET "http://localhost/api/v1/departments/1/attendance/report?date_from=2024-01-01&date_to=2024-01-31" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Compliance

### Data Isolation

Per Compliance_Requirements.md:
- Department data is strictly isolated
- Cross-department access requires explicit permissions
- All data access is logged for audit trails

### NAAC Compliance

All responses include department context for NAAC reporting requirements.

---

## Support

**Documentation**: `/docs/API_DOCUMENTATION.md`  
**Issues**: Contact development team  
**Migration Support**: Available until 2024-04-25

---

**Version**: 1.0  
**Last Updated**: 2024-01-25  
**Deprecation Date**: 2024-04-25 (Legacy endpoints)
