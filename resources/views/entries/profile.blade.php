@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-8">
                <h2>{{ $user->name }} <small class="text-muted">entries</small> </h2><br />

                @include('entries.__list')
            </div>
            <div class="col-12 col-md-4">
                <h5 class="text-primary">Latest tweets</h5><br />

                @if (!$tweets || isset($tweets['errors']))
                    @include('common.__alert', ['type' => 'warning', 'message' => 'No tweets'])
                @endif

                @php
                    $owner = $user->id == \Illuminate\Support\Facades\Auth::id();
                @endphp

                @if ($tweets && !isset($tweets['errors']))
                    @foreach ($tweets as $tweet)
                        @php $hidden = in_array($tweet['id_str'], $hiddenTweets); @endphp

                        @if ($owner || !$hidden)
                            <div
                                class="media @if ($hidden) medium-opacity @endif"
                                id="tweet-{{ $tweet['id_str'] }}"
                                style="position: relative;">
                                <img src="{{ $tweet['user']['profile_image_url'] }}" class="align-self-start mr-3" alt="...">
                                <div class="media-body">
                                    <div class="row justify-content-between">
                                        <div class="col-7">
                                            <h5 class="mt-0">
                                                {{ $tweet['user']['name'] }}
                                                <small class="text-muted">
                                                    <a href="https://twitter.com/{{ $user->twitter_user }}" target="_blank">
                                                        {{ '@'.$user->twitter_user }}
                                                    </a>
                                                </small>
                                            </h5>
                                        </div>
                                        <div class="col-5">
                                            @auth
                                                @if ($owner)
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-danger btn-sm @if ($hidden) active cursor-not-allowed @endif"
                                                        title="Hide this tweet"
                                                        id="btn-hide-{{ $tweet['id_str'] }}"
                                                        data-id="{{ $tweet['id_str'] }}"
                                                        data-role="{{ !$hidden ? 'btn-tweet-action' : '' }}"
                                                        data-resource="{{ route('admin.tweets.store') }}"
                                                        data-method="post"
                                                        data-field-ok="created"
                                                        data-container="#tweet-{{ $tweet['id_str'] }}"
                                                        data-opaque-container-on-success="true"
                                                        data-message="{{ __('Tweet hided') }}"
                                                        data-toggle-btn="#btn-unhide-{{ $tweet['id_str'] }}"
                                                    >
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-success btn-sm @if (!$hidden) active cursor-not-allowed @endif"
                                                        title="Unhide this tweet"
                                                        id="btn-unhide-{{ $tweet['id_str'] }}"
                                                        data-id="{{ $tweet['id_str'] }}"
                                                        data-role="{{ $hidden ? 'btn-tweet-action' : '' }}"
                                                        data-resource="{{ route('admin.tweets.destroy', $tweet['id_str']) }}"
                                                        data-method="delete"
                                                        data-field-ok="deleted"
                                                        data-container="#tweet-{{ $tweet['id_str'] }}"
                                                        data-opaque-container-on-success="false"
                                                        data-message="{{ __('Tweet unhided') }}"
                                                        data-toggle-btn="#btn-hide-{{ $tweet['id_str'] }}"
                                                    >
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                    <small class="text-muted"><time itemprop="datePublished" datetime="{{ $tweet['created_at'] }}">{{ $tweet['created_at'] }}</time></small><br/>
                                    <p class="mt-2">{{ $tweet['text'] }}</p>
                                </div>
                            </div>
                        @endif

                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection
