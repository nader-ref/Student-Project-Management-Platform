<?php

namespace Database\Seeders;

use App\Models\ProjectSubmission;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoSubmissionsSeeder extends Seeder
{
    private const DEMO_SUBMISSION_PATH = 'submissions/demo/mobile-attendance-seminar-one.txt';

    public function run(): void
    {
        $user = fn (string $uni) => User::where('university_number', $uni)->firstOrFail();
        $project = fn (string $name) => UniProject::where('name', $name)->firstOrFail();

        Storage::disk('public')->put(
            self::DEMO_SUBMISSION_PATH,
            implode("\n", [
                'Mobile Attendance System',
                'Seminar One Proposal (Demo Seed File)',
                '',
                'Problem Statement',
                'Manual attendance tracking is slow and error-prone for large classes.',
                '',
                'Objectives',
                '- Build a mobile attendance workflow for students and supervisors.',
                '- Provide milestone tracking for seminar deliverables.',
                '',
                'Timeline',
                '- Seminar 1: proposal and scope confirmation.',
                '- Seminar 2: prototype demo.',
                '- Seminar 3: evaluation and testing results.',
                '- Final: complete system presentation.',
            ]),
        );

        $supervisorReviewer = $user('200000');

        $rows = [
            // Canonical demo submission (pending review).
            [
                'project' => 'Mobile Attendance System',
                'student' => '300000',
                'milestone' => 'seminar_1',
                'title' => 'Seminar One Proposal',
                'status' => 'submitted',
                'notes' => 'Demo submission for browser smoke testing.',
                'path' => self::DEMO_SUBMISSION_PATH,
                'filename' => 'seminar-one-proposal.txt',
            ],
            [
                'project' => 'Parking Demand Predictor',
                'student' => '300004',
                'milestone' => 'seminar_1',
                'title' => 'Parking Demand Scope Document',
                'status' => 'approved',
                'notes' => 'Approved scope for occupancy forecasting.',
                'feedback' => 'Clear problem framing and feasible milestones.',
                'reviewer' => '200001',
            ],
            [
                'project' => 'Parking Demand Predictor',
                'student' => '300005',
                'milestone' => 'seminar_2',
                'title' => 'Parking Prototype Demo Notes',
                'status' => 'submitted',
                'notes' => 'Prototype screens and sample predictions.',
            ],
            [
                'project' => 'Hospital Queue Advisor',
                'student' => '300006',
                'milestone' => 'seminar_1',
                'title' => 'Queue Advisor Proposal',
                'status' => 'needs_revision',
                'notes' => 'Initial clinic workflow draft.',
                'feedback' => 'Add clearer metrics for wait-time estimation.',
                'reviewer' => '200003',
            ],
            [
                'project' => 'Hospital Queue Advisor',
                'student' => '300007',
                'milestone' => 'seminar_2',
                'title' => 'Queue Simulation Results',
                'status' => 'submitted',
                'notes' => 'Simulation tables for peak hours.',
            ],
            [
                'project' => 'Lab Inventory Manager',
                'student' => '300008',
                'milestone' => 'seminar_1',
                'title' => 'Inventory Requirements Spec',
                'status' => 'approved',
                'notes' => 'Equipment loan use cases.',
                'feedback' => 'Good coverage of loan and return flows.',
                'reviewer' => '200005',
            ],
            [
                'project' => 'Lab Inventory Manager',
                'student' => '300009',
                'milestone' => 'seminar_2',
                'title' => 'Inventory UI Wireframes',
                'status' => 'needs_revision',
                'notes' => 'Wireframes for stock screens.',
                'feedback' => 'Include barcode scan path in the wireframes.',
                'reviewer' => '200005',
            ],
            [
                'project' => 'Lab Inventory Manager',
                'student' => '300010',
                'milestone' => 'seminar_3',
                'title' => 'Inventory Test Plan',
                'status' => 'submitted',
                'notes' => 'Test cases for loan conflicts.',
            ],
            [
                'project' => 'Phishing Awareness Trainer',
                'student' => '300011',
                'milestone' => 'seminar_1',
                'title' => 'Phishing Module Outline',
                'status' => 'approved',
                'notes' => 'Scenario list for inbox simulations.',
                'feedback' => 'Strong scenario variety.',
                'reviewer' => '200004',
            ],
            [
                'project' => 'Phishing Awareness Trainer',
                'student' => '300012',
                'milestone' => 'seminar_2',
                'title' => 'Trainer Prototype Build',
                'status' => 'submitted',
                'notes' => 'Interactive quiz screenshots.',
            ],
            [
                'project' => 'Traffic Flow Analyzer',
                'student' => '300013',
                'milestone' => 'seminar_1',
                'title' => 'Traffic Dataset Description',
                'status' => 'approved',
                'notes' => 'Camera feed sampling plan.',
                'feedback' => 'Privacy notes are sufficient.',
                'reviewer' => '200006',
            ],
            [
                'project' => 'Traffic Flow Analyzer',
                'student' => '300014',
                'milestone' => 'seminar_2',
                'title' => 'Congestion Heatmap Demo',
                'status' => 'needs_revision',
                'notes' => 'Heatmap export samples.',
                'feedback' => 'Explain how peak intervals are chosen.',
                'reviewer' => '200006',
            ],
            [
                'project' => 'Face Mask Compliance Camera',
                'student' => '300015',
                'milestone' => 'seminar_1',
                'title' => 'Vision Model Baseline',
                'status' => 'submitted',
                'notes' => 'Accuracy numbers on sample clips.',
            ],
            [
                'project' => 'Face Mask Compliance Camera',
                'student' => '300016',
                'milestone' => 'seminar_2',
                'title' => 'Edge Deployment Notes',
                'status' => 'approved',
                'notes' => 'On-device inference constraints.',
                'feedback' => 'Clear hardware assumptions.',
                'reviewer' => '200006',
            ],
            [
                'project' => 'Adaptive Study Planner',
                'student' => '300017',
                'milestone' => 'seminar_1',
                'title' => 'Study Planner Proposal',
                'status' => 'submitted',
                'notes' => 'Personalization approach draft.',
            ],
            [
                'project' => 'Seminar Progress Companion',
                'student' => '300034',
                'milestone' => 'seminar_1',
                'title' => 'Companion Feature Spec',
                'status' => 'approved',
                'notes' => 'Milestone reminder flows.',
                'feedback' => 'Nice alignment with seminar dates.',
                'reviewer' => '200000',
            ],
            [
                'project' => 'Seminar Progress Companion',
                'student' => '300035',
                'milestone' => 'seminar_2',
                'title' => 'Companion Prototype Review',
                'status' => 'submitted',
                'notes' => 'Clickable prototype link notes.',
            ],
            [
                'project' => 'Mobile Attendance System',
                'student' => '300001',
                'milestone' => 'seminar_2',
                'title' => 'Attendance Prototype Demo',
                'status' => 'needs_revision',
                'notes' => 'Teammate prototype package.',
                'feedback' => 'Add offline sync behavior details.',
                'reviewer' => '200000',
            ],
        ];

        foreach ($rows as $row) {
            $proj = $project($row['project']);
            $student = $user($row['student']);
            $path = $row['path'] ?? 'submissions/demo/'.str($row['title'])->slug('_').'.txt';
            $filename = $row['filename'] ?? str($row['title'])->slug('-').'.txt';

            if ($path !== self::DEMO_SUBMISSION_PATH) {
                Storage::disk('public')->put(
                    $path,
                    $row['title']."\n\n".$row['notes']."\n\nSeeded demo submission file.",
                );
            }

            $payload = [
                'file_path' => $path,
                'original_filename' => $filename,
                'notes' => $row['notes'],
                'status' => $row['status'],
                'supervisor_feedback' => $row['feedback'] ?? null,
                'reviewed_at' => in_array($row['status'], ['approved', 'needs_revision'], true) ? now() : null,
                'reviewed_by_user_id' => isset($row['reviewer'])
                    ? $user($row['reviewer'])->id
                    : (in_array($row['status'], ['approved', 'needs_revision'], true) ? $supervisorReviewer->id : null),
            ];

            ProjectSubmission::updateOrCreate(
                [
                    'project_id' => $proj->id,
                    'submitted_by_user_id' => $student->id,
                    'milestone' => $row['milestone'],
                    'title' => $row['title'],
                ],
                $payload,
            );
        }
    }
}
