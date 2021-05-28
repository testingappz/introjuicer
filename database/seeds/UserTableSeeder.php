<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Model\UserLookingFors;
use App\Model\UserInterests;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        

        User::truncate();
        UserInterests::truncate();
        $faker = \Faker\Factory::create();
        $password = Hash::make('password@123');
        User::create([
        	'first_name' => 'Super',
        	'last_name' => 'Admin',
        	 'user_name'  => 'super_admin',
        	 'email' => 'super@admin.com',
        	 'gender' => 'male',
        	 'dob' => "1992-10-25",
        	 'age' => 28,
        	 'phone_number' => 9800186999,
        	 'password' => $password,
        ]);

        $user_interest = ['Creativity','Photography','Yoga','Gaming','Reading','Songwriting','Piano','Drums','Guitar','Playing an Instrument','Art & Design','Traveling','Volunteering','Blogging','Social Media','Playing Sports','Watching Sports','TV','Family Time','Productivity'];
        $looking_for = ['Friends','Just Browsing','Relationship','Dating','Professional','Just Chatting','Like Minds'];

        for($i = 1; $i <= 5; $i++){
        	$first_name = $faker->firstName;
        	$last_name = $faker->lastName;
        	$user = User::create([
        	'first_name' => $first_name,
        	'last_name' => $last_name,
        	 'user_name'  => $first_name.'_'.$last_name,
        	 'email' => $faker->email,
        	 'gender' => 'male',
        	 'dob' => "1992-10-25",
        	 'age' => 28,
        	 'phone_number' => 9800186999,
        	 'password' => $password,
        ]);

        	// User Interest
        	if($user){        		
        		$random_keys=array_rand($user_interest,3);
        		UserInterests::create(['user_id' => $user->id,
					'interest_type' =>$user_interest[$random_keys[0]]
				]);
				UserInterests::create(['user_id' =>$user->id,
					'interest_type' =>$user_interest[$random_keys[1]]
				]);
				UserInterests::create(['user_id' =>$user->id,
					'interest_type' =>$user_interest[$random_keys[2]]
				]);

				$random_keys=array_rand($looking_for ,3);
        		UserLookingFors::create(['user_id' => $user->id,
					'looking_for' =>$looking_for[$random_keys[0]]
				]);
				
        	}
        }
    
    }
}
