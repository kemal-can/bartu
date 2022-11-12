<?php

namespace Tests\Feature\Controller\Api\Resource;

use Tests\TestCase;
use App\Models\Activity;

class CommentControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_the_comments_endpoints()
    {
        $this->getJson('/api/comments')->assertUnauthorized();
        $this->getJson('/api/comments/FAKE_ID')->assertUnauthorized();
        $this->postJson('/api/comments')->assertUnauthorized();
        $this->putJson('/api/comments/FAKE_ID')->assertUnauthorized();
        $this->deleteJson('/api/comments/FAKE_ID')->assertUnauthorized();
    }

    public function test_comment_can_be_added_to_resource()
    {
        $user  = $this->signIn();
        $event = Activity::factory()->create();

        $this->postJson('/api/activities/' . $event->getKey() . '/comments', [
            'body' => 'Comment goes here',
        ])->assertCreated()
            ->assertJson([
                'body'       => 'Comment goes here',
                'created_by' => $user->getKey(),
            ])
            ->assertJsonStructure(['creator']);
    }

    public function test_comments_can_be_retrieved()
    {
        $this->signIn();
        $activity = Activity::factory()->create();
        $activity->comments()->create(['body' => 'Comment goes here']);

        $this->getJson("/api/activities/{$activity->id}/comments")->assertJsonCount(1);
    }

    public function test_comments_can_be_retrieved_for_record_that_the_user_is_authorized_to_see()
    {
        $user     = $this->signIn();
        $activity = Activity::factory()->for($user)->create();
        $activity->comments()->create(['body' => 'Comment goes here']);

        $this->asRegularUser()->signIn();

        $this->getJson("/api/activities/{$activity->id}/comments")->assertForbidden();
    }

    public function test_comment_cannot_be_added_to_resources_the_user_is_not_authorized_to_see()
    {
        $this->asRegularUser()->signIn();
        $activity = Activity::factory()->create();

        $this->postJson("/api/activities/{$activity->id}/comments", [
            'body' => 'Comment goes here',
        ])->assertForbidden();
    }

    public function test_when_present_comment_requires_resource()
    {
        $this->signIn();
        $activity = Activity::factory()->create();

        $this->postJson("/api/activities/{$activity->id}/comments", [
            'body'            => 'Comment goes here',
            'via_resource'    => '',
            'via_resource_id' => '',
        ])->assertJsonValidationErrors(['via_resource', 'via_resource_id']);

        $comment = $activity->comments()->create(['body' => 'Comment goes here']);

        $this->putJson('/api/comments/' . $comment->id, [
            'body'            => 'Comment goes here',
            'via_resource'    => '',
            'via_resource_id' => '',
        ])->assertJsonValidationErrors(['via_resource', 'via_resource_id']);
    }

    public function test_comment_requires_body()
    {
        $this->signIn();
        $activity = Activity::factory()->create();

        $this->postJson("/api/activities/{$activity->id}/comments", [
            'body' => '',
        ])->assertJsonValidationErrors(['body']);

        $id = $this->postJson("/api/activities/{$activity->id}/comments", [
            'body' => 'Comment goes here',
        ])->getData()->id;

        $this->putJson('/api/comments/' . $id, [
            'body' => '',
        ])->assertJsonValidationErrors(['body']);
    }

    public function test_comment_can_be_retrieved()
    {
        $this->signIn();
        $activity = Activity::factory()->create();
        $comment  = $activity->comments()->create(['body' => 'Comment goes here']);

        $this->getJson('/api/comments/' . $comment->id)->assertJson([
            'body' => 'Comment goes here',
        ]);
    }

    public function test_comment_can_be_retrieved_only_by_creator()
    {
        $this->signIn();
        $activity = Activity::factory()->create();
        $comment  = $activity->comments()->create(['body' => 'Comment goes here']);

        $this->asRegularUser()->signIn();

        $this->getJson('/api/comments/' . $comment->id)->assertForbidden();
    }

    public function test_comment_can_be_updated()
    {
        $this->signIn();
        $activity = Activity::factory()->create();

        $comment = $activity->comments()->create(['body' => 'Comment goes here']);

        $this->putJson('/api/comments/' . $comment->id, [
            'body' => 'Changed Body',
        ])->assertJson([
            'body' => 'Changed Body',
        ]);
    }

    public function test_comment_can_be_updated_only_by_creator()
    {
        $this->signIn();
        $activity = Activity::factory()->create();
        $comment  = $activity->comments()->create(['body' => 'Comment goes here']);

        $this->asRegularUser()->signIn();

        $this->putJson('/api/comments/' . $comment->id, [
            'body' => 'Changed Body',
        ])->assertForbidden();
    }

    public function test_comment_can_be_deleted()
    {
        $this->signIn();
        $activity = Activity::factory()->create();
        $comment  = $activity->comments()->create(['body' => 'Comment goes here']);

        $this->deleteJson('/api/comments/' . $comment->id)->assertNoContent();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_comment_can_be_deleted_only_by_creator()
    {
        $this->signIn();
        $activity = Activity::factory()->create();
        $comment  = $activity->comments()->create(['body' => 'Comment goes here']);

        $this->asRegularUser()->signIn();

        $this->deleteJson('/api/comments/' . $comment->id)->assertForbidden();
    }
}
