<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InsertPostsTest extends TestCase
{
    /**
     * Test that the API returns a list of users with posts.
     *
     * @return void
     */
    public function test_posts()
    {
        $response = $this->get('/get-posts');

        $response->assertStatus(200);

        $response->assertExactJson([
            'success' => true
        ]);
    }
}
