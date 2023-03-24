<?php

namespace App\Http\Controllers\Game;

use App\Actions\Hydrate;
use App\Http\Controllers\Controller;
use App\Models\Content\Review;
use App\Models\Content\Tag;
use App\Models\Game\Level;
use App\Models\System\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LevelController extends Controller
{
    /**
     * Display level list (light search functions)
     */
    public function index(Request $request): Response
    {
        $attributes = [
            'id',
            'rating_overall',
            'rating_gameplay',
            'rating_visual',
            'rating_overall',
            'reviews_count',
        ];
        $directions = [
            'DESC',
            'ASC',
        ];
        $sorting = [
            'sortBy' => $request->integer('sortBy'),
            'sortDir' => $request->integer('sortDir'),
            'filter' => $request->integer('filter'),
        ];

        $sorting['sortBy'] = $sorting['sortBy'] < count($attributes) ? $sorting['sortBy'] : 0;
        $sorting['sortDir'] = $sorting['sortDir'] < count($directions) ? $sorting['sortDir'] : 0;
        $sorting['filter'] = $sorting['filter'] < 3 ? $sorting['filter'] : 0;

        $levels = Level::query();

        if (auth()->check()) {
            if ($sorting['filter'] === 1) {
                /**
                 * @var User $user
                 */
                $user = auth()->user();
                $levels = $user->reviewedLevels();
            } else if ($sorting['filter'] === 2) {
                /**
                 * @var User $user
                 */
                $user = auth()->user();
                $levels = $levels->whereDoesntHave('reviews', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            }
        }

        return Inertia::render('Levels/Index', [
            'levels' => $levels
                ->orderBy($attributes[$sorting['sortBy']], $directions[$sorting['sortDir']])
                ->withCount('reviews')
                ->paginate(10)
                ->appends($sorting),
            'filters' => $sorting,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): Response
    {
        $level = Hydrate::level($id)->load(['images', 'tags']);

        return Inertia::render('Levels/Show', [
            'level' => $level->load(['reviews', 'reviews.author', 'videos']),
            'review' => auth()->check() ? Review::query()
                ->where('level_id', $id)
                ->where('user_id', auth()->id())
                ->first() : null,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function tags(Level $level): Response
    {
        return Inertia::render('Levels/Tags', [
            'level' => $level->load('tags'),
            'tags' => Tag::all(),
        ]);
    }

    public function images(Level $level): Response
    {
        return Inertia::render('Levels/Images', [
            'level' => $level,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Level $level): Response
    {
        return Inertia::render('Levels/Edit', [
            'level' => $level,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Level $level): RedirectResponse
    {
        switch ($request->input('action')) {
            case 'update banner':
                $request->validate([
                    'content' => 'mimes:jpeg,jpg,png,webp,gif|required|max:5000',
                ]);
                $disk = Storage::disk('contabo');
                $old = $level->banner_url;
                $level->banner_url = config('app.storage_url') . $disk->putFile('levels/banners/', $request->file('content'), 'public');
                $level->save();
                if (Level::whereBannerUrl($old)->count() === 0) {
                    $disk->delete(substr($old, strlen(config('app.storage_url'))));
                }
                break;
            case 'update tags':
                //
                break;
        }

        return redirect()->back();
    }
}