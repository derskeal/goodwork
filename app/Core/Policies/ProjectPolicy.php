<?php

namespace App\Core\Policies;

use App\Core\Models\User;
use App\Core\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the project.
     *
     * @param  \App\Core\Models\User $user
     * @param  \App\Core\Project     $project
     * @return mixed
     */
    public function view(User $user, Project $project)
    {
        return resolve('Authorization')->userHasPermissionTo('view', 'project', $project->id, false, 'project', $project->id);
    }

    /**
     * Determine whether the user can create projects.
     *
     * @param  \App\Core\Models\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return resolve('Authorization')->userHasPermissionTo('create', 'project');
    }

    /**
     * Determine whether the user can delete the project.
     *
     * @param  \App\Core\Models\User $user
     * @param  \App\Core\Project     $project
     * @return mixed
     */
    public function delete(User $user, Project $project)
    {
        return resolve('Authorization')->userHasPermissionTo('delete', 'project', $project->id, false, 'project', $project->id);
    }
}
