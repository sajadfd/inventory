<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(404);
});