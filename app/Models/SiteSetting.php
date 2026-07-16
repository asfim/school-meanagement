<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'logo_path',
        'institute_name',
        'tagline',
        'favicon_path',
        'about_title',
        'about_description',
        'about_stats',
        'contact_address',
        'contact_phone',
        'contact_email',
        'contact_hours',
    ];

    protected function casts(): array
    {
        return [
            'about_stats' => 'array',
        ];
    }
}
