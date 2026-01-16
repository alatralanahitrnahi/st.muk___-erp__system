# PVGS ERP - Frontend Pages Documentation

## Overview
Complete guide to all frontend pages, their purpose, and content for each user role.

---

## Public Pages (No Authentication)

### 1. Homepage (`/`)
**File**: `/public/index.html`

**Purpose**: Landing page for the ERP system

**Content**:
- Hero section with system title and tagline
- "Login to Portal" CTA button
- Features grid (6 features):
  - 📚 Student Management
  - 📊 Attendance Tracking
  - 💰 Fee Management
  - 🎯 Examination & Results
  - ✅ Workflow Approvals
  - 📋 NAAC Compliance
- Statistics section (Departments, Students, Workflows, Digital)
- Call-to-action section
- Footer with copyright

**Navigation**: Click "Login to Portal" → `/app/`

---

### 2. Login Page (`/app/`)
**File**: `/frontend/src/pages/Login.jsx`

**Purpose**: User authentication

**Content**:
- 🎓 PVGS ERP logo and title
- Email input field
- Password input field
- "Login to Portal →" button
- Demo accounts section:
  - 👨💼 Principal: principal@pvgs.edu
  - 👨🏫 Faculty: faculty1@pvgs.edu
  - 👨🎓 Student: student1@pvgs.edu
  - 🔑 Password: password123
- "← Back to Homepage" link

**API Used**: `POST /api/login`

**Navigation After Login**:
- Principal/Admin → `/super-admin`
- Faculty → `/faculty`
- Student → `/student`

---

## Principal/Admin Dashboard (`/super-admin`)

### Page: Principal Dashboard
**File**: `/frontend/src/pages/PrincipalDashboard.jsx`

**Purpose**: Administrative control and oversight

**Header**:
- Title: "Principal Dashboard"
- User name display
- Department selector dropdown
- Logout button

**Tabs**:

#### Tab 1: Workflow Approvals
**Component**: `/frontend/src/components/WorkflowApprovals.jsx`

**Content**:
- **Left Panel**: Workflow List
  - Workflow type (Student Admission, Fee Waiver, etc.)
  - Entity ID
  - Status badge (color-coded)
  - Initiated by name
  - Created date
  - Pending count indicator

- **Right Panel**: Workflow Details (when selected)
  - Workflow type and current state
  - Metadata (JSON details)
  - Transition history timeline:
    - Action taken
    - State changes (from → to)
    - Performed by user
    - Timestamp
    - Comments
  - Action section (if pending):
    - Comments textarea
    - "Approve" button (green)
    - "Reject" button (red)

**APIs Used**:
- `GET /workflows?department_id={id}` - List workflows
- `GET /workflows/{id}` - Get workflow details
- `POST /workflows/{id}/transition` - Approve/reject

**User Actions**:
- Select department
- Click workflow to view details
- Add comments
- Approve or reject workflow

---

#### Tab 2: Reports & Analytics
**Component**: `/frontend/src/components/Reports.jsx`

**Content**:
- Report type selector buttons:
  - 📊 Attendance Report
  - 📋 NAAC Report
  - 💰 Financial Report
- "📥 Export Report" button

**Attendance Report**:
- Cards:
  - Total Students (blue)
  - Total Present (green)
  - Total Absent (red)
  - Attendance % (purple)
- Analysis section with 75% threshold check

**NAAC Report**:
- Cards:
  - Student Enrollment (blue)
  - Attendance Rate (green)
  - Pass Percentage (purple)
- Criterion sections:
  - 📚 Criterion 1: Curricular Aspects
  - 👨🏫 Criterion 2: Teaching-Learning
  - 🎓 Criterion 3: Student Performance

**Financial Report**:
- Summary cards:
  - Total Amount (blue)
  - Collected (green)
  - Pending (orange)
  - Collection Rate % (purple)
- Payment status breakdown:
  - Fully Paid count (green)
  - Partial Payment count (yellow)
  - Pending count (red)
- Financial health analysis

**APIs Used**:
- `GET /api/reports/attendance?department_id={id}`
- `GET /api/reports/naac?department_id={id}`
- `GET /api/fees?department_id={id}`

**User Actions**:
- Select report type
- View metrics and analysis
- Export report as JSON

