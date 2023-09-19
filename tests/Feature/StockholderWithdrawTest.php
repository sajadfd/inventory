<?php

it('has stockholderwithdraw page', function () {
    $response = $this->get('/stockholderwithdraw');

    $response->assertStatus(200);
});
