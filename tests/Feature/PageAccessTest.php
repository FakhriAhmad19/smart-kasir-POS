<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_returns_successful_response(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_dashboard_renders_successfully(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');
        $this->actingAs($owner)->get('/dashboard')->assertStatus(200);
    }

    public function test_categories_page_renders_successfully(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');
        $this->actingAs($owner)->get('/categories')->assertStatus(200);
    }

    public function test_products_pages_render_successfully(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');
        $this->actingAs($owner)->get('/products')->assertStatus(200);
        $this->actingAs($owner)->get('/products/create')->assertStatus(200);
    }

    public function test_reports_pages_render_successfully(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');
        $this->actingAs($owner)->get('/reports')->assertStatus(200);
        $this->actingAs($owner)->get('/reports/reconciliation')->assertStatus(200);
    }
}
