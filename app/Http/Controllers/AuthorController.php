<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Exception;

class AuthorController extends Controller{

    public function index(){
        $authors = Author::orderBy('first_surname', 'asc')->get();
        return $this->getResponse200($authors);
    }

    public function store(Request $request){
        $author = new Author();
        $author->name = $request->name;
        $author->first_surname = $request->first_surname;
        $author->second_surname = $request->second_surname;
        if ($author->save()) {
            if(isset($request->books)){
                foreach ($request->books as $item) {
                    $author->books()->attach($item);
                }
            }
            return $this->getResponse201('author', 'created', $author);
        } else {
            return $this->getResponse400();
        }
    }

    public function update(Request $request, $id){
        $author = Author::find($id);
        try {
            if ($author) {
                $author->name = $request->name;
                $author->first_surname = $request->first_surname;
                $author->second_surname = $request->second_surname;
                $author->update();
                return $this->getResponse201('author', 'updated', $author);
            } else {
                return $this->getResponse404();
            }
        } catch (Exception $e) {
            return $this->getResponse500([]);
        }
    }

    public function show($id)
    {
        $author = Author::with('books')->where('id', $id)->first();
        if ($author) {
            return $this->getResponse200($author);
        }else{
            return $this->getResponse404();
        }
    }

    public function destroy($id)
    {
        $author = Author::find($id);
        if ($author != null) {
            $author->books()->detach();
            $author->delete();
            return $this->getResponseDelete200('author');
        }else {
            return $this->getResponse404();
        }
    }
}
