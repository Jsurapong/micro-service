<?php

namespace App\Http\Controllers;

use App\Book;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BooksController
{
    /**
     * GET /books
     * @return array
     */
    public function index()
    {
        //return Book::all();
        return ['data' => Book::all()->toArray()];
    }

    public function show($id)
    {
        return ['data' => Book::findOrFail($id)->toArray()];
    }


    public function store(Request $request)
    {
        $book = Book::create($request->all());

        return response()->json(['data' => $book->toArray()], 201, [
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
        return ['data' => $book->toArray()];
    }

    public function destroy($id)
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
       
        $book->delete();

        return response(null, 204);
    }
}
