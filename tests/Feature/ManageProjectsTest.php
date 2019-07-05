<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use Facades\Tests\Setup\ProjectFactory;

class ManageProjectsTest extends TestCase
{   
    use WithFaker , RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_project() 
    {   
        $this->signIn();

        $this->get('/projects/create')->assertStatus(200);

        $attributes = factory(Project::class)->raw(['owner_id' => auth()->id()]);

        $response = $this->followingRedirects()->post('/projects', $attributes);

        $response->assertSee($attributes['title'])->assertSee($attributes['description'])->assertSee($attributes['notes']);

    }

    /** @test */
    public function a_user_can_see_all_project_they_have_been_invited()
    {
        $this->withoutExceptionHandling();
        
        $user = $this->signIn();

        $project = ProjectFactory::create();

        $project->invite($user);

        $this->get('/projects')->assertSee($project->title);
    }

    /** @test */
    public function a_user_can_update_a_project() {

        $project = ProjectFactory::ownedBy($this->signIn())->create();
        

        $this->patch($project->path() , $attributes = ['title'=> 'changed', 'description' => 'changed' ,'notes'=>'changed']);

        $this->get($project->path() . '/edit')->assertOk();

        $this->assertDatabaseHas('projects', $attributes);

    }

    /** @test */
    public function unauthorize_cannot_delete_a_project() {

        $project = ProjectFactory::create();
        
        $this->delete($project->path())->assertRedirect('/login');

        $user = $this->signIn();

        $this->delete($project->path())->assertStatus(403);

        $project->invite($user);

        $this->actingAs($user)->delete($project->path())->assertStatus(403);
    }

    /** @test */
    public function a_user_can_delete_a_project() {

        $project = ProjectFactory::ownedBy($this->signIn())->create();
        
        $this->delete($project->path())->assertRedirect('/projects');

        $this->assertDatabaseMissing('projects' , $project->only('id'));
    }

    /** @test */
    public function a_project_require_a_title() 
    {   
        $this->signIn();

        $attribute = factory('App\Project')->raw(['title' => '']);

        $this->post('/projects',$attribute)->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_project_require_a_description() 
    {
        $this->signIn();

        $attribute = factory('App\Project')->raw(['description' => '']);

        $this->post('/projects',$attribute)->assertSessionHasErrors('description');
    }

    /** @test */
    public function a_user_can_view_their_project() 
    {
        $project = ProjectFactory::ownedBy($this->signIn())->create();

        $this->get($project->path())->assertSee($project->title);
    }

    /** @test */
    public function an_authenticated_user_can_not_view_the_project_of_others() 
    {
        $this->signIn();

        $project = factory('App\Project')->create();

        $this->get($project->path())->assertStatus(403);
        
    }

    /** @test */
    public function an_authenticated_user_can_not_update_the_project_of_others() 
    {
        $this->signIn();

        $project = factory('App\Project')->create();

        $this->get($project->path() , [])->assertStatus(403);
        
    }

    /** @test */
    public function guests_cannot_manage_projects() 
    {
        $project = factory('App\Project')->create();

        $this->get('/projects')->assertRedirect('login');
        $this->get($project->path() . '/edit/')->assertRedirect('login');        
        $this->get('/projects/create')->assertRedirect('login');
        $this->get($project->path())->assertRedirect('login');
        $this->post('/projects',$project->toArray())->assertRedirect('login');

    }

    /** @test */
    public function a_user_can_update_general_note()
    {
        $project = ProjectFactory::ownedBy($this->signIn())->create();

        $this->patch($project->path() , $attributes = ['notes'=>'changed']);

        $this->assertDatabaseHas('projects', $attributes);
    }

    /** @test */
    public function task_can_be_include_as_part_a_new_project_creation()
    {
        $this->signIn();

        $attribute = factory('App\Project')->raw();

        $attribute['tasks'] = [
            ['body' => 'task one'],
            ['body' => 'task two']
        ];

        $this->post('/projects',$attribute);
    
        $this->assertCount(2,Project::first()->tasks);
    }

}
