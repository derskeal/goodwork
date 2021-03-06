<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Core\Models\Task;
use App\Core\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uses_scout()
    {
        $this->assertTrue(in_array('Laravel\Scout\Searchable', class_uses('App\Core\Models\Search')));
    }

    /** @test */
    public function index_resource_when_it_is_created()
    {
        $this->actingAs($this->user)->post('projects', [
            'name'        => 'New Project',
            'description' => 'Description',
        ]);

        $this->assertDatabaseHas('searches', [
            'name'        => 'New Project',
            'description' => 'Description',
        ]);
    }

    /** @test */
    public function delete_index_when_deleting_resource()
    {
        $this->index_resource_when_it_is_created();

        $projectId = Project::where('name', 'New Project')->first()->id;

        $this->actingAs($this->user)->delete("/projects/{$projectId}");

        $this->assertDatabaseMissing('searches', [
            'name'        => 'New Project',
            'description' => 'Description',
        ]);
    }

    /** @test */
    public function update_index_when_resource_is_updated()
    {
        $task = factory(Task::class)->create([
            'name'       => 'old name',
            'notes'      => 'old',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->put("/tasks/{$task->id}", [
            'name'          => 'Updated task',
            'notes'         => 'updated',
            'due_on'        => $task->due_on,
            'group_type'    => $task->taskable_type,
            'group_id'      => $task->taskable_id,
        ]);

        $this->assertDatabaseHas('searches', [
            'name'        => 'Updated task',
            'description' => 'updated',
        ]);

        $this->assertDatabaseMissing('searches', [
            'name'        => 'old name',
            'description' => 'old',
        ]);
    }
}
