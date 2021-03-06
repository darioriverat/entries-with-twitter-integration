<?php

namespace App\Http\Controllers\Admin;

use App\API\Contracts\TwitterServiceContract;
use App\Entry;
use App\HiddenTweets;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEntry;
use App\Http\Requests\UpdateEntry;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('entries.index', [
            'entries' => Entry::orderBy('created_at', 'desc')->paginate(3),
        ]);
    }

    /**
     * Display a listing of the resource per user.
     *
     * @param int $userId
     * @param TwitterServiceContract $twitter
     * @return View
     */
    public function profile(int $userId, TwitterServiceContract $twitter): View
    {
        $user = User::findOrFail($userId);
        $entries = Entry::where('created_by', $user->id)->orderBy('created_at', 'desc')->paginate(3);

        $tweets = $twitter->getTweetsByUser($user->twitter_user) ?? [];
        $hiddenTweets = [];

        if ($tweets) {
            if (isset($tweets['errors'])) {
                Log::warning('Tweeter service error', $tweets['errors']);
            }

            $tweets_in = array_key_exists('id_str', $tweets[0] ?? [])
                ? array_column($tweets, 'id_str')
                : [];

            $hiddenTweets = HiddenTweets::whereIn('tweet_id', $tweets_in)
                ->where('user_id', $user->id)
                ->pluck('tweet_id')
                ->toArray();
        } else {
            Log::error('Tweeter service failed', []);
        }

        return view('entries.profile', compact('user', 'entries', 'tweets', 'hiddenTweets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Entry $entry
     * @return Response
     */
    public function create(Entry $entry)
    {
        return view('admin.entries.create', compact('entry'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreEntry $request
     * @return Response
     */
    public function store(StoreEntry $request)
    {
        $entry = Entry::create(array_merge($request->except('_token'), [
            'created_by' => Auth::id(),
            'friendly_url_hash' => hash('md5', $request->input('friendly_url')),
        ]));

        return redirect()->route('entries.show', compact('entry'))->with([
            'success' => __('entries.messages.created'),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Entry $entry
     * @return Response
     */
    public function show(Entry $entry)
    {
        return view('entries.show', compact('entry'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $userId
     * @param string $friendlyUrl
     * @return Response
     */
    public function showBySlug(int $userId, string $friendlyUrl)
    {
        $entry = Entry::where('created_by', User::findOrFail($userId)->id)
            ->where('friendly_url_hash', hash('md5', $friendlyUrl))
            ->get()->first();

        if (!$entry) {
            abort(404);
        }

        return view('entries.show', compact('entry'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Entry $entry
     * @return Response
     */
    public function edit(Entry $entry)
    {
        return view('admin.entries.edit', compact('entry'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateEntry $request
     * @param Entry $entry
     * @return Response
     */
    public function update(UpdateEntry $request, Entry $entry)
    {
        $entry->update(array_merge($request->except('_token')));

        return redirect()->route('entries.show', compact('entry'))->with([
            'success' => __('entries.messages.updated'),
        ]);
    }
}
