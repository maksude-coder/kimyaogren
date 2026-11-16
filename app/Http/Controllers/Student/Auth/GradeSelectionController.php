<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Program;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Registration step 2 (design § 08): "Kaçıncı sınıftasın?".
 */
class GradeSelectionController extends Controller
{
    /** Form value of the "Mezun · YKS hazırlık" choice. */
    public const GRADUATE = 'mezun';

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('student.auth.grade', [
            'grades' => $this->grades(),
            'selected' => $user->is_graduate ? self::GRADUATE : $user->grade_id,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'grade' => ['required', Rule::in([self::GRADUATE, ...$this->grades()->modelKeys()])],
        ], [
            'grade.required' => 'Bir sınıf seç.',
            'grade.in' => 'Listeden bir sınıf seç.',
        ]);

        $graduate = $validated['grade'] === self::GRADUATE;

        $request->user()->forceFill([
            'grade_id' => $graduate ? null : (int) $validated['grade'],
            'is_graduate' => $graduate,
        ])->save();

        return redirect()->route('today');
    }

    /**
     * Active grades of the first published program — the same program
     * `/mufredat` opens (Sprint 2.3 plan § 2, rule 4).
     *
     * @return Collection<int, Grade>
     */
    private function grades(): Collection
    {
        $program = Program::published()->ordered()->first();

        return $program?->grades()->active()->ordered()->get() ?? new Collection;
    }
}
