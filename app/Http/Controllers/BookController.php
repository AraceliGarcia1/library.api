<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookDownload;
use App\Models\BookReview;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::orderBy('title', 'asc')->with('category', 'authors','editorial' )->get();
        return $this->getResponse200($books);
    }

    public function store(Request $request)
    {
        try {
            $isbn = trim($request->isbn);
            $existIsbn = Book::where('isbn', $isbn)->exists();
            if (!$existIsbn) {
                $book = new Book();
                $book->isbn = $isbn;
                $book->title = $request->title;
                $book->description = $request->description;
                $book->published_date = date('y-m-d h:i:s');
                $book->category_id = $request->category['id'];
                $book->editorial_id = $request->editorial['id'];
                $book->save();
                $bookDownload = new BookDownload();
                $bookDownload->book_id = $book->id;
                $bookDownload->save();
                foreach ($request->authors as $item) {
                    $book->authors()->attach($item);
                }
                return $this->getResponse201('book', 'created', $book);
            } else {
                return $this->getResponse500(['The isbn field must be unique']);
            }
        } catch (Exception $e) {
            return $this->getResponse500([]);
        }
    }

    public function update(Request $request, $id)
    {
        $book = Book::find($id);
        try {
            if ($book) {
                $isbn = trim($request->isbn);
                $isbnBook = Book::where('isbn', $isbn)->first();
                if (!$isbnBook || $isbnBook->id == $book->id) {
                    $book->isbn = $isbn;
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->published_date = date('y-m-d h:i:s');
                    $book->category_id = $request->category['id'];
                    $book->editorial_id = $request->editorial['id'];
                    $book->update();
                    foreach ($book->authors as $item) {
                        $book->authors()->detach($item);
                    }
                    foreach ($request->authors as $item) {
                        $book->authors()->attach($item);
                    }
                    $book = Book::with('category', 'editorial', 'authors')->where('id', $id)->get();
                    return $this->getResponse201('book', 'updated', $book);
                } else {
                    return $this->getResponse400();
                }
            } else {
                return $this->getResponse404();
            }
        } catch (Exception $e) {
            return $this->getResponse500([]);
        }
    }

    public function show($id)
    {
        $book = Book::with('authors')->where('id', $id)->with('category')->with('editorial')->first();
        if ($book) {
            return $this->getResponse200($book);
        } else {
            return $this->getResponse404();
        }
    }

    public function addBookReview(Request $request, $book_id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $user = auth()->user();
                if ($user) {
                    $bookRew = new BookReview();
                    $bookRew->comment = $request->comment;
                    $bookRew->book_id = $book_id;
                    $bookRew->user_id = $user->id;
                    $bookRew->save();
                    DB::commit();
                    return $this->getResponse201('book review', 'created', $bookRew);
                }else{
                    return $this->getResponse401();
                }
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }


    public function updateBookReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $bookReview = BookReview::where('id', $id)->get()->first();
                $user = auth()->user();
                if($user){
                    if ($bookReview->user_id == $user->id) {
                        $bookReview->comment = $request->comment;
                        $bookReview->edited = true;
                        $bookReview->update();
                        DB::commit();
                        return $this->getResponse201('book review', 'updated', $bookReview);
                    } else {
                        return $this->getResponse403();
                    }
                }else{
                    return $this->getResponse401();
                }
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function destroy($id)
    {
        $book = Book::find($id);
        if ($book != null) {
            $book->authors()->detach();
            $id = $book->id;
            $bookDownload = BookDownload::where('book_id', $id)->first();
            $bookDownload->delete();
            $book->delete();
            return $this->getResponseDelete200('book');
        } else {
            return $this->getResponse404();
        }
    }
}
