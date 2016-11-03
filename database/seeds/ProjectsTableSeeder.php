<?php

use App\Project;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $project = new Project();
        $project->name = 'EWS';
        $project->user_id = 1;
        $project->date = Carbon::now('Asia/Phnom_Penh')->toDateString();
        $project->save();
    }
}