---

#### Tab 3: Overview
**Content**:
- Statistics cards:
  - Total Departments
  - Students count
  - Active Users
- Student list table (when department selected):
  - Name
  - Email
  - Program

**APIs Used**:
- `GET /api/departments`
- `GET /api/students?department_id={id}`

---

## Faculty Dashboard (`/faculty`)

### Page: Faculty Dashboard
**File**: `/frontend/src/pages/FacultyDashboard.jsx`

**Purpose**: Attendance management and student tracking

**Header**:
- Title: "Faculty Dashboard"
- User name display
- Department selector dropdown
- Logout button

**Tabs**:

#### Tab 1: Mark Attendance

**Content**:
- Date picker (default: today)
- Bulk action buttons:
  - "Mark All Present" (green)
  - "Mark All Absent" (red)
- Student table:
  - Columns: Roll No, Student Name, Program, Status
  - Status toggle button per student:
    - Not Marked (gray)
    - ✓ Present (green)
    - ✗ Absent (red)
- Footer:
  - Counter: "Marked: X / Y students"
  - "Submit Attendance" button (disabled if none marked)

**APIs Used**:
- `GET /api/departments`
- `GET /api/students?department_id={id}`
- `POST /api/attendance` - Submit attendance

**User Actions**:
- Select department
- Select date
- Mark all present/absent OR toggle individual students
- Submit attendance

---

#### Tab 2: Attendance Report

**Content**:
- Student table:
  - Columns: Roll No, Student Name, Program, Attendance %, Status
  - Color-coded percentages:
    - Green: ≥75%
    - Red: <75%
  - Status badges:
    - ✓ Good (green) - ≥75%
    - ⚠️ Defaulter (red) - <75%
  - Defaulter rows highlighted in red background

**APIs Used**:
- `GET /api/students?department_id={id}`
- `GET /api/attendance?department_id={id}`

**User Actions**:
- View attendance percentages
- Identify defaulters

---

## Student Portal (`/student`)

### Page: Student Dashboard
**File**: `/frontend/src/pages/StudentDashboard.jsx`

**Purpose**: Student self-service portal

**Header**:
- Title: "Student Portal"
- User name display
- Logout button

**Tabs**:

#### Tab 1: Dashboard

**Content**:
- Metric cards:
  - **Attendance**: Percentage (color-coded: green ≥75%, red <75%)
    - Present / Total classes
  - **Fee Balance**: Amount in ₹
    - Paid amount
  - **Results**: Count of exams completed

- Quick action cards:
  - 📊 View Attendance
  - 💰 Fee Payment
  - 🎓 Exam Results

**APIs Used**:
- `GET /api/departments`
- `GET /api/attendance?department_id={id}`
- `GET /api/fees?department_id={id}`
- `GET /api/results?department_id={id}`

---

#### Tab 2: Profile

**Content**:
- Profile information grid:
  - Name
  - Email
  - User Type
  - Department

**APIs Used**:
- User data from auth store
- `GET /api/departments`

---

#### Tab 3: Attendance

**Content**:
- Header:
  - Overall attendance percentage
  - Present/Absent counts
  - Status badge:
    - ✓ Good Standing (green) - ≥75%
    - ⚠️ Below 75% (red) - <75%

- Attendance history table:
  - Columns: Date, Subject, Status
  - Status badges:
    - ✓ Present (green)
    - ✗ Absent (red)

**APIs Used**:
- `GET /api/attendance?department_id={id}`

**User Actions**:
- View attendance records
- Check overall percentage

---

#### Tab 4: Fees

**Content**:
- Header:
  - Total amount
  - Paid amount
  - Balance amount
  - Status badge:
    - ✓ Paid (green) - balance = 0
    - ⚠️ Pending (orange) - balance > 0

- Fee breakdown table:
  - Columns: Category, Total Amount, Paid, Balance, Status
  - Status badges:
    - Paid (green)
    - Partial (yellow)
    - Pending (red)

**APIs Used**:
- `GET /api/fees?department_id={id}`

**User Actions**:
- View fee status
- Check payment history

---

#### Tab 5: Results

