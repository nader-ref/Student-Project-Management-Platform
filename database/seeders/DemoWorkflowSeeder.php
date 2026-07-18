<?php

namespace Database\Seeders;

use App\Models\contact;
use App\Models\idea;
use App\Models\IdeaMember;
use App\Models\projectrequest;
use App\Models\ProjectRequestMember;
use App\Models\Supervisor;
use App\Models\supcontact;
use App\Models\UniProject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $user = fn (string $uni) => User::where('university_number', $uni)->firstOrFail();
        $supervisor = fn (string $uni) => Supervisor::whereHas(
            'user',
            fn ($q) => $q->where('university_number', $uni),
        )->firstOrFail();

        $sup200000 = $supervisor('200000');
        $sup200001 = $supervisor('200001');
        $sup200002 = $supervisor('200002');
        $sup200003 = $supervisor('200003');
        $sup200005 = $supervisor('200005');
        $sup200006 = $supervisor('200006');

        $portal = UniProject::where('name', 'Smart Campus Portal')->firstOrFail();
        $parkingFinder = UniProject::where('name', 'Smart Parking Spot Finder')->firstOrFail();
        $clinic = UniProject::where('name', 'Clinic Appointment Booking')->firstOrFail();
        $attendance = UniProject::where('name', 'Mobile Attendance System')->firstOrFail();

        // Remove any legacy pending idea for demo student 300002.
        idea::query()
            ->where('requested_by_user_id', $user('300002')->id)
            ->where('accepted', false)
            ->where('rejected', false)
            ->get()
            ->each(function (idea $idea) {
                $idea->members()->delete();
                $idea->delete();
            });

        // 3 pending requests (300002 keeps request-only demo workflow).
        $this->seedPendingRequest($portal, $user('300002'), ['300002']);
        $this->seedPendingRequest($parkingFinder, $user('300018'), ['300018']);
        $this->seedPendingRequest($clinic, $user('300019'), ['300019']);

        // 4 pending ideas (not on 300002).
        $this->seedIdea($sup200000, $user('300020'), ['300020'], 'AI-Powered Study Planner', $this->proposal(
            'Students lose track of weekly study goals across multiple courses.',
            ['Build adaptive weekly plans', 'Sync with course deadlines', 'Send focus reminders'],
            'Campus courses and personal study blocks.',
            ['Course calendar import', 'Plan generation', 'Reminder notifications'],
        ), false, false);

        $this->seedIdea($sup200001, $user('300021'), ['300021'], 'Campus Lost-and-Found Matcher', $this->proposal(
            'Lost items on campus are hard to reunite with owners quickly.',
            ['Photo-based item reports', 'Match notifications', 'Pickup scheduling'],
            'Main campus buildings and student services desk.',
            ['Item report form', 'Image similarity match', 'Claim workflow'],
        ), false, false);

        $this->seedIdea($sup200006, $user('300022'), ['300022'], 'Lab Seat Booking Board', $this->proposal(
            'Students queue for limited computer-lab seats during peak hours.',
            ['Live seat availability', 'Short reservations', 'No-show release'],
            'Faculty computer labs only.',
            ['Seat map', 'Reservation timer', 'Staff override'],
        ), false, false);

        $this->seedIdea($sup200005, $user('300023'), ['300023'], 'Peer Tutoring Matchboard', $this->proposal(
            'Students struggle to find tutors for specific course topics before exams.',
            ['Match tutors by course', 'Schedule short sessions', 'Rate session quality'],
            'Undergraduate courses offered on campus.',
            ['Tutor profiles', 'Booking calendar', 'Feedback form'],
        ), false, false);

        // 2 rejected ideas.
        $this->seedIdea($sup200005, $user('300024'), ['300024'], 'Generic Campus Chat Bot', $this->proposal(
            'Students ask repetitive FAQ questions on many disconnected channels.',
            ['Answer FAQs', 'Route complex tickets'],
            'Public FAQ content only.',
            ['Chat widget', 'FAQ knowledge base'],
        ), false, true, 'Scope overlaps an existing advising chatbot pilot; please refine a novel focus.');

        $this->seedIdea($sup200002, $user('300025'), ['300025'], 'Unlimited Drone Delivery Network', $this->proposal(
            'Campus deliveries are slow between distant faculties.',
            ['Autonomous drone routes', 'Package lockers'],
            'Entire city airspace.',
            ['Fleet control', 'Air corridor planning'],
        ), false, true, 'Out of scope for a single graduation project; narrow to one building pilot.');

        // 8 accepted ideas with rich proposal_description (similarity corpus).
        $this->seedIdea($sup200001, $user('300026'), ['300026'], 'Campus Lot Reservation Assistant', $this->proposal(
            'Students waste time circling campus lots when parking occupancy is unclear.',
            ['Show live lot occupancy', 'Allow short bay holds', 'Notify when a hold expires'],
            'Selected campus parking lots with sensors.',
            ['Occupancy map', 'Reservation window', 'Expiry alerts'],
        ), true, false);

        $this->seedIdea($sup200006, $user('300027'), ['300027'], 'Lecture Presence Verifier', $this->proposal(
            'Manual roll call wastes lecture time and is easy to fake with proxy attendance.',
            ['QR or biometric check-in', 'Session attendance lists', 'Export supervisor reports'],
            'University lecture halls and seminars.',
            ['Check-in flow', 'Anti-proxy rules', 'CSV export'],
        ), true, false);

        $this->seedIdea($sup200003, $user('300028'), ['300028'], 'Outpatient Booking Companion', $this->proposal(
            'Patients wait on phone lines to book clinic appointments and often miss slots.',
            ['Online booking', 'Reminder messages', 'Staff schedule view'],
            'Outpatient clinics affiliated with the teaching hospital.',
            ['Patient login', 'Book and cancel', 'Reminder service'],
        ), true, false);

        $this->seedIdea($sup200002, $user('300029'), ['300029'], 'Workshop Machine Health Monitor', $this->proposal(
            'Engineering workshops lack early warnings when machines overheat or vibrate abnormally.',
            ['Ingest sensor telemetry', 'Threshold alerts', 'Maintenance history log'],
            'Selected engineering workshop machines.',
            ['Telemetry ingest', 'Alert rules', 'Technician dashboard'],
        ), true, false);

        $this->seedIdea($sup200005, $user('300030'), ['300030'], 'Student Thrift Marketplace', $this->proposal(
            'Students struggle to buy and sell used textbooks and electronics safely on campus.',
            ['List products with stock', 'Cart checkout', 'Order status tracking'],
            'Campus community marketplace for students only.',
            ['Catalog browse', 'Checkout', 'Order tracking'],
        ), true, false);

        $this->seedIdea($sup200006, $user('300031'), ['300031'], 'Radiology Teaching Image Helper', $this->proposal(
            'Medical students need guided practice spotting urgent patterns in anonymized X-ray sets.',
            ['Label practice cases', 'Highlight candidate regions', 'Track student accuracy'],
            'Teaching datasets only; not a clinical diagnostic device.',
            ['Case viewer', 'Annotation tools', 'Score report'],
        ), true, false);

        $this->seedIdea($sup200001, $user('300032'), ['300032'], 'Field Crop Leaf Scanner', $this->proposal(
            'Small growers detect crop disease late because expert visits are infrequent.',
            ['Classify leaf photos', 'Suggest early actions', 'Store field history'],
            'Common leaf diseases for selected crops.',
            ['Photo capture', 'Disease classification', 'Recommendation card'],
        ), true, false);

        $this->seedIdea($sup200000, $user('300033'), ['300033'], 'Campus Announcement Digest', $this->proposal(
            'Students miss important faculty announcements scattered across email and portals.',
            ['Aggregate announcements', 'Personal digest feed', 'Deadline highlights'],
            'Faculty and department announcement sources.',
            ['Source connectors', 'Digest timeline', 'Reminder toggles'],
        ), true, false);

        contact::updateOrCreate(
            [
                'student_user_id' => $user('300000')->id,
                'supervisor_id' => $sup200000->id,
                'subject' => 'Seminar one requirements',
            ],
            [
                'Message' => 'Could you confirm the deliverables for seminar one?',
                'Replay' => 'Please submit a 5-page proposal covering the problem statement, objectives, methodology, and timeline for Seminar 1.',
            ],
        );

        supcontact::updateOrCreate(
            [
                'supervisor_id' => $sup200000->id,
                'project_id' => $attendance->id,
                'subject' => 'Seminar 1 deliverables reminder',
            ],
            [
                'Message' => 'Seminar 1 is approaching. Upload your proposal PDF and a short presentation outline before the scheduled date.',
            ],
        );
    }

    /**
     * @param  list<string>  $memberUniversityNumbers
     */
    private function seedPendingRequest(UniProject $project, User $requester, array $memberUniversityNumbers): projectrequest
    {
        $request = projectrequest::updateOrCreate(
            [
                'project_id' => $project->id,
                'requested_by_user_id' => $requester->id,
            ],
            [
                'count' => count($memberUniversityNumbers),
                'accepted' => false,
                'rejected' => false,
                'reason' => null,
            ],
        );

        $request->members()->delete();
        foreach (array_values($memberUniversityNumbers) as $index => $universityNumber) {
            $member = User::where('university_number', $universityNumber)->firstOrFail();
            ProjectRequestMember::create([
                'project_request_id' => $request->id,
                'user_id' => $member->id,
                'position' => $index + 1,
            ]);
        }

        return $request;
    }

    /**
     * @param  list<string>  $memberUniversityNumbers
     */
    private function seedIdea(
        Supervisor $supervisor,
        User $requester,
        array $memberUniversityNumbers,
        string $projectName,
        string $proposal,
        bool $accepted,
        bool $rejected,
        ?string $reason = null,
    ): idea {
        $idea = idea::updateOrCreate(
            [
                'supervisor_id' => $supervisor->id,
                'requested_by_user_id' => $requester->id,
                'projectname' => $projectName,
            ],
            [
                'proposal_description' => $proposal,
                'count' => count($memberUniversityNumbers),
                'accepted' => $accepted,
                'rejected' => $rejected,
                'reason' => $reason,
            ],
        );

        $idea->members()->delete();
        foreach (array_values($memberUniversityNumbers) as $index => $universityNumber) {
            $member = User::where('university_number', $universityNumber)->firstOrFail();
            IdeaMember::create([
                'idea_id' => $idea->id,
                'user_id' => $member->id,
                'position' => $index + 1,
            ]);
        }

        return $idea;
    }

    /**
     * @param  list<string>  $objectives
     * @param  list<string>  $requirements
     */
    private function proposal(string $problem, array $objectives, string $scope, array $requirements): string
    {
        $objectiveLines = array_map(fn (string $line) => '• '.$line, $objectives);
        $requirementLines = array_map(fn (string $line) => '• '.$line, $requirements);

        return implode("\n", [
            'Problem Statement',
            $problem,
            '',
            'Objectives',
            ...$objectiveLines,
            '',
            'Scope',
            $scope,
            '',
            'Initial Functional Requirements',
            ...$requirementLines,
        ]);
    }
}
