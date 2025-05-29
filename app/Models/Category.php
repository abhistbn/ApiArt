<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color'
    ];

    /**
     * Boot method untuk auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
        
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Relasi ke Articles
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Scope untuk kategori yang memiliki artikel published
     */
    public function scopeWithPublishedArticles($query)
    {
        return $query->whereHas('articles', function($q) {
            $q->where('status', 'published')
              ->whereNotNull('published_at')
              ->where('published_at', '<=', now());
        });
    }
}