**Content**:
- Results count header
- Results table:
  - Columns: Subject, Marks Obtained, Total Marks, Percentage, Grade
  - Color-coded percentages:
    - Green: ≥75%
    - Blue: 60-74%
    - Yellow: 40-59%
    - Red: <40%
  - Grade badges:
    - A+/A (green)
    - B+/B (blue)
    - C (yellow)
    - D/F (red)

**APIs Used**:
- `GET /api/results?department_id={id}`

**User Actions**:
- View exam results
- Check grades and percentages

---

## Common UI Components

### Navigation
- All dashboards have tabs for different sections
- Active tab highlighted with blue underline
- Smooth transitions between tabs

### Department Selector
- Dropdown in header (Principal, Faculty)
- Shows only departments user has access to
- Changes data when department selected

### Status Badges
- Color-coded for quick visual identification:
  - Green: Success/Good/Approved
  - Yellow: Warning/Partial/Pending
  - Red: Error/Bad/Rejected
  - Blue: Info/Neutral
  - Purple: Special/Metrics

### Tables
- Hover effect on rows
- Responsive design
- Pagination (future enhancement)
- Empty state messages

### Cards
- Shadow on hover
- Consistent padding and spacing
- Icon + Title + Value format
- Color-coded backgrounds

### Buttons
- Primary: Blue gradient
- Success: Green
- Danger: Red
- Secondary: Gray
- Disabled state with opacity

---

## Responsive Design

### Mobile (< 768px)
- Single column layouts
- Stacked cards
- Hamburger menu (future)
- Touch-friendly buttons

### Tablet (768px - 1024px)
- 2-column grids
- Adjusted spacing
- Readable font sizes

### Desktop (> 1024px)
- 3-column grids
- Full-width tables
- Optimal spacing

---

## Color Scheme

### Primary Colors
- Blue: `#667eea` (Primary actions)
- Purple: `#764ba2` (Gradients)
- Green: `#10b981` (Success)
- Red: `#ef4444` (Danger)
- Orange: `#f59e0b` (Warning)
- Yellow: `#eab308` (Caution)

### Neutral Colors
- Gray-50: `#f9fafb` (Backgrounds)
- Gray-100: `#f3f4f6` (Borders)
- Gray-600: `#4b5563` (Text secondary)
- Gray-800: `#1f2937` (Text primary)

---

## Future Enhancements

### Principal Dashboard
- [ ] Add student admission form
- [ ] Bulk workflow actions
- [ ] Advanced filters for workflows
- [ ] Chart visualizations for reports
- [ ] PDF export for reports

### Faculty Dashboard
- [ ] Lesson planning module
- [ ] Marks entry form
- [ ] Student performance analytics
- [ ] Attendance calendar view
- [ ] Bulk attendance upload (CSV)

### Student Portal
- [ ] Online fee payment integration
- [ ] Document upload/download
- [ ] Exam schedule view
- [ ] Assignment submission
- [ ] Notifications center

### Common
- [ ] Dark mode
- [ ] Multi-language support
- [ ] Real-time notifications
- [ ] Advanced search
- [ ] Mobile app (PWA)

---

## Page Load Flow

### 1. User visits `/`
→ Homepage loads
→ Click "Login to Portal"
→ Redirect to `/app/`

### 2. User at `/app/`
→ Login page loads
→ Enter credentials
→ API call to `/api/login`
→ Store token and user data
→ Redirect based on role:
  - Admin → `/super-admin`
  - Faculty → `/faculty`
  - Student → `/student`

### 3. User at dashboard
→ Dashboard loads
→ Fetch departments
→ Select department (if applicable)
→ Fetch data for selected tab
→ Display data

### 4. User logs out
→ Clear token and user data
→ Redirect to `/app/`

---

## Error Handling

### Network Errors
- Display error message
- Retry button
- Fallback to cached data (if available)

### Authentication Errors
- Token expired → Redirect to login
- Invalid credentials → Show error message
- Access denied → Show permission error

### Data Errors
- Empty state messages
- "No data found" placeholders
- Helpful error descriptions

---

## Performance Optimization

### Implemented
- Code splitting by route
- Lazy loading components
- React Query caching
- Debounced search (future)
- Optimized bundle size (~340KB)

### Future
- Virtual scrolling for large lists
- Image lazy loading
- Service worker for offline support
- CDN for static assets
