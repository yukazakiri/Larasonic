<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Actions\Jetstream\AddTeamMember;

final class TeamMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::findOrFail(1);
        $team->load('owner');
        $addTeamMember = new AddTeamMember();

        // Create 10 users and add them to the team
        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) use ($team, $addTeamMember): void {
                // Assign random role (editor or reader)
                $role = fake()->randomElement(['Student', 'Student']);
                
                // Add user to team
                $addTeamMember->add(
                    user: $team->owner,
                    team: $team,
                    email: $user->email,
                    role: $role
                );
            });
    }
} 