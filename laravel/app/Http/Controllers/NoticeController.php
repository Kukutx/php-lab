<?php

namespace App\Http\Controllers;

use App\Enums\NoticeCategory;
use App\Http\Requests\NoticeRequest;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Resource controller: the 7 RESTful actions registered by
 * Route::resource('notices', NoticeController::class).
 */
class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $category = NoticeCategory::tryFrom((string) $request->query('category'));
        // Visitors see published notices; drafts and scheduled ones are one click away.
        $status = in_array($request->query('status'), ['published', 'drafts', 'all'], true)
            ? $request->query('status')
            : 'published';

        $notices = Notice::query()
            ->when($status === 'published', fn ($q) => $q->published())
            ->when($status === 'drafts', fn ($q) => $q->unpublished())
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->query('q').'%'))
            ->orderByDesc('published_at')
            ->paginate(6)
            ->withQueryString();

        return view('notices.index', [
            'notices' => $notices,
            'categories' => NoticeCategory::cases(),
            'activeCategory' => $category,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('notices.create', ['notice' => new Notice(['published_at' => now()]), 'categories' => NoticeCategory::cases()]);
    }

    public function store(NoticeRequest $request): RedirectResponse
    {
        $notice = Notice::create($request->validated());

        return redirect()->route('notices.show', $notice)->with('status', 'Notice created.');
    }

    /** Route model binding: Laravel finds the Notice by slug or returns 404. */
    public function show(Notice $notice): View
    {
        return view('notices.show', ['notice' => $notice]);
    }

    public function edit(Notice $notice): View
    {
        return view('notices.edit', ['notice' => $notice, 'categories' => NoticeCategory::cases()]);
    }

    public function update(NoticeRequest $request, Notice $notice): RedirectResponse
    {
        $notice->update($request->validated());

        return redirect()->route('notices.show', $notice)->with('status', 'Notice updated.');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return redirect()->route('notices.index')->with('status', 'Notice deleted.');
    }
}
