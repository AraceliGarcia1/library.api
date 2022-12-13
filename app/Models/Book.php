<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $table = "books";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'isbn',
        'title',
        'description',
        'published_date',
        'category_id',
        'editorial_id'
    ];

    public function bookDownload()
    {
        return $this->hasOne(BookDownload::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function authors() {
        return $this->belongsToMany(
            Author::class, //Table relationship
            'authors_books', //Table pivot o intersection
            'books_id', //From
            'authors_id' //To
        );
    }

    public function editorial() {
        return $this->belongsTo(Editorial::class);
    }
}