<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class Statistics
{
    public static function all(): array
    {
        return [
            'users' => self::count(\App\Models\User::class),
            'threads' => self::count(\App\Models\Thread::class),
            'posts' => self::count(\App\Models\Post::class),
            'levels' => self::count(\App\Models\Level::class),
            'reviews' => self::count(\App\Models\Review::class),
            'videos' => self::count(\App\Models\Video::class),
            'playlists' => self::count(\App\Models\Playlist::class),
            'nongs' => self::count(\App\Models\Nong::class),
            'patreon' => (function () {
                $res = Http::withToken(config('hyperbolus.patreon_token'))
                    ->get('https://patreon.com/api/oauth2/v2/campaigns/1078668?include=goals&fields%5Bgoal%5D=description,amount_cents,completed_percentage');
                Cache::get('statistics:patreon', $res);
            })()
        ];
    }

    public static function count($model)
    {
        $table = app($model)->getTable();
        $key = 'statistics:' . $table;
        $value = Cache::get($key);
        if ($value === null) {
            $value = DB::table($table)->count();
            Cache::put($key, $value, now()->addHour());
        }
        return $value;
    }
}
