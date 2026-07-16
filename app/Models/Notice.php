<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'attachment_path',
        'image_path',
        'publish_date',
        'expiry_date',
        'target_audience',
        'posted_by',
        'status',
    ];

    /**
     * Get the casts structure.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($notice) {
            if (empty($notice->slug) || $notice->isDirty('title')) {
                $notice->slug = static::generateUniqueSlug($notice->title, $notice->id);
            }
        });
    }

    /**
     * Generate a unique slug for a notice.
     */
    protected static function generateUniqueSlug(string $title, ?int $id = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug.'-'.$count++;
        }

        return $slug;
    }
}
