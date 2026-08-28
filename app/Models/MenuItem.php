<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'group',
        'label',
        'route_name',
        'url',
        'icon',
        'sort_order',
        'is_active',
        'min_role',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getUrlAttribute(): ?string
    {
        if ($this->route_name && route()->has($this->route_name)) {
            return route($this->route_name);
        }

        return $this->url;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSidebar($query)
    {
        return $query->where('group', 'sidebar');
    }

    public function scopeTopbar($query)
    {
        return $query->where('group', 'topbar');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
