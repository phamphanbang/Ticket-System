<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Searchable
{
  public function scopeSearch(Builder $query, ?string $searchTerm): Builder
  {
    if (!$searchTerm || empty($this->searchable)) {
      return $query;
    }

    return $query->where(function ($q) use ($searchTerm) {
      foreach ($this->searchable as $column) {
        $q->orWhere($column, 'like', '%' . $searchTerm . '%');
      }
    });
  }
}
