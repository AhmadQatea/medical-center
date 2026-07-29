<?php

use App\Services\WhatsAppService;

test('whatsapp normalize accepts international syria and strips double zero prefix', function (string $input, string $expected) {
    expect(app(WhatsAppService::class)->normalizeNumber($input))->toBe($expected);
})->with([
    'plus syria' => ['+963959422413', '963959422413'],
    'plain syria' => ['963959422413', '963959422413'],
    'double zero syria' => ['00963959422413', '963959422413'],
    'local syria 10' => ['0959422413', '963959422413'],
    'saudi local' => ['0512345678', '966512345678'],
    'saudi international' => ['+966512345678', '966512345678'],
]);
