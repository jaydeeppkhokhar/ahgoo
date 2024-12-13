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
use App\Models\Events;
use App\Models\EventInvites;
use App\Models\EventMedia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Exports\AllUsersExport;
use App\Exports\EventsExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function index()
    {
        $data['total_users'] = AllUser::count();
        $data['free_events'] = Events::where('event_type','1')->count();
        $data['paid_events'] = Events::where('event_type','2')->count();
        $data['promotions'] = Promotion::count();
        $data['posts'] = Posts::count();
        $data['countries'] = Countries::count();
        return view('admin.dashboard',$data);
    }
    public function users(){
        $data['user_data'] = AllUser::orderBy('created_at', 'desc')->get();
        return view('admin.users',$data);
    }
    public function exportAllUsers()
    {
        return Excel::download(new AllUsersExport, 'all_users.xlsx');
    }
    public function countries(){
        $data['countries'] = Countries::get();
        return view('admin.countries',$data);
    }
    public function cms(){
        $data['cms'] = Cms::get();
        return view('admin.cms',$data);
    }
    public function edit_cms(Request $request){
        $data['cms'] = Cms::where('_id',$request->id);
        return view('admin.edit_cms',$data);
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
            $post->post_by = $user->name ?? 'NA';
            $post->thumb = !empty($post->thumbnail_img) 
                                ? $post->thumbnail_img 
                                : 'http://34.207.97.193/ahgoo/storage/profile_pics/video_thum.jpg';
        }
        $data['posts'] = $posts;
        return view('admin.posts',$data);
    }
    public function events(){
        $event_all_details = Events::where('event_name', '<>', '')->limit(100)->orderBy('created_at', 'desc')->get();
        foreach($event_all_details as $event_details){
            
            $user = AllUser::where('_id',$event_details->user_id)->first();
            $event_details->event_created_by = $user->name;
            if(!isset($user->profile_pic) OR empty($user->profile_pic)){
                $event_details->event_created_by_profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }else{
                $event_details->event_created_by_profile_pic = $user->profile_pic;
            }
            
            $inv_cnt = EventInvites::where('event_id',$event_details->_id)->get();
            if(!$inv_cnt->isEmpty()){
                $event_details->event_invites_count = count($inv_cnt);
                $event_details->event_invites1 = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                $event_details->event_invites2 = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }else{
                $event_details->event_invites_count = 0;
                $event_details->event_invites1 = '';
                $event_details->event_invites2 = '';
            }
            // $event_details->event_media = EventMedia::select('_id','media_path')->where('event_id',$request->event_id)->get();
        }
        
        $data['events'] = $event_all_details;
        // echo '<pre>';print_r($data['events']);exit;
        return view('admin.events',$data);
    }
    public function exportAllEvents()
    {
        return Excel::download(new EventsExport, 'events.xlsx');
    }
}
