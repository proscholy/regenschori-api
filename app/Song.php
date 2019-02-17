<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

/**
 * App\Song
 *
 * @property int                                                            $id
 * @property string|null                                                    $name
 * @property \Carbon\Carbon|null                                            $created_at
 * @property \Carbon\Carbon|null                                            $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Author[]    $authors
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SongLyric[] $song_lyrics
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Song whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Song whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Song whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null                                                       $visits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SongLyric[] $songLyrics
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Song whereCreatedAt($value)
 */
class Song extends Model
{
    protected $fillable = ['name', 'lyrics'];

    public function authors()
    {
        // TODO: return all authors of the SongLyrics combined ... but rather not necessary
        Log::error("get authors not implemented");
    }

    /**
     * Returns all SongLyrics instances
     */
    public function song_lyrics()
    {
        return $this->hasMany(SongLyric::class);
    }

    public function getDominantSongLyric($id_exclude)
    {
        // TODO comment why this is here :D
        $candidates = $this->song_lyrics()->where('id', '!=', $id_exclude)->orderBy('is_original', 'desc');

        return $candidates->first();
    }


    public function getOriginalLyric()
    {
        return $this->song_lyrics()->where('is_original', 1)->get()->first();
    }
}
