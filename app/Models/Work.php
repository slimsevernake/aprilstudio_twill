<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Behaviors\HasPosition;
use A17\Twill\Models\Behaviors\Sortable;
use A17\Twill\Models\Model;
use Cartalyst\Tags\TaggableTrait;
use \ImageService;

class Work extends Model implements Sortable
{
    use HasBlocks, HasTranslation, HasSlug, HasMedias, HasFiles, HasPosition, TaggableTrait;

    protected $fillable = [
        'published',
        'title',
        'description',
        'position',
        'casestudy'
    ];

    public $translatedAttributes = [
         'title',
         'description',
         'casestudy'
     ];

    public $slugAttributes = [
         'title',
    ];

    public $checkboxes = [
        'published'
    ];

    public $mediasParams = [
        'cover' => [
            'default' => [
                [
                    'name' => 'landscape',
                    'ratio' => 16 / 9,
                ],
                [
                    'name' => 'portrait',
                    'ratio' => 3 / 4,
                ],
            ],
            'mobile' => [
                [
                    'name' => 'mobile',
                    'ratio' => 1,
                ],
            ],
        ],
    ];

    public function getSlugLocale($slugText) {
        foreach ($this->slugs as $slug) {
            if ($slug->slug === $slugText) {
                return $slug->locale;
            }
        }

        return false;
    }

    public function scopeForSlugIgnoreLocale($query, $slug) {
        return $query->whereHas('slugs', function ($query) use ($slug) {
            $query->whereSlug($slug);
            $query->whereActive(true);
        })->with(['slugs']);
    }

    public function scopeFromBucket($query, $bucketKey) {
        return $query
            ->leftJoin('features', 'works.id', '=', 'features.featured_id')
            ->select('works.*', 'features.position')
            ->where('features.bucket_key', '=', $bucketKey)
            ->where('features.featured_type', '=', 'works')
            ->orderBy('features.position', 'ASC');
    }

    public function getRelativeUrl() {
        $activeSlug = $this->getActiveSlug();
        return '/works/' . $activeSlug->slug;
    }

    public function rawImage($role, $crop = "default", $has_fallback = false, $media = null) {
        if (!$media) {
            $media = $this->findMedia($role, $crop);
        }

        if ($media) {
            return ImageService::getRawUrl($media->uuid);
        }

        if ($has_fallback) {
            return null;
        }

        return ImageService::getTransparentFallbackUrl();
    }

    public function blocks() {
        return $this->morphMany(Block::class, 'blockable')->orderBy('blocks.position', 'asc');
    }

    public function designers() {
        return $this->belongsToMany(Designer::class);
    }

    public function categories() {
        return $this->belongsToMany(Category::class);
    }

    public function areas() {
        return $this->belongsToMany(Area::class);
    }

    public function types() {
        return $this->belongsToMany(Type::class);
    }
}
