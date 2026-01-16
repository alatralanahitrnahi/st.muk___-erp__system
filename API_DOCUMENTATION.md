# PVGS ERP - API Documentation

## Base URLs
- **Main API**: `http://localhost:8000/direct-api.php`
- **Workflow API**: `http://localhost:8000/workflow-api.php`

## Authentication

All endpoints (except login and health) require Bearer token authentication.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Token Expiry
- Tokens expire after 15 minutes
- Refresh by logging in again

---

## Authentication APIs

### 1. Login
**POST** `/api/login`

**Purpose**: Authenticate user and get access token

**Request:**
```json
{
  "email": "principal@pvgs.edu",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "abc123...",
    "expires_at": "2024-01-01 10:15:00",
    "user": {
      "id": 1,
      "name": "Dr. Principal",
      "email": "principal@pvgs.edu",
      "user_type": "admin"
    },
    "departments": [...]
  }
}
```

**Used By**: Login page (all roles)

---

### 2. Health Check
**GET** `/api/health`

**Purpose**: Check API status (no auth required)

**Response:**
```json
{
  "success": true,
  "message": "API is healthy",
  "data": {
    "database": "connected",
    "version": "1.0.0"
  }
}
```

**Used By**: System monitoring

---

## Department APIs

### 3. Get Departments
**GET** `/api/departments`

**Purpose**: Get list of departments user has access to

**Response:**
```json
{
  "success": true,
  "message": "Departments retrieved",
  "data": [
    {
      "id": 10,
      "name": "Science",
      "code": "SCI"
    }
  ]
}
```

**Used By**: 
- Principal Dashboard (department selector)
- Faculty Dashboard (department selector)
- Student Portal (profile display)

---

## Student APIs

### 4. Get Students
**GET** `/api/students?department_id=10`

**Purpose**: Get list of students in a department

**Query Parameters:**
- `department_id` (required): Department ID

**Response:**
```json
{
  "success": true,
  "message": "Students retrieved",
  "data": [
    {
      "id": 1,
      "admission_number": "2024001",
      "student_name": "John Doe",
      "email": "student1@pvgs.edu",
      "program_name": "B.Sc Computer Science"
    }
  ],
  "meta": {
    "department_id": 10,
    "count": 10
  }
}
```

**Used By**:
- Principal Dashboard (overview tab)
- Faculty Dashboard (attendance marking, reports)

---

## Attendance APIs

### 5. Get Attendance Records
**GET** `/api/attendance?department_id=10&date_from=2024-01-01&date_to=2024-01-31`

**Purpose**: Get attendance records for a department

**Query Parameters:**
- `department_id` (required): Department ID
- `date_from` (optional): Start date
- `date_to` (optional): End date

**Response:**
```json
{
  "success": true,
  "message": "Attendance retrieved",
  "data": [
    {
      "id": 1,
      "student_id": 1,
      "admission_number": "2024001",
      "student_name": "John Doe",
      "subject_id": 1,
      "attendance_date": "2024-01-15",
      "status": "present"
    }
  ]
}
```

**Used By**:
- Faculty Dashboard (attendance report tab)
- Student Portal (attendance tab)

---

### 6. Mark Attendance
**POST** `/api/attendance`

**Purpose**: Mark attendance for students

**Request:**
```json
{
  "records": [
    {
      "student_id": 1,
      "subject_id": 1,
      "date": "2024-01-15",
      "status": "present"
    }
  ],
  "subject_id": 1,
  "date": "2024-01-15"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Attendance marked successfully"
}
```

**Used By**:
- Faculty Dashboard (mark attendance tab)

---

## Fee APIs

### 7. Get Student Fees
**GET** `/api/fees?department_id=10&status=pending`

**Purpose**: Get fee records for students

**Query Parameters:**
- `department_id` (required): Department ID
- `status` (optional): Filter by status (paid/partial/pending)

**Response:**
```json
{
  "success": true,
  "message": "Fees retrieved",
  "data": [
    {
      "id": 1,
      "student_id": 1,
      "admission_number": "2024001",
      "student_name": "John Doe",
      "net_amount": 50000,
      "paid_amount": 25000,
      "balance_amount": 25000,
      "status": "partial"
    }
  ]
}
```

**Used By**:
- Student Portal (fees tab)
- Principal Dashboard (reports - financial)

---

## Results APIs

### 8. Get Exam Results
**GET** `/api/results?department_id=10`

**Purpose**: Get exam results for students

**Query Parameters:**
- `department_id` (required): Department ID

**Response:**
```json
{
  "success": true,
  "message": "Results retrieved",
  "data": [
    {
      "id": 1,
      "student_id": 1,
      "admission_number": "2024001",
      "student_name": "John Doe",
      "subject_id": 1,
      "subject_name": "Mathematics",
      "marks_obtained": 85,
      "total_marks": 100,
      "grade": "A"
    }
  ]
}
```

**Used By**:
- Student Portal (results tab)

---

## Report APIs

### 9. Attendance Report
**GET** `/api/reports/attendance?department_id=10`

**Purpose**: Get attendance summary report

**Query Parameters:**
- `department_id` (required): Department ID

**Response:**
```json
{
  "success": true,
  "message": "Attendance report generated",
  "data": {
    "total_students": 10,
    "total_present": 850,
    "total_absent": 150,
    "attendance_percentage": 85.0
  }
}
```

**Used By**:
- Principal Dashboard (reports tab - attendance report)

---

### 10. NAAC Report
**GET** `/api/reports/naac?department_id=10`

