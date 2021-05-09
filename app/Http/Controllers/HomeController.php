<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Memo;
use App\Tag;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //ログインしているユーザー情報を取得
        $user = \Auth::user();
        //メモ一覧を表示
        $memos = Memo::where('user_id',$user['id'])->where('status',1)->orderBy('updated_at','desc')->get();
        return view('create',compact('user','memos'));
    }

    public function create(){
        //ログインしているユーザー情報を取得
        $user = \Auth::user();
        $memos = Memo::where('user_id',$user['id'])->where('status',1)->orderBy('updated_at','desc')->get();
        
        return view('create',compact('user','memos'));
    }

    public function store(Request $request){
        $data = $request->all();

        $exist_tag = Tag::where('name',$data['tag'])->where('user_id',$data['user_id'])->first();
        if(empty($exist_tag['id'])){
            //タグをインサート
            $tag_id = Tag::insertGetId([
                'name' => $data['tag'],
                'user_id' => $data['user_id']
            ]);
        }else{
            $tag_id = $exist_tag['id'];
        }
        
        //POSTされたデータをmemosテーブルに挿入
        $memo_id = Memo::insertGetId([
            'content' => $data['content'],
            'user_id' => $data['user_id'],
            'tag_id' => $tag_id,
            'status' => 1,
        ]);

        //タグテーブルにインサート
        // if($default_tag = Tag::where('name',$data['tag'])->where('user_id',$data['user_id'])->first()){
        //     $tag_id = $default_tag['id'];
        // }else{
        //     $tag_id = Tag::insertGetId([
        //         'name' => $data['tag'],
        //         'user_id' => $data['user_id'],
        //     ]);
        // }

        //リダイレクト
        return redirect()->route('home');
    }

    public function edit($id){
        //ログインしているユーザー情報を取得
        $user = \Auth::user();
        $memo = Memo::where('status',1)->where('id',$id)->where('user_id',$user['id'])->first();
        $memos = Memo::where('user_id',$user['id'])->where('status',1)->orderBy('updated_at','desc')->get();

        $tags = Tag::where('user_id',$user['id'])->get();
        return view('edit',compact('memo','user','memos','tags'));
    }

    public function update(Request $request,$id){
        $input = $request->all();
        Memo::where('id',$id)->update(['content' => $input['content'],'tag_id' => $input['tag_id']]);
        //リダイレクト
        return redirect()->route('home');
    }

    public function delete(Request $request,$id){
        $input = $request->all();

        //論理削除
        Memo::where('id',$id)->update([
            'status' => 2
        ]);

        //フラッシュメッセージ
        return redirect()->route('home')->with('success','削除しました');
    }
}
