<?php

namespace App\Http\Controllers;

use App\Book;

class BooksController
{
    /**
     * GET /books
     * @return array
     */
    public function index()
    {
        return Book::all();
    }
}
