<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

class ActivityTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->user2 = factory(\App\Core\Models\User::class)->create(['role_id' => 2]);
        $this->user3 = factory(\App\Core\Models\User::class)->create(['role_id' => 2]);
    }

    /** @test */
    public function owner_can_see_all_recent_activity()
    {
        $this->actingAs($this->user2)->post('projects', [
            'name'        => 'New Project',
            'description' => 'Description for new project',
            'owner_id'    => $this->user2->id,
        ]);

        $this->actingAs($this->user)->get('admin/activities')
            ->assertJsonFragment([
                'status'       => 'success',
                'description'  => 'created',
                'causer_id'    => (string) $this->user2->id,
                'subject_type' => 'project',
            ]);
    }

    /** @test */
    public function owner_can_filter_activity_by_user()
    {
        $this->actingAs($this->user2)->post('projects', [
            'name'        => 'New Project by user 1',
            'description' => 'Description for new project',
            'owner_id'    => $this->user2->id,
        ]);

        $this->actingAs($this->user3)->post('projects', [
            'name'        => 'New Project by user 2',
            'description' => 'Description for new project',
            'owner_id'    => $this->user3->id,
        ]);

        $this->actingAs($this->user)->call('GET', 'admin/activities', ['user' => $this->user2->id])
            ->assertJsonFragment([
                'status'       => 'success',
                'description'  => 'created',
                'causer_id'    => (string) $this->user2->id,
                'subject_type' => 'project',
            ])
            ->assertJsonMissing([
                'causer_id'    => (string) $this->user3->id,
            ]);
    }

    /** @test */
    public function owner_can_filter_activity_by_date()
    {
        $this->actingAs($this->user2)->post('projects', [
            'name'        => 'New Project by user 1',
            'description' => 'Description for new project',
            'owner_id'    => $this->user2->id,
        ]);

        $this->actingAs($this->user3)->post('projects', [
            'name'        => 'New Project by user 2',
            'description' => 'Description for new project',
            'owner_id'    => $this->user3->id,
        ]);

        Activity::where('causer_id', $this->user3->id)->update(['created_at' => Carbon::now()->addDay()]);

        $this->actingAs($this->user)->call('GET', 'admin/activities', ['date' => Carbon::now()->toDateString()])
            ->assertJsonFragment([
                'status'       => 'success',
                'description'  => 'created',
                'causer_id'    => (string) $this->user2->id,
                'subject_type' => 'project',
                'subject_id'   => '1',
            ])
            ->assertJsonMissing([
                'causer_id'    => (string) $this->user3->id,
                'subject_id'   => '2',
            ]);
    }

    /** @test */
    public function owner_can_filter_activity_by_date_and_user()
    {
        $this->actingAs($this->user2)->post('projects', [
            'name'        => 'New Project by user 1',
            'description' => 'Description for new project',
            'owner_id'    => $this->user2->id,
        ]);

        $this->actingAs($this->user2)->post('projects', [
            'name'        => 'Another Project by user 1',
            'description' => 'Description for another project',
            'owner_id'    => $this->user2->id,
        ]);

        $this->actingAs($this->user3)->post('projects', [
            'name'        => 'New Project by user 2',
            'description' => 'Description for new project',
            'owner_id'    => $this->user3->id,
        ]);

        Activity::where('id', 2)->update(['created_at' => Carbon::now()->addDay()]);

        $this->actingAs($this->user)->call('GET', 'admin/activities', ['user' => $this->user2->id, 'date' => Carbon::now()->toDateString()])
            ->assertJsonFragment([
                'status'       => 'success',
                'description'  => 'created',
                'causer_id'    => (string) $this->user2->id,
                'subject_type' => 'project',
                'subject_id'   => '1',
            ])
            ->assertJsonMissing([
                'causer_id'    => (string) $this->user3->id,
                'subject_id'   => '2',
            ]);
    }
}