**Purpose**: Get NAAC compliance report

**Query Parameters:**
- `department_id` (required): Department ID

**Response:**
```json
{
  "success": true,
  "message": "NAAC report generated",
  "data": {
    "student_enrollment": 10,
    "attendance_percentage": 85.0,
    "pass_percentage": 92.0
  }
}
```

**Used By**:
- Principal Dashboard (reports tab - NAAC report)

---

## Workflow APIs

### 11. List Workflows
**GET** `/workflows?department_id=10&state=pending_principal&type=student_admission`

**Purpose**: Get list of workflows

**Query Parameters:**
- `department_id` (optional): Filter by department
- `state` (optional): Filter by state
- `type` (optional): Filter by workflow type

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "workflow_type": "student_admission",
      "entity_type": "student",
      "entity_id": 1,
      "current_state": "pending_principal",
      "department_id": 10,
      "initiated_by": 1,
      "initiated_by_name": "Registrar",
      "metadata": "{...}",
      "created_at": "2024-01-15 10:00:00"
    }
  ]
}
```

**Used By**:
- Principal Dashboard (workflow approvals tab)

---

### 12. Get Workflow Details
**GET** `/workflows/{id}`

**Purpose**: Get detailed workflow information with history

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "workflow_type": "student_admission",
    "current_state": "pending_principal",
    "metadata": "{...}",
    "transitions": [
      {
        "id": 1,
        "from_state": null,
        "to_state": "pending_registrar",
        "action": "create",
        "performed_by": 1,
        "performed_by_name": "Registrar",
        "comments": "",
        "created_at": "2024-01-15 10:00:00"
      }
    ]
  }
}
```

**Used By**:
- Principal Dashboard (workflow detail view)

---

### 13. Create Workflow
**POST** `/workflows`

**Purpose**: Create new workflow

**Request:**
```json
{
  "workflow_type": "student_admission",
  "entity_type": "student",
  "entity_id": 1,
  "department_id": 10,
  "metadata": {
    "student_name": "John Doe",
    "program": "B.Sc Computer Science"
  }
}
```

**Response:**
```json
{
  "success": true,
  "workflow_id": "1",
  "state": "pending_registrar"
}
```

**Used By**:
- Admin/Registrar workflows (future feature)

---

### 14. Transition Workflow
**POST** `/workflows/{id}/transition`

**Purpose**: Approve/reject workflow

**Request:**
```json
{
  "action": "approve",
  "comments": "Approved by principal"
}
```

**Response:**
```json
{
  "success": true,
  "new_state": "approved"
}
```

**Used By**:
- Principal Dashboard (workflow approval actions)

---

## Workflow Types

### 1. Student Admission
**States**: pending_registrar → pending_hod → pending_principal → approved/rejected

**Actions**:
- `approve`: Move to next state
- `reject`: Reject workflow

---

### 2. Fee Waiver
**States**: pending_registrar → pending_hod/pending_principal → approved/rejected

**Special Logic**: If amount ≤ ₹5,000, skip HOD approval

**Actions**:
- `approve`: Move to next state
- `approve_direct`: Skip HOD (registrar only, amount ≤ ₹5,000)
- `reject`: Reject workflow

---

### 3. Lesson Plan
**States**: draft → pending_hod → pending_principal → approved/rejected
**Alternative**: draft → pending_hod → revision_required → pending_hod

**Actions**:
- `submit`: Submit for approval
- `approve`: Move to next state
- `request_revision`: Send back for revision
- `resubmit`: Resubmit after revision
- `reject`: Reject workflow

---

### 4. Department Transfer
**States**: pending_source_hod → pending_target_hod → pending_principal → approved/rejected

**Actions**:
- `approve`: Move to next state
- `reject`: Reject workflow

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "department_id required"
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized - Invalid or expired token"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Access denied to this department"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Endpoint not found"
}
```

### 429 Too Many Requests
```json
{
  "success": false,
  "message": "Rate limit exceeded"
}
```

---

## Rate Limiting

- **Limit**: 60 requests per minute per user per department
- **Headers**: None (internal tracking)
- **Action**: Returns 429 error when exceeded

---

## API Usage Summary by Role

### Principal
- `/api/login` - Authentication
- `/api/departments` - Get all departments
- `/api/students` - View all students
- `/api/reports/attendance` - Attendance reports
- `/api/reports/naac` - NAAC reports
- `/api/fees` - Financial reports
- `/workflows` - View all workflows
- `/workflows/{id}` - View workflow details
- `/workflows/{id}/transition` - Approve/reject workflows

### Faculty
- `/api/login` - Authentication
- `/api/departments` - Get assigned departments
- `/api/students` - View students in department
- `/api/attendance` - Mark and view attendance
- `/api/attendance` (POST) - Submit attendance

### Student
- `/api/login` - Authentication
- `/api/departments` - Get enrolled department
- `/api/attendance` - View own attendance
- `/api/fees` - View own fees
- `/api/results` - View own results

---

## Testing with cURL

### Login
```bash
curl -X POST http://localhost:8000/direct-api.php/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"principal@pvgs.edu","password":"password123"}'
```

### Get Students (with token)
```bash
TOKEN="your_token_here"
curl -X GET "http://localhost:8000/direct-api.php/api/students?department_id=10" \
  -H "Authorization: Bearer $TOKEN"
```

### Approve Workflow
```bash
curl -X POST http://localhost:8000/workflow-api.php/workflows/1/transition \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"action":"approve","comments":"Approved"}'
```
