-- Backfill Department Data Script
-- This script populates department_id in existing records
-- Run after migrations are complete

-- Step 1: Populate students.department_id from programs
UPDATE students 
SET department_id = (
    SELECT p.department_id 
    FROM programs p 
    WHERE p.id = students.program_id
)
WHERE department_id IS NULL 
AND program_id IS NOT NULL;

-- Step 2: Populate subjects.department_id from programs
UPDATE subjects 
SET department_id = (
    SELECT p.department_id 
    FROM programs p 
    WHERE p.id = subjects.program_id
)
WHERE department_id IS NULL 
AND program_id IS NOT NULL;

-- Step 3: Populate attendance_records.department_id from students
UPDATE attendance_records 
SET department_id = (
    SELECT s.department_id 
    FROM students s 
    WHERE s.id = attendance_records.student_id
)
WHERE department_id IS NULL 
AND student_id IS NOT NULL;

-- Step 4: Populate exam_results.department_id from students
UPDATE exam_results 
SET department_id = (
    SELECT s.department_id 
    FROM students s 
    WHERE s.id = exam_results.student_id
)
WHERE department_id IS NULL 
AND student_id IS NOT NULL;

-- Step 5: Populate student_fees.department_id from students
UPDATE student_fees 
SET department_id = (
    SELECT s.department_id 
    FROM students s 
    WHERE s.id = student_fees.student_id
)
WHERE department_id IS NULL 
AND student_id IS NOT NULL;

-- Step 6: Populate faculty_assignments.department_id from subjects
UPDATE faculty_assignments 
SET department_id = (
    SELECT sub.department_id 
    FROM subjects sub 
    WHERE sub.id = faculty_assignments.subject_id
)
WHERE department_id IS NULL 
AND subject_id IS NOT NULL;

-- Verification queries
SELECT 'students' as table_name, 
       COUNT(*) as total, 
       SUM(CASE WHEN department_id IS NULL THEN 1 ELSE 0 END) as null_count
FROM students
UNION ALL
SELECT 'attendance_records', COUNT(*), SUM(CASE WHEN department_id IS NULL THEN 1 ELSE 0 END)
FROM attendance_records
UNION ALL
SELECT 'exam_results', COUNT(*), SUM(CASE WHEN department_id IS NULL THEN 1 ELSE 0 END)
FROM exam_results
UNION ALL
SELECT 'student_fees', COUNT(*), SUM(CASE WHEN department_id IS NULL THEN 1 ELSE 0 END)
FROM student_fees
UNION ALL
SELECT 'faculty_assignments', COUNT(*), SUM(CASE WHEN department_id IS NULL THEN 1 ELSE 0 END)
FROM faculty_assignments
UNION ALL
SELECT 'subjects', COUNT(*), SUM(CASE WHEN department_id IS NULL THEN 1 ELSE 0 END)
FROM subjects;
