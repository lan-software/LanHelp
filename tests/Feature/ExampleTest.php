<?php

test('home redirects to LanCore SSO when enabled', function () {
    // LanCore is enabled in .env — unauthenticated visits to / should redirect.
    $this->get(route('home'))->assertRedirect();
});
