<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_url_redirects_to_the_admin_panel(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }

    public function test_the_admin_shortcuts_redirect_to_the_panel(): void
    {
        $this->get('/users')->assertRedirect('/admin/users');
        $this->get('/report')->assertRedirect('/admin/user-report-page');
        $this->get('/reports')->assertRedirect('/admin/user-report-page');
    }
}
