<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendNewPostEmail;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{
    public function search($term){
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }

    public function actuallyUpdate(Post $post, Request $request){
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        return back()->with('success','Post successfully updated');
    }
    public function showEditForm(Post $post){
        return view('edit-post',['post' => $post]);
    }

    public function delete(Post $post){
        // if(auth()->user()->cannot('delete',$post)){
        //     return 'You cannot do that';
        // }
        $post->delete();
        return redirect('/profile/'.auth()->user()->username)->with('success','Post successfully deleted!');
    }

    public function deleteApi(Post $post){
        $post->delete();
        return 'Post successfully deleted!';
    }
    public function viewSinglePost(Post $post){
        // return $pizza->title;
        // $ourHTML = Str::markdown($post->body);
        // $post['body'] = $ourHTML;

        // if($post->user_id === auth()->user()->id){
        //     return 'you are the author';
        // }
        // return 'you are not the author';


        $post['body'] =strip_tags(Str::markdown($post->body),'<p><ul><ol><li><strong><em><h1><h2><h3><h4><h5><h6><br>');
        return view('single-post',['post' => $post]);
    }
    public function storeNewPost(Request $request){
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
    ]);

    $incomingFields['title'] = strip_tags($incomingFields['title']);
    $incomingFields['body'] = strip_tags($incomingFields['body']);
    $incomingFields['user_id'] = auth()->id();

    $newPost = Post::create($incomingFields);

    dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email,'name' => auth()->user()->username, 'title' => $newPost->title]));


    return redirect("/post/{$newPost->id}")->with('success','New Post successfully created');
    }

    public function storeNewPostApi(Request $request){
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
    ]);

    $incomingFields['title'] = strip_tags($incomingFields['title']);
    $incomingFields['body'] = strip_tags($incomingFields['body']);
    $incomingFields['user_id'] = auth()->id();

    $newPost = Post::create($incomingFields);

    dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email,'name' => auth()->user()->username, 'title' => $newPost->title]));


    return $newPost->id;
    }

    public function showCreateForm(){
        return view('create-post');
    }
}