<?php

namespace App\Models;

use App\Enums\NoticeCategory;
use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Active Record: one instance = one row in `notices`.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $body
 * @property NoticeCategory $category
 * @property Carbon|null $published_at
 */
class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory;

    /**
     * Mass-assignment whitelist: only these keys can be filled from
     * request data. `id` or `slug` sent by a malicious form are ignored.
     */
    protected $fillable = ['title', 'body', 'category', 'published_at'];

    protected function casts(): array
    {
        return [
            'category' => NoticeCategory::class,
            'published_at' => 'datetime',
        ];
    }

    /** Model event: generate a unique slug when a notice is created. */
    protected static function booted(): void
    {
        static::creating(function (Notice $notice) {
            $base = Str::slug($notice->title);
            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $notice->slug = $slug;
        });
    }

    /** Route model binding uses the slug: /notices/road-closure-via-roma */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Local scope: Notice::published()->get() */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Drafts (no date) and scheduled notices (date in the future). */
    public function scopeUnpublished(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '>', now()));
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }
}
