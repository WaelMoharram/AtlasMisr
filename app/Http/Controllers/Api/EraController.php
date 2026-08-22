<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EraResource;
use App\Models\Era;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EraController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $eras = Era::query()
            ->published()
            ->with('source')
            ->orderBy('start_year')
            ->get();

        return EraResource::collection($eras);
    }
}
