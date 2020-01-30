<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Work;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class WorkController
{
    private function getBucketWorks($bucket_key) {
        return Work::where('published', true)
                        ->whereHas('slugs', function (Builder $query) {
                            $query
                                ->where('locale', '=', app()->getLocale())
                                ->where('active', '=', 1);
                        })
                        ->fromBucket($bucket_key)
                        ->get();
    }

    private function chunkModelArray($models, $chunkSize) {
        $array_of_models = [];
        foreach ($models as $model) {
            $array_of_models[] = $model;
        }
        return array_chunk($array_of_models, $chunkSize);
    }

    public function index() {
        $favouritedWorks = $this->getBucketWorks('home_favourite_works');
        $otherWorks = $this->getBucketWorks('home_other_works');

        return view('welcome', [
            'favouriteWorks' => $this->chunkModelArray($favouritedWorks, 5),
            'otherWorks'     => $this->chunkModelArray($otherWorks, 5),
        ]);
    }

    /**
     * @param  string $slug
     * @return View
     */
    public function show($slug)
    {
        $work = Work::forSlugIgnoreLocale($slug)->with('blocks')->first();

        if (!$work) {
            return response(view('errors.404'), 404);
        }

        $slugLocale = $work->getSlugLocale($slug);
        $localesDiffer = $slugLocale != app()->getLocale();

        if ($localesDiffer) {
            Session::put('applocale', $slugLocale);
            app()->setLocale($slugLocale);
        }

        $nextWork = Work::where('created_at', '>', $work->created_at)->first();

        $renderedBlocks = $work->renderBlocks(false, [
            'quote'            => 'blocks/quote',
            'youtube'          => 'blocks/youtube',
            'vimeo'            => 'blocks/vimeo',
            'full_width_image' => 'blocks/full_width_image',
            'fixed_image_grid' => 'blocks/fixed_image_grid',
            'fluid_image_grid' => 'blocks/fluid_image_grid'
        ]);

        return view('work', [
            'work'           => $work,
            'renderedBlocks' => $renderedBlocks,
            'nextWork'       => $nextWork,
        ]);
    }
}
