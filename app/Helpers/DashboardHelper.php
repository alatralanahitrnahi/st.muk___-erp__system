<?php

namespace App\Helpers;

class DashboardHelper
{
    public static function getDashboardTitle($userType): string
    {
        return match($userType) {
            'super-admin' => '⚡ PVGS Super Admin Dashboard',
            'principal' => '👑 PVGS Principal Dashboard',
            'registrar' => '📝 PVGS Registrar Dashboard',
            'faculty' => '👨🏫 PVGS Faculty Portal',
            'student' => '🎓 PVGS Student Portal',
            default => '🎓 PVGS ERP Dashboard'
        };
    }

    public static function getDashboardSubtitle($userType): string
    {
        return match($userType) {
            'super-admin' => 'Complete System Control',
            'principal' => 'Executive Overview',
            'registrar' => 'Student Records & Operations',
            'faculty' => 'Teaching & Assessment',
            'student' => 'Academic Progress',
            default => 'Academic Management'
        };
    }
}