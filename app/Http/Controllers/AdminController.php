<?php

namespace App\Http\Controllers;

use App\Achievement;
use App\User;
use App\Setting;
use App\Comment;
use App\AchievementType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Excel;
use App\Exports\AchievementTypesExport;
use App\Imports\AchievementTypesImport;
use App\Exports\LeadersExport;
use App\Exports\SortedAchievementExport;
use Illuminate\Filesystem\Filesystem;



class AdminController extends Controller
{
    public function addType(Request $request){
        $request->validate([
            'category' => 'required',
            'type' => 'required',
            'stage' => 'required',
            'result' => 'required',
            'score' => 'required',
        ]);
      $newType = new AchievementType();
      $newType -> category = $request -> category;
      $newType -> type = $request -> type;
      $newType -> stage = $request -> stage;
      $newType -> result = $request -> result;
      $newType -> score = $request -> score;
      $newType -> save();
      return redirect('/');
    }

    public function exportLeaderboard() 
    {
        return Excel::download(new LeadersExport, 'leaders.xlsx');
    }

    public function uploadAchievementTypesFile(Request $request){
        if ($request -> has('file')) {
            $mimeTypes = [
                'application/vnd.ms-excel',
                'application/vnd.ms-office',
                'application/vnd-xls',
                'application/vnd.ms-excel',
                'application/msexcel',
                'application/x-msexcel',
                'application/x-ms-excel',
                'application/x-excel',
                'application/excel',
                'application/x-dos_ms_excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/xls',
                'application/x-xls',
            ];
            if (in_array(request()->file->getClientMimeType(), $mimeTypes)) {
                AchievementType::truncate();
                Excel::import(new AchievementTypesImport,request()->file);
            }
        }
        return redirect(url()->previous());
    }

    public function downloadAchievementTypesFile(){
        return Excel::download(new AchievementTypesExport, 'achievementTypes.xlsx');
    }
    
    public function showAddType(){
        $achievementTypes = AchievementType::all();
        $categories = AchievementType::select('category')->distinct()->get();
        $types = AchievementType::select('type')->distinct()->get();
        $stages = AchievementType::select('stage')->distinct()->get();
        $results = AchievementType::select('result')->distinct()->get();
        return view('admin/addAchievementType', compact('stages', 'results','categories', 'types','achievementTypes'));
    }

    public function deleteAchievementType($id){
        $type = AchievementType::findOrFail($id);
        $type -> delete();
        return redirect(url()->previous());
    }

    public function getEditTypeView($id){
        $thisType = AchievementType::findOrFail($id);
        $achievementTypes = AchievementType::all();
        $categories = AchievementType::select('category')->distinct()->get();
        $types = AchievementType::select('type')->distinct()->get();
        $stages = AchievementType::select('stage')->distinct()->get();
        $results = AchievementType::select('result')->distinct()->get();
        return view('admin/editAchievementType', compact('stages', 'results','categories', 'types','achievementTypes', 'thisType'));
    }

    public function editAchievementType($id, Request $request){
        $request->validate([
            'category' => 'required',
            'type' => 'required',
            'stage' => 'required',
            'result' => 'required',
            'score' => 'required',
        ]);
        $type = AchievementType::findOrFail($id);
        $type -> category = $request -> category;
        $type -> type = $request -> type;
        $type -> stage = $request -> stage;
        $type -> result = $request -> result;
        $type -> score = $request -> score;
        $type -> save();
        return redirect('/all_achievement_types');
    }

    public function index(){
        $allTypes = AchievementType::all() ->take(7);
        $sentAchievements = Achievement::all() -> where('status', 'sent')->take(5);
        $students = User::all()-> where('role', 'student') ->take(10);
        return view('admin/admin', compact('sentAchievements', 'students', 'allTypes'));
    }

    public function getAllSentAchievements(){
        $sentAchievements = Achievement::all() -> where('status', 'sent');
        return view('admin/allSentAchievements', compact('sentAchievements'));
    }

