<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    protected $fillable = [
        'about_text',
        'contact_phone',
        'contact_email',
        'contact_address',
        'google_maps_iframe',
        'instagram_url',
        'facebook_url',
        'tiktok_url',
        'youtube_url',
        'blog_header_image',
        'whatsapp_number',
        'logo',
        'logo_dark',
        'gallery_parallax_bg',
    ];

    // Helper method untuk mendapatkan settings
    public static function getSettings()
    {
        return self::first() ?? new self();
    }
}
