<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $table = "authors";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'first_surname',
        'second_surname'
    ];

    public function books() {
        return $this->belongsToMany(
            Book::class, //Table relationship
            'authors_books', //Table pivot o intersection
            'authors_id', //To
            'books_id' //From
        );
    }
}
