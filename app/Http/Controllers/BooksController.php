<?php

namespace App\Http\Controllers;

use App\Book;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;

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

    public function show($id)
    {
        try {
            return Book::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' =>[
                    'message' => 'Book not found'
                ]
                ], 404);
        }
    }


    public function store(Request $request)
    {
        $book = Book::create($request->all());

        return response()->json(['created' => true], 201, [
            'Location' => route('books.show', ['id' => $book->id])
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Book not found'
                ]
                ], 404);
        }
       
        $book->fill($request->all());
        $book->save();
        return $book;
    }
}
