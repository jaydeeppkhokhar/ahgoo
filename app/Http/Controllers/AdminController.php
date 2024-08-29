<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AllUser;
use App\Models\Followers;
use App\Models\Friends;
use App\Models\Blocks;
use App\Models\Countries;
use App\Models\Notifications;
use App\Models\InfCatMap;
use App\Models\Posts;
use App\Models\Promotion;
use App\Models\ProfileViewLog;
use App\Models\KeywordSearchLog;
use App\Models\PostLikes;
use App\Models\Backgrounds;
use App\Models\EventConfirm;
use App\Models\Cms;
use App\Models\Hobbies;
use App\Models\InfluencerCat;
use App\Models\Locations;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }
    public function users(){
        $data['user_data'] = AllUser::orderBy('created_at', 'desc')->get();
        return view('admin.users',$data);
    }
    public function countries(){
        $data['countries'] = Countries::get();
        return view('admin.countries',$data);
    }
    public function cms(){
        $data['cms'] = Cms::get();
        return view('admin.cms',$data);
    }
    public function hobbies(){
        $data['hobbies'] = Hobbies::get();
        return view('admin.hobbies',$data);
    }
    public function influencer_categories(){
        $data['influencer_categories'] = InfluencerCat::get();
        return view('admin.influencer_categories',$data);
    }
    public function locations(){
        $data['locations'] = Locations::get();
        return view('admin.locations',$data);
    }
    public function posts(){
        $posts = Posts::orderBy('created_at', 'desc')->get();
        foreach($posts as $post){
            $user = AllUser::where('_id', $post->user_id)->first();
            $post->post_by = $user->name;
            $post->thumb = !empty($post->thumbnail_img) 
                                ? $post->thumbnail_img 
                                : 'http://34.207.97.193/ahgoo/storage/profile_pics/video_thum.jpg';
        }
        $data['posts'] = $posts;
        return view('admin.posts',$data);
    }
}
