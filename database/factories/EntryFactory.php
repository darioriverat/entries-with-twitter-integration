<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Entry;
use Faker\Generator as Faker;

$factory->define(Entry::class, function (Faker $faker) {
    $title = $faker->text(50);
    $slug = preg_replace(['#\s#', '#\.#'], '-', $title);

    return [
        'title' => $title,
        'friendly_url' => $slug,
        'friendly_url_hash' => hash('md5', $slug),
        'description' => $faker->text(100),
        'content' => $faker->text,
    ];
});
