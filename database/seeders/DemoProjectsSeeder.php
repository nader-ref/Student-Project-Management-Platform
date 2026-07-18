<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $s = fn (string $uni) => Supervisor::whereHas(
            'user',
            fn ($q) => $q->where('university_number', $uni),
        )->firstOrFail();

        $sup200000 = $s('200000');
        $sup200001 = $s('200001');
        $sup200002 = $s('200002');
        $sup200003 = $s('200003');
        $sup200004 = $s('200004');
        $sup200005 = $s('200005');
        $sup200006 = $s('200006');

        $dates = fn (int $weeks) => $this->milestoneDates($weeks);

        // Canonical demo projects (preserve names; enrich descriptions slightly within 255 chars).
        $this->seedProject(
            'Smart Campus Portal',
            'Central hub for campus announcements, course files, and deadline reminders used by students and staff.',
            'software',
            $sup200000,
            available: true,
            dates: $dates(4),
        );

        $this->seedProject(
            'IoT Lab Monitor',
            'Live dashboards for lab temperature, humidity, and equipment alerts using campus IoT sensors.',
            'network',
            $sup200000,
            available: true,
            dates: $dates(8),
        );

        $attendance = $this->seedProject(
            'Mobile Attendance System',
            'Mobile and QR class check-in with session records, anti-proxy checks, and supervisor attendance reports.',
            'software',
            $sup200000,
            available: false,
            dates: $dates(2),
            status: 'in_progress',
        );
        $this->assignTeam($attendance, ['300000', '300001']);

        // Parking cluster.
        $this->seedProject(
            'Smart Parking Spot Finder',
            'Detects free campus parking spots from occupancy signals and helps drivers locate open spaces quickly.',
            'ai',
            $sup200001,
            available: true,
            dates: $dates(5),
        );

        $this->seedProject(
            'Campus Parking Reservation',
            'Lets students reserve a campus parking bay for a short window during peak lecture hours.',
            'ai',
            $sup200001,
            available: true,
            dates: $dates(6),
        );

        $parkingDemand = $this->seedProject(
            'Parking Demand Predictor',
            'Predicts busy parking periods from historical occupancy to reduce congestion at campus gates.',
            'ai',
            $sup200001,
            available: false,
            dates: $dates(3),
            status: 'in_progress',
        );
        $this->assignTeam($parkingDemand, ['300004', '300005']);

        // Attendance / education cluster.
        $this->seedProject(
            'QR Class Attendance Tracker',
            'Speeds lecture check-in with QR codes, stores session lists, and exports attendance for supervisors.',
            'software',
            $sup200006,
            available: true,
            dates: $dates(5),
        );

        $this->seedProject(
            'Face Recognition Attendance',
            'Uses classroom cameras to verify student presence and reduce proxy attendance during lectures.',
            'ai',
            $sup200006,
            available: true,
            dates: $dates(7),
        );

        $seminar = $this->seedProject(
            'Seminar Progress Companion',
            'Tracks seminar milestones, reminders, and simple team progress for graduation project cohorts.',
            'software',
            $sup200000,
            available: false,
            dates: $dates(3),
            status: 'in_progress',
        );
        $this->assignTeam($seminar, ['300034', '300035']);

        // Healthcare cluster.
        $this->seedProject(
            'Clinic Appointment Booking',
            'Online outpatient booking with reminder messages that reduce phone queues and missed visits.',
            'software',
            $sup200003,
            available: true,
            dates: $dates(6),
        );

        $hospitalQueue = $this->seedProject(
            'Hospital Queue Advisor',
            'Estimates clinic wait times and helps staff balance appointment slots across outpatient units.',
            'ai',
            $sup200003,
            available: false,
            dates: $dates(4),
            status: 'in_progress',
        );
        $this->assignTeam($hospitalQueue, ['300006', '300007']);

        $this->seedProject(
            'Patient Records Portal',
            'Secure patient profile pages for appointments history, prescriptions summary, and clinic notes.',
            'software',
            $sup200003,
            available: true,
            dates: $dates(9),
        );

        // IoT cluster.
        $this->seedProject(
            'Equipment Health Logger',
            'Logs vibration and power readings from lab machines and alerts technicians before failures.',
            'network',
            $sup200002,
            available: true,
            dates: $dates(5),
        );

        $this->seedProject(
            'Smart Sensor Dashboard',
            'Aggregates campus IoT sensors into one dashboard with threshold alerts for facility staff.',
            'network',
            $sup200002,
            available: true,
            dates: $dates(7),
        );

        // E-commerce / inventory.
        $this->seedProject(
            'Campus Marketplace Store',
            'Student buy-and-sell catalog with cart checkout, order tracking, and basic stock counts.',
            'software',
            $sup200005,
            available: true,
            dates: $dates(6),
        );

        $inventory = $this->seedProject(
            'Lab Inventory Manager',
            'Tracks lab equipment loans, stock levels, and return deadlines for engineering workshops.',
            'software',
            $sup200005,
            available: false,
            dates: $dates(3),
            status: 'in_progress',
        );
        $this->assignTeam($inventory, ['300008', '300009', '300010']);

        // Library / tourism.
        $this->seedProject(
            'Smart Library Shelf Finder',
            'Helps students locate books through shelf maps and live availability status in the library.',
            'network',
            $sup200002,
            available: true,
            dates: $dates(8),
        );

        $this->seedProject(
            'Tourism Route Recommender',
            'Recommends city itineraries from traveler interests, budget limits, and estimated travel time.',
            'ai',
            $sup200001,
            available: true,
            dates: $dates(6),
        );

        $this->seedProject(
            'Visitor Guide Kiosk',
            'Touch-screen campus visitor guide with maps, building directories, and event highlights.',
            'software',
            $sup200005,
            available: true,
            dates: $dates(10),
        );

        // Cybersecurity / networking.
        $this->seedProject(
            'Intrusion Alert Dashboard',
            'Monitors campus network events and raises alerts when suspicious traffic patterns appear.',
            'network',
            $sup200004,
            available: true,
            dates: $dates(5),
        );

        $phishing = $this->seedProject(
            'Phishing Awareness Trainer',
            'Interactive modules that teach students to spot phishing emails using realistic inbox scenarios.',
            'software',
            $sup200004,
            available: false,
            dates: $dates(4),
            status: 'in_progress',
        );
        $this->assignTeam($phishing, ['300011', '300012']);

        $this->seedProject(
            'Secure Campus VPN Portal',
            'Simplifies student VPN onboarding with device checks and connection health diagnostics.',
            'network',
            $sup200004,
            available: true,
            dates: $dates(9),
        );

        // Transportation / vision / agriculture / docs.
        $traffic = $this->seedProject(
            'Traffic Flow Analyzer',
            'Analyzes campus road congestion from camera feeds to suggest quieter entry routes.',
            'ai',
            $sup200006,
            available: false,
            dates: $dates(3),
            status: 'in_progress',
        );
        $this->assignTeam($traffic, ['300013', '300014']);

        $this->seedProject(
            'Bus Arrival Notifier',
            'Shows shuttle ETA and stop alerts for students waiting on the main campus bus routes.',
            'network',
            $sup200002,
            available: true,
            dates: $dates(7),
        );

        $mask = $this->seedProject(
            'Face Mask Compliance Camera',
            'Detects mask compliance at lab entry zones using on-device computer vision models.',
            'ai',
            $sup200006,
            available: false,
            dates: $dates(4),
            status: 'in_progress',
        );
        $this->assignTeam($mask, ['300015', '300016']);

        $this->seedProject(
            'Medical Image Triage Aid',
            'Helps radiologists flag urgent patterns in sample X-ray sets during teaching clinics.',
            'ai',
            $sup200003,
            available: true,
            dates: $dates(8),
        );

        $this->seedProject(
            'Smart Irrigation Controller',
            'Schedules farm irrigation from soil moisture readings to reduce water waste in dry seasons.',
            'ai',
            $sup200001,
            available: true,
            dates: $dates(6),
        );

        $this->seedProject(
            'Crop Disease Detector',
            'Classifies common leaf diseases from phone photos to guide early treatment for growers.',
            'ai',
            $sup200006,
            available: true,
            dates: $dates(7),
        );

        $this->seedProject(
            'Document Similarity Analyzer',
            'Compares graduation proposals by topic keywords to help advisors spot overlapping themes.',
            'ai',
            $sup200001,
            available: true,
            dates: $dates(5),
        );

        $solo = $this->seedProject(
            'Adaptive Study Planner',
            'Builds weekly study plans from course load, exam dates, and personal focus preferences.',
            'ai',
            $sup200000,
            available: false,
            dates: $dates(3),
            status: 'in_progress',
        );
        $this->assignTeam($solo, ['300017']);
    }

    /**
     * @param  array{seminar_1: string, seminar_2: string, seminar_3: string, final: string}  $dates
     * @param  list<string>  $memberUniversityNumbers
     */
    private function seedProject(
        string $name,
        string $description,
        string $department,
        Supervisor $supervisor,
        bool $available,
        array $dates,
        string $status = 'available',
    ): UniProject {
        if (mb_strlen($description) > 255) {
            $description = mb_substr($description, 0, 255);
        }

        $project = UniProject::updateOrCreate(
            ['name' => $name],
            [
                'description' => $description,
                'supervisor_id' => $supervisor->id,
                'department' => $department,
                'taken' => ! $available,
                'student_count' => 0,
                'status' => $available ? 'available' : $status,
                ...$dates,
            ],
        );

        if ($available) {
            $project->members()->delete();
            $project->update(['student_count' => 0, 'taken' => false, 'status' => 'available']);
        }

        return $project->fresh();
    }

    /**
     * @param  list<string>  $universityNumbers
     */
    private function assignTeam(UniProject $project, array $universityNumbers): void
    {
        $project->members()->delete();

        foreach (array_values($universityNumbers) as $index => $universityNumber) {
            $user = User::where('university_number', $universityNumber)->firstOrFail();
            $project->members()->create([
                'user_id' => $user->id,
                'position' => $index + 1,
            ]);
        }

        $project->update([
            'taken' => true,
            'student_count' => count($universityNumbers),
            'status' => 'in_progress',
        ]);
    }

    /**
     * @return array{seminar_1: string, seminar_2: string, seminar_3: string, final: string}
     */
    private function milestoneDates(int $weeksFromNow): array
    {
        $base = now()->addWeeks($weeksFromNow);

        return [
            'seminar_1' => $base->copy()->addWeeks(2)->toDateString(),
            'seminar_2' => $base->copy()->addWeeks(6)->toDateString(),
            'seminar_3' => $base->copy()->addWeeks(10)->toDateString(),
            'final' => $base->copy()->addWeeks(14)->toDateString(),
        ];
    }
}