    public function getAllUsers(){
        if (Auth::user() -> role == 'superadmin') {
            $users = User::all()->where('role', '!=','superadmin');
        } else {
            $users = User::all()->where('role', 'student');
        }
        return view('admin/allUsers', compact('users'));
    }

    public function getAllAchievementTypes(){
        $types = AchievementType::all();
        return view('admin/allAchievementTypes', compact('types'));
    }

    public function showBannedUsers(){
        $bannedUsers = User::all() -> where('role', 'banned');
        return view('admin/bannedUsers', compact('bannedUsers'));
    }

    public function aboutUser($id){
        $user = User::findOrFail($id);
        $students = User::all() -> where('role', 'student');
        $place = $user -> place();
        $confirmedAchievements = $user -> achievements -> where('status', 'confirmed');
        return view('admin/profile', compact('user', 'place', 'confirmedAchievements'));
    }

    public function ban($id){
        $user = User::findOrFail($id);
        if (((Auth::user()->role == 'superadmin')||($user -> role != 'admin')) && ($user -> role != 'superadmin')) {
            $user -> role = 'banned';
            $user -> save();
        }
        return redirect(url() -> previous());
    }

    public function promote($id){
        $user = User::findOrFail($id);
        if ($user -> role != 'superadmin'){
            $user -> role = 'admin';
            $user -> save();
        }
        return redirect(url() -> previous());
    }

    public function degrade($id){
        $user = User::findOrFail($id);
        if (($user -> role != 'superadmin') && ($user -> role != 'student')){
            $user -> role = 'student';
            $user -> save();
        }
        return redirect(url() -> previous());
    }

    public function unblock($id){
        $user = User::findOrFail($id);
        if (($user -> role != 'admin') && ($user -> role != 'superadmin'))  {
            $user -> role = 'student';
            $user -> save();
        }
        return redirect(url() -> previous());
    }

    public function reject($id, Request $request){
        $achievement = Achievement::findOrFail($id);
        $achievement -> status = 'rejected';
        $achievement -> save();
        if (isset($request->comment)){
            $comment = new Comment;
            $comment->achievement_id = $id;
            $comment->text = $request->comment;
            $comment->author = 'admin';
            $comment->save();
        }
        return redirect(url()->previous());
    }

    public function confirm($id){
        $achievement = Achievement::findOrFail($id);
        $achievement -> status = 'confirmed';
        $achievement -> save();
        return redirect('/');
    }

    public function settingsView(){
        $settings = Setting::all();
        $categories = AchievementType::select('category')->distinct()->get();
        return view('admin.settings', compact('settings', 'categories'));
    }

    public function settingsUpdate(Request $request){
        $settings = Setting::all();
        foreach ($settings as $setting){
            if ($setting->type!='GlobalVariable'){
                if (($setting->type=='on/off') and ($request->input($setting->id)!='on') and ($request->input($setting->id)!='off')){
                    if ($setting->value == 'on'){
                        $setting->value = 'off';
                    } else {
                        $setting->value = 'on';
                    }
                } else {
                    $setting->value = $request->input($setting->id);
                }
                $setting->save();
            }
        }
        return redirect(url()->previous());
    }
    public function getAchievementsTable(){
        $allAchievements = Achievement::all()->where('status', 'confirmed');
        foreach ($allAchievements as $achievement){
            $achievement->student = $achievement->user->name;
            $achievement->form = $achievement->user->form;
            $achievements[] = $achievement;
        }
        return view('admin.table', compact('achievements'));
    }

    public function downloadAchievementTable($name){
        $pathToFile = storage_path('sorted_achievements') . '/' . $name; // TODO узнать дерикторию, куда будут сохраняться файлы
        return response()->download($pathToFile);
    }

    public function importAchievementTable(Request $request){
        $file = new Filesystem;
        $file->cleanDirectory(storage_path('sorted_achievements'));
        $achievements = $request->table;        
        Excel::store(new SortedAchievementExport($achievements),'sortedAchievements.xlsx','mydisk');
        return 'sortedAchievements.xlsx';
    }
}
