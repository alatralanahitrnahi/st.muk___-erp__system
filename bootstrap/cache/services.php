<?php return array (
  'providers' => 
  array (
    0 => 'Laravel\\Sail\\SailServiceProvider',
    1 => 'Laravel\\Tinker\\TinkerServiceProvider',
    2 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
    3 => 'Carbon\\Laravel\\ServiceProvider',
    4 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    5 => 'Termwind\\Laravel\\TermwindServiceProvider',
    6 => 'Spatie\\Permission\\PermissionServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
    1 => 'Carbon\\Laravel\\ServiceProvider',
    2 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    3 => 'Termwind\\Laravel\\TermwindServiceProvider',
    4 => 'Spatie\\Permission\\PermissionServiceProvider',
  ),
  'deferred' => 
  array (
    'Laravel\\Sail\\Console\\InstallCommand' => 'Laravel\\Sail\\SailServiceProvider',
    'Laravel\\Sail\\Console\\PublishCommand' => 'Laravel\\Sail\\SailServiceProvider',
    'command.tinker' => 'Laravel\\Tinker\\TinkerServiceProvider',
  ),
  'when' => 
  array (
    'Laravel\\Sail\\SailServiceProvider' => 
    array (
    ),
    'Laravel\\Tinker\\TinkerServiceProvider' => 
    array (
    ),
  ),
);