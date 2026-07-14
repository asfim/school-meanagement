<?php

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can manage academic programs', function () {
    $admin = User::factory()->create();

    // 1. Create Program
    $this->actingAs($admin)->post('/programs', [
        'name' => 'Computer Science',
        'code' => 'CSE',
        'duration_years' => 4,
    ])->assertRedirect();

    $this->assertDatabaseHas('programs', [
        'name' => 'Computer Science',
        'code' => 'CSE',
    ]);

    $program = Program::where('name', 'Computer Science')->first();

    // 2. Edit Program
    $this->actingAs($admin)->put("/programs/{$program->id}", [
        'name' => 'Computer Science & Engineering',
        'code' => 'CSE',
        'duration_years' => 4,
    ])->assertRedirect();

    $this->assertDatabaseHas('programs', [
        'id' => $program->id,
        'name' => 'Computer Science & Engineering',
    ]);

    // 3. Delete Program
    $this->actingAs($admin)->delete("/programs/{$program->id}")->assertRedirect();
    $this->assertDatabaseMissing('programs', [
        'id' => $program->id,
    ]);
});

test('admin can add subjects under a program', function () {
    $admin = User::factory()->create();
    $program = Program::factory()->create(['name' => 'CSE']);

    $this->actingAs($admin)->post('/subjects', [
        'name' => 'Data Structures',
        'code' => 'CSE201',
        'program_id' => $program->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('subjects', [
        'name' => 'Data Structures',
        'program_id' => $program->id,
    ]);
});
