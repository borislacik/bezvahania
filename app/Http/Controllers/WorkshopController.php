<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Support\Seo;
use Illuminate\Contracts\View\View;

class WorkshopController extends Controller
{
    public function show(Course $course): View
    {
        abort_unless($course->is_active, 404);

        return view('pages.workshops.show', [
            'seo' => Seo::forCourse($course),
            'course' => $course,
            'others' => Course::query()->active()->whereKeyNot($course->getKey())->get(),
        ]);
    }
}
