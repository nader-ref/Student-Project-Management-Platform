<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Projectrequest;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProjectrequestController extends Controller
{
    public function request(Request $request)
    {
        $validated = $request->validate([
            'projectid' => 'required|integer|exists:uni_projects,id',
            'count' => 'required|integer|min:1|max:3',
            'nameone' => 'required|string|max:255',
            'oneid' => 'required|integer',
            'nametwo' => 'nullable|string|max:255',
            'twoid' => 'nullable|integer',
            'namethree' => 'nullable|string|max:255',
            'threeid' => 'nullable|integer',
        ], [
            'projectid.exists' => 'The selected project ID does not exist in our records.',
        ]);

        $project = UniProject::findOrFail($validated['projectid']);

        if ($project->taken == 1) {
            return redirect()->back()->with('faild', ' project already taken');
        }

        $studentIds = array_filter([
            $validated['oneid'],
            $validated['twoid'] ?? null,
            $validated['threeid'] ?? null,
        ]);

        if ($this->teamAlreadyHasAcceptedProject($studentIds)) {
            return redirect()->back()->with('faild2', 'Your already have a project');
        }

        Projectrequest::create([
            'projectid' => $validated['projectid'],
            'count' => $validated['count'],
            'nameone' => $validated['nameone'],
            'oneid' => $validated['oneid'],
            'nametwo' => $validated['nametwo'] ?? null,
            'twoid' => $validated['twoid'] ?? null,
            'namethree' => $validated['namethree'] ?? null,
            'threeid' => $validated['threeid'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

    public function accept()
    {
        $studentName = Session::get('name');
        $requests = Projectrequest::query()
            ->when($studentName, function ($query) use ($studentName) {
                $query->where(function ($q) use ($studentName) {
                    $q->where('nameone', $studentName)
                        ->orWhere('nametwo', $studentName)
                        ->orWhere('namethree', $studentName);
                });
            }, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->get();

        return view('student.acceptance', ['requests' => $requests]);
    }

    public function idea(Request $request)
    {
        $validated = $request->validate([
            'projectname' => 'required|string',
            'count' => 'required|integer|min:1|max:3',
            'supname' => 'required|string',
            'nameone' => 'required|string|max:255',
            'oneid' => 'required|integer',
            'nametwo' => 'nullable|string|max:255',
            'twoid' => 'nullable|integer',
            'namethree' => 'nullable|string|max:255',
            'threeid' => 'nullable|integer',
        ]);

        $studentIds = array_filter([
            $validated['oneid'],
            $validated['twoid'] ?? null,
            $validated['threeid'] ?? null,
        ]);

        if ($this->teamAlreadyHasAcceptedProject($studentIds)) {
            return redirect()->back()->with('faild2', 'Your already have a project');
        }

        Idea::create([
            'projectname' => $validated['projectname'],
            'count' => $validated['count'],
            'supname' => $validated['supname'],
            'nameone' => $validated['nameone'],
            'oneid' => $validated['oneid'],
            'nametwo' => $validated['nametwo'] ?? null,
            'twoid' => $validated['twoid'] ?? null,
            'namethree' => $validated['namethree'] ?? null,
            'threeid' => $validated['threeid'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

    public function acceptidea()
    {
        $studentName = Session::get('name');
        $requests = Idea::query()
            ->when($studentName, function ($query) use ($studentName) {
                $query->where(function ($q) use ($studentName) {
                    $q->where('nameone', $studentName)
                        ->orWhere('nametwo', $studentName)
                        ->orWhere('namethree', $studentName);
                });
            }, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->get();

        return view('student.acceptanceidea', ['requests' => $requests]);
    }

    /**
     * @param  array<int, int|string|null>  $studentIds
     */
    private function teamAlreadyHasAcceptedProject(array $studentIds): bool
    {
        foreach (array_filter($studentIds) as $studentId) {
            $hasAcceptedRequest = Projectrequest::query()
                ->where('accepted', 1)
                ->where(function ($query) use ($studentId) {
                    $query->where('oneid', $studentId)
                        ->orWhere('twoid', $studentId)
                        ->orWhere('threeid', $studentId);
                })
                ->exists();

            if ($hasAcceptedRequest) {
                return true;
            }

            $student = User::where('university_number', (string) $studentId)->first();
            $enrolledOnProject = $student
                ? $student->projectMemberships()->exists()
                : false;

            if ($enrolledOnProject) {
                return true;
            }
        }

        return false;
    }
}
