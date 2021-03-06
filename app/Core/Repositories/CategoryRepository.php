<?php

namespace App\Core\Repositories;

use App\Core\Models\Category;

class CategoryRepository
{
    protected $model;

    public function __construct(Category $category)
    {
        $this->model = $category;
    }

    public function getAllCategories()
    {
        return $this->model->get();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }
}
