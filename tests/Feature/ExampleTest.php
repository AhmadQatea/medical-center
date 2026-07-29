<?php

it('redirects the home page to public booking', function () {
    $this->get('/')
        ->assertRedirect('/book');
});
