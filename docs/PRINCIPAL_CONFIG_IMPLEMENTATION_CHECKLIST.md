# Principal Configuration System - Implementation Checklist

## Priority 1: Critical Schema Fixes (Week 1)

### Students Module - Status Field Resolution
- [x] Create migration to rename `status` → `enrollment_status`
- [ ] Add `application_status` field (pending/under_review/approved/rejected)
- [ ] Update Student model with new field accessors
- [ ] Update admission workflow to use `application_status`
- [ ] Update enrollment workflow to use `enrollment_status`
- [ ] Test admission → enrollment transition
- [ ] Update API endpoints to return both fields
- [ ] Update frontend forms to use correct fields

**Validation:**
```sql
-- Test query
SELECT id, application_status, enrollment_status 
FROM students 
WHERE application_status = 'approved' AND enrollment_status = 'active';
```

### Fees Module - Payment Calculation Fix
- [x] Create migration to add `payable_amount` (computed column)
- [x] Create migration to add `balance_amount` (computed column)
- [ ] Update FeeCalculationService to use new fields
- [ ] Add validation: `paid_amount <= payable_amount`
- [ ] Update payment_status logic based on balance_amount
- [ ] Test scholarship deduction: total - scholarship - concession = payable
- [ ] Update fee receipt generation
- [ ] Update payment history tracking

**Validation:**
```sql
-- Test calculation
SELECT 
    total_amount,
    scholarship_amount,
    concession_amount,
    payable_amount,
    paid_amount,
    balance_amount,
    payment_status
FROM student_fees
WHERE payable_amount != (total_amount - COALESCE(scholarship_amount, 0) - COALESCE(concession_amount, 0));
```

### Attendance Module - Component Type Resolution
- [x] Create migration to add `component_type` field
- [ ] Update AttendanceController to handle component_type
- [ ] Add validation: theory uses subject_id, lab uses subject_component_id
- [ ] Update attendance marking UI to show component type
- [ ] Test theory attendance marking
- [ ] Test lab/practical attendance marking
- [ ] Update attendance reports to group by component_type
- [ ] Add index for performance: (subject_id, component_type, date)

**Validation:**
```sql
-- Test component tracking
SELECT component_type, COUNT(*) 
FROM attendance 
GROUP BY component_type;
```

### Results Module - Marks Validation
- [x] Create migration to add CHECK constraint
- [ ] Add Laravel validation rules in ResultController
- [ ] Test marks_obtained <= max_marks constraint
- [ ] Test marks_obtained >= 0 constraint
- [ ] Add frontend validation before submission
- [ ] Update result entry form with max_marks display
- [ ] Test grade calculation based on marks
- [ ] Add error messages for validation failures

**Validation:**
```sql
-- Test constraint
INSERT INTO student_results (marks_obtained, max_marks) 
VALUES (110, 100); -- Should fail
```

## Priority 2: Configuration System (Week 2-3)

### Database Schema
- [x] Create `module_permissions` table
- [x] Create `visibility_rules` table
- [x] Create `approval_chains` table
- [x] Create `config_history` table
- [ ] Run migrations on development database
- [ ] Seed default permissions
- [ ] Test permission queries performance
- [ ] Add indexes for frequently queried columns

### Backend API
- [x] Create PrincipalConfigController
- [ ] Add routes to api.php with principal middleware
- [ ] Implement getModulePermissions endpoint
- [ ] Implement updatePermissions endpoint
- [ ] Implement getVisibilityRules endpoint
- [ ] Implement updateVisibilityRules endpoint
- [ ] Implement getApprovalChains endpoint
- [ ] Implement updateApprovalChain endpoint
- [ ] Implement exportConfig endpoint
- [ ] Implement importConfig endpoint
- [ ] Add validation for all endpoints
- [ ] Add authorization checks (principal only)
- [ ] Test all endpoints with Postman

### Frontend UI
- [x] Create principal-config-manager.js
- [ ] Add configuration section to secure_principal.html
- [ ] Implement permission matrix UI
- [ ] Implement visibility rules UI
- [ ] Implement approval chain UI
- [ ] Add module tabs (Students, Attendance, Fees, Results)
- [ ] Implement role preview functionality
- [ ] Add export/import buttons
- [ ] Add configuration history viewer
- [ ] Style with Tailwind CSS
- [ ] Test responsive design
- [ ] Add loading states and error handling

## Priority 3: Permission Enforcement (Week 4)

### Middleware & Guards
- [ ] Create PermissionMiddleware to check module_permissions
- [ ] Create VisibilityScope to filter queries based on visibility_rules
- [ ] Update existing controllers to use PermissionMiddleware
- [ ] Apply VisibilityScope to Student queries
- [ ] Apply VisibilityScope to Attendance queries
- [ ] Apply VisibilityScope to Fee queries
- [ ] Apply VisibilityScope to Result queries
- [ ] Test permission denial (403 responses)
- [ ] Test visibility filtering (faculty sees only assigned students)

