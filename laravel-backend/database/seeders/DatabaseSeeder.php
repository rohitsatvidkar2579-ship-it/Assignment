<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'Admin@123',
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'User@123',
            'role' => 'user',
        ]);

        $other = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => 'User@123',
            'role' => 'user',
        ]);

        $project = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Revamp the marketing website.',
            'created_by' => $admin->id,
        ]);

        $project->tasks()->createMany([
            [
                'title' => 'Design homepage',
                'description' => 'Create wireframes and mockups.',
                'status' => 'IN_PROGRESS',
                'priority' => 'HIGH',
                'due_date' => now()->addDays(5)->toDateString(),
                'assigned_to' => $user->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Write copy',
                'description' => 'Landing page content.',
                'status' => 'TODO',
                'priority' => 'MEDIUM',
                'due_date' => now()->subDays(3)->toDateString(),
                'assigned_to' => $user->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Set up analytics',
                'description' => 'Install tracking.',
                'status' => 'TODO',
                'priority' => 'LOW',
                'due_date' => now()->addDays(10)->toDateString(),
                'assigned_to' => $other->id,
                'created_by' => $admin->id,
            ],
        ]);

        Project::create([
            'name' => 'Mobile App',
            'description' => 'Companion mobile application.',
            'created_by' => $admin->id,
        ]);
    }
}
