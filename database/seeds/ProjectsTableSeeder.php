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
        $projects = [
            [
                'name' => 'EWS',
                'user_id' => 1,
                'date' => Carbon::now(),
            ],
            [
                'name' => 'Bong Pheak',
                'user_id' => 1,
                'date' => Carbon::now(),
            ]
        ];
        Project::insert($projects);
    }
}
