<?php

use App\Services\WhatsAppService;

test('whatsapp normalize accepts international syria and strips double zero prefix', function (string $input, string $expected) {
    expect(app(WhatsAppService::class)->normalizeNumber($input))->toBe($expected);
})->with([
    'plus syria' => ['+963999123456', '963999123456'],
    'plain syria' => ['963999123456', '963999123456'],
    'double zero syria' => ['00963999123456', '963999123456'],
    'local syria 10' => ['0999123456', '963999123456'],
    'saudi local' => ['0512345678', '966512345678'],
    'saudi international' => ['+966512345678', '966512345678'],
]);
