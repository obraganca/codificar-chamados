<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class CatalogoController extends Controller
{
    public function categorias(): JsonResponse
    {
        return response()->json([
            'data' => Categoria::orderBy('nome')->get(['id', 'nome', 'cor']),
        ]);
    }

    public function tags(): JsonResponse
    {
        return response()->json([
            'data' => Tag::orderBy('nome')->get(['id', 'nome']),
        ]);
    }
}