### Frontend Permission Checks
- [ ] Update navigation-config.js to check module_permissions
- [ ] Hide/disable buttons based on permissions
- [ ] Update student list to respect visibility rules
- [ ] Update attendance marking to respect visibility rules
- [ ] Update fee management to respect visibility rules
- [ ] Update result entry to respect visibility rules
- [ ] Add permission-based field hiding
- [ ] Test as Registrar role
- [ ] Test as Faculty role
- [ ] Test as Student role

## Priority 4: Approval Workflows (Week 5)

### Workflow Engine
- [ ] Create ApprovalWorkflowService
- [ ] Implement startWorkflow method
- [ ] Implement approveStep method
- [ ] Implement rejectStep method
- [ ] Implement getWorkflowStatus method
- [ ] Add workflow state tracking table
- [ ] Test admission approval workflow
- [ ] Test fee waiver workflow
- [ ] Test result publish workflow
- [ ] Add email notifications for approvals
- [ ] Add workflow history logging

### UI Components
- [ ] Create approval request form
- [ ] Create approval pending list
- [ ] Create approval history viewer
- [ ] Add approve/reject buttons
- [ ] Add approval comments field
- [ ] Test workflow UI as Registrar
- [ ] Test workflow UI as Principal
- [ ] Add workflow status badges

## Priority 5: Testing & Validation (Week 6)

### Unit Tests
- [ ] Test PrincipalConfigController methods
- [ ] Test PermissionMiddleware
- [ ] Test VisibilityScope
- [ ] Test ApprovalWorkflowService
- [ ] Test fee calculation with new fields
- [ ] Test attendance component type logic
- [ ] Test result marks validation
- [ ] Test student status transitions

### Integration Tests
- [ ] Test complete admission workflow
- [ ] Test complete fee payment workflow
- [ ] Test complete result entry workflow
- [ ] Test permission changes affecting UI
- [ ] Test visibility rule changes affecting queries
- [ ] Test approval chain modifications
- [ ] Test configuration export/import
- [ ] Test configuration history tracking

### User Acceptance Testing
- [ ] Principal can configure module permissions
- [ ] Principal can set visibility rules
- [ ] Principal can modify approval chains
- [ ] Registrar sees correct permissions
- [ ] Faculty sees only assigned students
- [ ] Student sees only own records
- [ ] Approval workflows function correctly
- [ ] Configuration export/import works
- [ ] All critical issues are resolved

## Priority 6: Documentation & Training (Week 7)

### Technical Documentation
- [ ] Document database schema changes
- [ ] Document API endpoints
- [ ] Document permission system architecture
- [ ] Document visibility rules logic
- [ ] Document approval workflow engine
- [ ] Create developer guide for adding new modules
- [ ] Create troubleshooting guide

### User Documentation
- [ ] Create Principal configuration guide
- [ ] Create permission matrix reference
- [ ] Create visibility rules reference
- [ ] Create approval workflow guide
- [ ] Create video tutorials
- [ ] Create FAQ document

### Training Materials
- [ ] Prepare Principal training session
- [ ] Prepare Registrar training session
- [ ] Prepare Faculty training session
- [ ] Create quick reference cards
- [ ] Schedule training sessions

## Rollout Plan

### Phase 1: Development Environment (Week 1-3)
- Complete critical schema fixes
- Build configuration system
- Test with sample data

### Phase 2: Staging Environment (Week 4-5)
- Deploy to staging
- Implement permission enforcement
- Test approval workflows
- Fix bugs

### Phase 3: Production Rollout (Week 6-7)
- Deploy to production (off-peak hours)
- Run data migration scripts
- Configure default permissions
- Monitor for issues
- Provide user training

## Success Metrics

- [ ] All 4 critical schema issues resolved
- [ ] Zero permission bypass vulnerabilities
- [ ] Configuration changes take effect immediately
- [ ] Approval workflows complete within 24 hours
- [ ] 100% test coverage for permission system
- [ ] Principal can configure without developer help
- [ ] Export/import works for disaster recovery
- [ ] Performance: Permission checks < 50ms
- [ ] User satisfaction: 90%+ approval rating

## Risk Mitigation

### High Risk: Data Migration Failures
- **Mitigation**: Test migrations on copy of production data
- **Rollback**: Keep backup before migration
- **Validation**: Run data integrity checks post-migration

### Medium Risk: Permission Misconfiguration
- **Mitigation**: Require confirmation for critical permission changes
- **Rollback**: Configuration history allows reverting changes
- **Validation**: Automated tests for common permission scenarios

### Low Risk: Performance Degradation
- **Mitigation**: Add database indexes for permission queries
- **Rollback**: Cache permission checks
- **Validation**: Load testing with 1000+ concurrent users
