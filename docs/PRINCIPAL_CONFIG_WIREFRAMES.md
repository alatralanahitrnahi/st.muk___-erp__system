# Principal Configuration System - Wireframes & UI Design

## Main Configuration Dashboard

```
┌─────────────────────────────────────────────────────────────────┐
│ Principal Configuration Center                    [Export] [Save]│
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│ ┌─ Module Selection ────────────────────────────────────────┐   │
│ │ [Students] [Attendance] [Results] [Fees] [Lesson Planning]│   │
│ │ [Reports] [Admissions] [Documents] [Library] [More...]    │   │
│ └───────────────────────────────────────────────────────────┘   │
│                                                                   │
│ ┌─ Permission Matrix: STUDENTS MODULE ──────────────────────┐   │
│ │                                                             │   │
│ │         │ View │ Create │ Edit │ Delete │ Export │ Approve│   │
│ │ ────────┼──────┼────────┼──────┼────────┼────────┼────────│   │
│ │ Registrar│ [✓] │  [✓]   │ [✓]  │  [✓]   │  [✓]   │  [✓]  │   │
│ │ Faculty  │ [✓] │  [ ]   │ [ ]  │  [ ]   │  [ ]   │  [ ]  │   │
│ │ Student  │ [✓] │  [ ]   │ [ ]  │  [ ]   │  [ ]   │  [ ]  │   │
│ │                                                             │   │
│ │ ⚠️ Warning: Removing Registrar "Edit" will prevent profile │   │
│ │    updates. Recommended: Keep enabled.                     │   │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│ ┌─ Data Visibility Rules ───────────────────────────────────┐   │
│ │ Faculty can see:                                           │   │
│ │ [✓] Students in assigned classes only                     │   │
│ │ [✓] Students in assigned subjects                         │   │
│ │ [ ] All students in their department                      │   │
│ │ [ ] All students (unrestricted)                           │   │
│ │                                                             │   │
│ │ Student can see:                                           │   │
│ │ [✓] Own profile only                                       │   │
│ │ [✓] Classmates (name & roll number only)                  │   │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│ ┌─ Field-Level Permissions ─────────────────────────────────┐   │
│ │ Field Name          │ Registrar │ Faculty │ Student       │   │
│ │ ────────────────────┼───────────┼─────────┼───────────    │   │
│ │ Personal Details    │ Edit      │ View    │ View          │   │
│ │ Contact Info        │ Edit      │ View    │ Edit (own)    │   │
│ │ Academic Status     │ Edit      │ View    │ View          │   │
│ │ Fee Status          │ View      │ Hidden  │ View (own)    │   │
│ │ Attendance %        │ View      │ Edit    │ View (own)    │   │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│ ┌─ Preview Changes ─────────────────────────────────────────┐   │
│ │ View as: [Registrar ▼] [Faculty ▼] [Student ▼]           │   │
│ │                                                             │   │
│ │ [Preview shows filtered UI based on selected role]        │   │
│ └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

## Module-Specific Configuration Panels

### STUDENTS MODULE - Critical Issues Resolution

```
┌─ Students Module Configuration ────────────────────────────────┐
│                                                                  │
│ 🔧 CRITICAL ISSUES DETECTED                                     │
│                                                                  │
│ ⚠️ Issue #1: Status Field Confusion                            │
│ │ Problem: application_status vs status field mismatch         │
│ │ Impact: Enrollment workflow broken                           │
│ │                                                               │
│ │ Resolution:                                                   │
│ │ [✓] Use application_status for admission workflow            │
│ │     Values: pending → approved → rejected                    │
│ │ [✓] Use status for enrollment state                          │
│ │     Values: active → inactive → graduated → withdrawn        │
│ │                                                               │
│ │ [Apply Fix] [View Migration]                                 │
│ └───────────────────────────────────────────────────────────────│
│                                                                  │
│ Workflow Configuration:                                         │
│ ┌─────────────────────────────────────────────────────────────┐│
│ │ Admission Approval Chain:                                    ││
│ │ Application → Registrar Review → Principal Approval          ││
│ │                                                               ││
│ │ Step 1: [Registrar ▼] can [Approve/Reject ▼]                ││
│ │ Step 2: [Principal ▼] can [Final Approve ▼]                 ││
│ │                                                               ││
│ │ [+ Add Step] [Remove Step]                                   ││
│ └─────────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────────┘
```

### ATTENDANCE MODULE - Schema Fix

```
┌─ Attendance Module Configuration ──────────────────────────────┐
│                                                                  │
│ 🔧 CRITICAL ISSUES DETECTED                                     │
│                                                                  │
│ ⚠️ Issue #2: Subject Component Mismatch                        │
│ │ Problem: subject_component_id vs subject_id inconsistency    │
│ │ Impact: Attendance tracking fails for lab/practical          │
│ │                                                               │
│ │ Resolution:                                                   │
│ │ [✓] Use subject_id for theory attendance                     │
│ │ [✓] Use subject_component_id for lab/practical/tutorial      │
│ │ [✓] Add component_type field (theory/lab/practical/tutorial) │
│ │                                                               │
│ │ [Apply Fix] [View Migration]                                 │
│ └───────────────────────────────────────────────────────────────│
│                                                                  │
│ Marking Rules:                                                  │
│ ┌─────────────────────────────────────────────────────────────┐│
│ │ Faculty can mark attendance for:                             ││
│ │ [✓] Assigned subjects only                                   ││
│ │ [✓] Within 24 hours of class                                 ││
│ │ [ ] Retroactive marking (requires approval)                  ││
│ │                                                               ││
│ │ Minimum attendance threshold: [75%]                          ││
│ │ Grace period for medical leave: [7 days]                     ││
│ └─────────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────────┘
```

### FEES MODULE - Payment Logic Fix

```
┌─ Fees Module Configuration ────────────────────────────────────┐
│                                                                  │
│ 🔧 CRITICAL ISSUES DETECTED                                     │
│                                                                  │
│ ⚠️ Issue #3: Scholarship & Payment Status Logic                │
│ │ Problem: Scholarship not deducted from total_amount          │
│ │ Impact: Incorrect fee calculations                           │
│ │                                                               │
│ │ Resolution:                                                   │
│ │ [✓] Calculate: payable = total - scholarship - concession    │
│ │ [✓] Track: paid_amount separately                            │
│ │ [✓] Status: pending/partial/paid/overdue based on payable    │
│ │                                                               │
│ │ [Apply Fix] [View Migration]                                 │
│ └───────────────────────────────────────────────────────────────│
│                                                                  │
│ Waiver Approval Workflow:                                       │
│ ┌─────────────────────────────────────────────────────────────┐│
│ │ Fee Waiver Request:                                          ││
│ │ Student Request → Registrar Review → Principal Approval      ││
│ │                                                               ││
│ │ Auto-approve if amount < ₹[5000]                             ││
│ │ Require Principal approval if > ₹[5000]                      ││
│ │                                                               ││
│ │ Payment deadline: [15 days] after installment due date      ││
│ │ Late fee: ₹[100] per day after deadline                      ││
│ └─────────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────────┘
```

### RESULTS MODULE - Validation Rules

```
┌─ Results Module Configuration ─────────────────────────────────┐
│                                                                  │
│ 🔧 CRITICAL ISSUES DETECTED                                     │
│                                                                  │
│ ⚠️ Issue #4: Marks Validation Missing                          │
│ │ Problem: No validation for marks_obtained ≤ max_marks        │
│ │ Impact: Invalid results entered                              │
│ │                                                               │
│ │ Resolution:                                                   │
│ │ [✓] Add DB constraint: marks_obtained ≤ max_marks            │
│ │ [✓] Add validation: marks_obtained ≥ 0                       │
│ │ [✓] Add validation: grade matches marks range                │
│ │                                                               │
│ │ [Apply Fix] [View Migration]                                 │
│ └───────────────────────────────────────────────────────────────│
│                                                                  │
│ Result Entry Rules:                                             │
│ ┌─────────────────────────────────────────────────────────────┐│
│ │ Faculty can enter results for:                               ││
│ │ [✓] Assigned subjects only                                   ││
│ │ [✓] Within exam window: [Start Date] to [End Date]          ││
│ │                                                               ││
│ │ Approval Chain:                                              ││
│ │ Faculty Entry → HOD Review → Registrar Approval              ││
│ │                                                               ││
│ │ Auto-calculate: [✓] Grade [✓] SGPA [✓] CGPA [✓] ATKT        ││
│ └─────────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────────┘
```

## Configuration Export/Import

```
┌─ Configuration Management ─────────────────────────────────────┐
│                                                                  │
│ Export Configuration:                                           │
│ [Export All Modules] [Export Selected: Students, Fees]         │
│                                                                  │
│ Import Configuration:                                           │
│ [Choose File] configuration_backup_2024-01-15.json             │
│ [Preview Changes] [Import & Apply]                             │
│                                                                  │
│ Configuration History:                                          │
│ • 2024-01-15 10:30 - Updated Students module permissions       │
│ • 2024-01-14 15:45 - Fixed Fees calculation logic              │
│ • 2024-01-13 09:20 - Added Attendance validation rules         │
│                                                                  │
│ [View Full History] [Restore Previous Version]                 │
└──────────────────────────────────────────────────────────────────┘
```
