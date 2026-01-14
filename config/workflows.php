<?php

return [
    'student_admission' => [
        'states' => ['pending', 'registrar_review', 'hod_approved', 'principal_approved', 'rejected'],
        'transitions' => [
            'pending' => ['registrar_review', 'rejected'],
            'registrar_review' => ['hod_approved', 'rejected'],
            'hod_approved' => ['principal_approved', 'rejected'],
            'principal_approved' => [],
            'rejected' => []
        ],
        'approvers' => [
            'registrar_review' => ['registrar'],
            'hod_approved' => ['department_head'],
            'principal_approved' => ['principal']
        ],
        'department_validation' => [
            'registrar_review' => 'validate_student_documents',
            'hod_approved' => 'validate_department_capacity',
            'principal_approved' => 'validate_final_approval'
        ],
        'requires_department' => true,
        'description' => 'Student admission: Registrar → Department Head → Principal'
    ],

    'fee_waiver' => [
        'states' => ['requested', 'registrar_review', 'hod_review', 'principal_approved', 'rejected'],
        'transitions' => [
            'requested' => ['registrar_review', 'rejected'],
            'registrar_review' => ['hod_review', 'principal_approved', 'rejected'],
            'hod_review' => ['principal_approved', 'rejected'],
            'principal_approved' => [],
            'rejected' => []
        ],
        'approvers' => [
            'registrar_review' => ['registrar'],
            'hod_review' => ['department_head'],
            'principal_approved' => ['principal']
        ],
        'conditional_logic' => [
            'registrar_review' => [
                'condition' => 'amount <= 5000',
                'skip_to' => 'principal_approved',
                'description' => 'Waiver ≤ ₹5000: Skip HOD, go to Principal'
            ],
            'hod_review' => [
                'condition' => 'amount > 5000',
                'required' => true,
                'description' => 'Waiver > ₹5000: Requires HOD approval'
            ]
        ],
        'department_validation' => [
            'registrar_review' => 'validate_waiver_eligibility',
            'hod_review' => 'validate_department_budget',
            'principal_approved' => 'validate_institutional_policy'
        ],
        'requires_department' => true,
        'description' => 'Fee waiver: Student → Registrar → HOD (if > ₹5000) → Principal'
    ],

    'fee_payment' => [
        'states' => ['pending', 'partial', 'paid', 'overdue', 'waived'],
        'transitions' => [
            'pending' => ['partial', 'paid', 'overdue'],
            'partial' => ['paid', 'overdue'],
            'paid' => [],
            'overdue' => ['paid'],
            'waived' => []
        ],
        'approvers' => [],
        'requires_department' => true,
        'description' => 'Fee payment tracking (no approval workflow)'
    ],

    'lesson_plan_approval' => [
        'states' => ['draft', 'submitted', 'hod_approved', 'principal_approved', 'rejected'],
        'transitions' => [
            'draft' => ['submitted'],
            'submitted' => ['hod_approved', 'rejected'],
            'hod_approved' => ['principal_approved', 'rejected'],
            'principal_approved' => [],
            'rejected' => ['draft']
        ],
        'approvers' => [
            'hod_approved' => ['department_head'],
            'principal_approved' => ['principal']
        ],
        'department_validation' => [
            'submitted' => 'validate_lesson_plan_completeness',
            'hod_approved' => 'validate_curriculum_alignment',
            'principal_approved' => 'validate_naac_compliance'
        ],
        'requires_department' => true,
        'description' => 'Lesson plan: Faculty → HOD → Principal'
    ],

    'department_transfer' => [
        'states' => ['requested', 'source_hod_approved', 'target_hod_approved', 'principal_approved', 'rejected'],
        'transitions' => [
            'requested' => ['source_hod_approved', 'rejected'],
            'source_hod_approved' => ['target_hod_approved', 'rejected'],
            'target_hod_approved' => ['principal_approved', 'rejected'],
            'principal_approved' => [],
            'rejected' => []
        ],
        'approvers' => [
            'source_hod_approved' => ['department_head'],
            'target_hod_approved' => ['department_head'],
            'principal_approved' => ['principal']
        ],
        'department_validation' => [
            'source_hod_approved' => 'validate_source_clearance',
            'target_hod_approved' => 'validate_target_capacity',
            'principal_approved' => 'validate_transfer_eligibility'
        ],
        'requires_department' => true,
        'cross_department' => true,
        'description' => 'Department transfer: Student → Current HOD → Target HOD → Principal'
    ]
];
