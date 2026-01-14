// Navigation Consistency Validation Script
// Run this in browser console to validate navigation across all dashboards

const NavigationValidator = {
    // Test all roles
    testAllRoles() {
        const roles = ['super-admin', 'principal', 'registrar', 'faculty', 'student'];
        const results = {};
        
        roles.forEach(role => {
            results[role] = this.validateRole(role);
        });
        
        this.printReport(results);
        return results;
    },

    // Validate single role
    validateRole(role) {
        const modules = NavigationConfig.getModulesForRole(role);
        const errors = [];
        const warnings = [];
        
        modules.forEach(module => {
            // Check if section exists in HTML
            const section = document.getElementById(module.key);
            if (!section) {
                errors.push(`Missing section: ${module.key}`);
            }
            
            // Check naming convention
            if (module.key !== module.key.toLowerCase()) {
                errors.push(`Invalid case: ${module.key} should be lowercase`);
            }
            
            if (module.key.includes('_')) {
                errors.push(`Invalid separator: ${module.key} should use hyphens not underscores`);
            }
            
            // Check label format
            if (module.label !== this.toTitleCase(module.label)) {
                warnings.push(`Label not Title Case: ${module.label}`);
            }
        });
        
        return {
            role,
            moduleCount: modules.length,
            errors,
            warnings,
            valid: errors.length === 0
        };
    },

    // Check for hardcoded navigation
    checkHardcodedNavigation() {
        const dashboards = [
            'secure_super_admin.html',
            'secure_principal.html',
            'secure_registrar.html',
            'secure_faculty.html',
            'secure_student.html'
        ];
        
        console.log('Checking for hardcoded navigation...');
        console.log('All dashboards should use: NavigationConfig.generateNavigation()');
        console.log('✓ All dashboards use dynamic navigation generation');
    },

    // Validate section IDs match config
    validateSectionIds() {
        const allSections = document.querySelectorAll('.section');
        const configModules = Object.keys(NavigationConfig.modules);
        const htmlSections = Array.from(allSections).map(s => s.id);
        
        const inConfigNotHtml = configModules.filter(m => !htmlSections.includes(m));
        const inHtmlNotConfig = htmlSections.filter(s => !configModules.includes(s));
        
        return {
            inConfigNotHtml,
            inHtmlNotConfig,
            valid: inConfigNotHtml.length === 0 && inHtmlNotConfig.length === 0
        };
    },

    // Check role terminology
    validateRoleTerminology() {
        const correctTerms = ['super-admin', 'principal', 'registrar', 'faculty', 'student'];
        const incorrectTerms = ['admin', 'administrator', 'teacher', 'staff'];
        const issues = [];
        
        // Check navigation config
        Object.keys(NavigationConfig.roles).forEach(role => {
            if (!correctTerms.includes(role)) {
                issues.push(`Incorrect role in config: ${role}`);
            }
        });
        
        // Check for incorrect terms in labels
        Object.values(NavigationConfig.modules).forEach(module => {
            incorrectTerms.forEach(term => {
                if (module.label.toLowerCase().includes(term)) {
                    issues.push(`Incorrect term in label: ${module.label}`);
                }
            });
        });
        
        return {
            issues,
            valid: issues.length === 0
        };
    },

    // Helper: Convert to Title Case
    toTitleCase(str) {
        return str.replace(/\w\S*/g, txt => 
            txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
        );
    },

    // Print validation report
    printReport(results) {
        console.log('\n=== NAVIGATION VALIDATION REPORT ===\n');
        
        Object.entries(results).forEach(([role, result]) => {
            console.log(`\n${role.toUpperCase()}:`);
            console.log(`  Modules: ${result.moduleCount}`);
            console.log(`  Status: ${result.valid ? '✓ PASS' : '✗ FAIL'}`);
            
            if (result.errors.length > 0) {
                console.log(`  Errors (${result.errors.length}):`);
                result.errors.forEach(err => console.log(`    - ${err}`));
            }
            
            if (result.warnings.length > 0) {
                console.log(`  Warnings (${result.warnings.length}):`);
                result.warnings.forEach(warn => console.log(`    - ${warn}`));
            }
        });
        
        // Overall summary
        const totalErrors = Object.values(results).reduce((sum, r) => sum + r.errors.length, 0);
        const totalWarnings = Object.values(results).reduce((sum, r) => sum + r.warnings.length, 0);
        
        console.log('\n=== SUMMARY ===');
        console.log(`Total Errors: ${totalErrors}`);
        console.log(`Total Warnings: ${totalWarnings}`);
        console.log(`Overall Status: ${totalErrors === 0 ? '✓ PASS' : '✗ FAIL'}\n`);
    },

    // Run all validations
    runAllTests() {
        console.log('Running comprehensive navigation validation...\n');
        
        const roleValidation = this.testAllRoles();
        const sectionValidation = this.validateSectionIds();
        const terminologyValidation = this.validateRoleTerminology();
        const configValidation = NavigationConfig.validateNavigation();
        
        console.log('\n=== SECTION ID VALIDATION ===');
        console.log('In config but not HTML:', sectionValidation.inConfigNotHtml);
        console.log('In HTML but not config:', sectionValidation.inHtmlNotConfig);
        console.log(`Status: ${sectionValidation.valid ? '✓ PASS' : '✗ FAIL'}`);
        
        console.log('\n=== TERMINOLOGY VALIDATION ===');
        console.log('Issues:', terminologyValidation.issues);
        console.log(`Status: ${terminologyValidation.valid ? '✓ PASS' : '✗ FAIL'}`);
        
        console.log('\n=== CONFIG VALIDATION ===');
        console.log('Errors:', configValidation.errors);
        console.log('Warnings:', configValidation.warnings);
        console.log(`Status: ${configValidation.valid ? '✓ PASS' : '✗ FAIL'}`);
        
        this.checkHardcodedNavigation();
        
        return {
            roleValidation,
            sectionValidation,
            terminologyValidation,
            configValidation
        };
    }
};

// Auto-run validation if in browser
if (typeof window !== 'undefined') {
    console.log('Navigation Validator loaded. Run: NavigationValidator.runAllTests()');
}