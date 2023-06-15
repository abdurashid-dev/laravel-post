<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        $data = [];
        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'image' => asset('storage/'.$post->image),
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
            ];
        }
        return response()->json($data);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'image' => asset($post->image),
            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
        ];
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'sometimes',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $imageName = time() . '.' . $request->image->extension();
        $request->image->storeAs('public/posts', $imageName);
        $imagePath = "posts/" . $imageName;
        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'image' => $imagePath,
        ]);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'sometimes',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $oldImagePath = $post->image;
            if ($oldImagePath) {
                $oldImageName = explode('/', $oldImagePath)[1];
                Storage::delete('public/posts/' . $oldImageName);
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/posts', $imageName);
            $imagePath = "posts/" . $imageName;
            $post->image = $imagePath;
        }
        $post->title = $data['title'];
        if (isset($data['content']))
            $post->content = $data['content'];
        $post->save();
        return response()->json($post);
    }
}
