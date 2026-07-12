<?php

namespace App\Http\Controllers\Consultations;

use App\Http\Controllers\Controller;
use App\Repositories\Consultations\ConsultantRepository;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function __construct(private ConsultantRepository $repository) {}

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'niche', 'min_experience', 'max_price', 'sort']);
        $consultants = $this->repository->searchConsultants($filters);
        $niches = $this->repository->uniqueNiches();

        return view('marketplace.consultants.index', compact('consultants', 'filters', 'niches'));
    }

    public function show(string $slug)
    {
        $consultant = $this->repository->findBySlug($slug);

        abort_if(! $consultant, 404);

        $canBook = auth()->check()
            && auth()->user()->vendor?->consultantProfile?->id !== $consultant->id;

        return view('marketplace.consultants.show', compact('consultant', 'canBook'));
    }
}
