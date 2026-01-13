# PVGS ERP - Role Permission Matrix

## 🎯 5 Core Roles & Their Permissions

### **1. SUPER-ADMIN** 🔴
**Level**: System Administrator
**Access**: ALL PERMISSIONS (20 total)

```
✅ ALL STUDENT OPERATIONS
- view students, create students, edit students, delete students

✅ ALL PROGRAM MANAGEMENT  
- view programs, create programs, edit programs, delete programs

✅ ALL SUBJECT MANAGEMENT
- view subjects, create subjects, edit subjects, delete subjects

✅ ALL ATTENDANCE CONTROL
- view attendance, mark attendance

✅ ALL RESULTS MANAGEMENT
- view results, enter results

✅ ALL FINANCIAL CONTROL
- view fees, manage fees

✅ ALL REPORTING ACCESS
- view reports, generate reports
```

---

### **2. PRINCIPAL** 🟠  
**Level**: Institution Head
**Access**: 18 permissions (90% of system)

```
✅ STUDENT OPERATIONS (Full Control)
- view students, create students, edit students, delete students

✅ PROGRAM MANAGEMENT (Full Control)
- view programs, create programs, edit programs, delete programs

✅ SUBJECT MANAGEMENT (Full Control)  
- view subjects, create subjects, edit subjects, delete subjects

✅ ATTENDANCE MANAGEMENT
- view attendance, mark attendance

✅ RESULTS MANAGEMENT
- view results, enter results

✅ FINANCIAL MANAGEMENT
- view fees, manage fees

✅ REPORTING ACCESS
- view reports, generate reports

❌ CANNOT DELETE STUDENTS (vs Super-Admin)
```

---

### **3. ADMIN** 🟡
**Level**: Administrative Staff  
**Access**: 12 permissions (60% of system)

```
✅ STUDENT OPERATIONS (Limited)
- view students, create students, edit students
❌ delete students

✅ PROGRAM ACCESS (Read-Only)
- view programs
❌ create/edit/delete programs

✅ SUBJECT ACCESS (Read-Only)
- view subjects  
❌ create/edit/delete subjects

✅ ATTENDANCE MANAGEMENT
- view attendance, mark attendance

✅ RESULTS MANAGEMENT
- view results, enter results

✅ FINANCIAL MANAGEMENT
- view fees, manage fees

✅ REPORTING ACCESS
- view reports, generate reports
```

---

### **4. FACULTY** 🟢
**Level**: Teaching Staff
**Access**: 8 permissions (40% of system)

```
✅ STUDENT ACCESS (Read-Only)
- view students
❌ create/edit/delete students

✅ ACADEMIC ACCESS (Read-Only)
- view programs, view subjects
❌ create/edit/delete programs/subjects

✅ ATTENDANCE CONTROL
- view attendance, mark attendance

✅ RESULTS CONTROL  
- view results, enter results

❌ NO FINANCIAL ACCESS
❌ NO REPORTING ACCESS
```

---

### **5. STUDENT** 🔵
**Level**: End User
**Access**: 5 permissions (25% of system)

```
✅ ACADEMIC INFO (Read-Only)
- view programs, view subjects

✅ PERSONAL DATA (Read-Only)
- view attendance, view results, view fees

❌ NO CREATE/EDIT/DELETE PERMISSIONS
❌ NO ADMINISTRATIVE ACCESS
❌ NO REPORTING ACCESS
```

## 📊 Permission Hierarchy Summary

| Permission | Super-Admin | Principal | Admin | Faculty | Student |
|------------|-------------|-----------|-------|---------|---------|
| **Students** | CRUD | CRUD | CRU | R | - |
| **Programs** | CRUD | CRUD | R | R | R |
| **Subjects** | CRUD | CRUD | R | R | R |
| **Attendance** | RW | RW | RW | RW | R |
| **Results** | RW | RW | RW | RW | R |
| **Fees** | RW | RW | RW | - | R |
| **Reports** | RW | RW | RW | - | - |

**Legend**: C=Create, R=Read, U=Update, D=Delete, W=Write, -=No Access

## 🔐 Security Principles

1. **Least Privilege**: Each role has minimum required permissions
2. **Separation of Duties**: Clear boundaries between roles  
3. **Hierarchical Access**: Higher roles inherit lower role capabilities
4. **Data Protection**: Students cannot modify any system data
5. **Academic Integrity**: Only faculty+ can enter results