<?php

use App\Support\TimeFormat;

test('arabic 12-hour formatting', function (string $input, string $expected) {
    expect(TimeFormat::arabic($input))->toBe($expected);
})->with([
    ['09:00', '09:00 صباحاً'],
    ['11:30', '11:30 صباحاً'],
    ['12:00', '12:00 ظهراً'],
    ['13:00', '01:00 مساءً'],
    ['14:30', '02:30 مساءً'],
    ['16:00', '04:00 مساءً'],
]);

test('english 12-hour formatting', function () {
    expect(TimeFormat::english('14:30'))->toBe('02:30 PM');
});
