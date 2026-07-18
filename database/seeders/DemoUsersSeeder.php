<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Laratrust\Models\Role;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureRoles();

        // Canonical demo accounts (preserve exactly).
        $this->seedUser('Admin User', '100000', 'admin@example.com', 'admin');
        $this->seedUser('Secondary Admin', '100001', 'admin2@example.com', 'admin');

        $this->seedUser('Supervisor User', '200000', 'supervisor@example.com', 'supervisor');
        $this->seedSupervisorProfile('200000', 'Supervisor User', 'supervisor@example.com');

        $extraSupervisors = [
            ['200001', 'Dr. Lina Haddad', 'lina.haddad@example.com', 'AI and machine learning'],
            ['200002', 'Dr. Omar Nasser', 'omar.nasser@example.com', 'IoT and computer networks'],
            ['200003', 'Dr. Sara Khalil', 'sara.khalil@example.com', 'Healthcare information systems'],
            ['200004', 'Dr. Rami Farhat', 'rami.farhat@example.com', 'Cybersecurity and networking'],
            ['200005', 'Dr. Maya Saleh', 'maya.saleh@example.com', 'E-commerce and software engineering'],
            ['200006', 'Dr. Hadi Qassem', 'hadi.qassem@example.com', 'Computer vision and smart campus'],
        ];

        foreach ($extraSupervisors as [$uni, $name, $email]) {
            $this->seedUser($name, $uni, $email, 'supervisor');
            $this->seedSupervisorProfile($uni, $name, $email);
        }

        // Canonical students.
        $this->seedUser('Student User', '300000', 'student@example.com', 'student');
        $this->seedUser('Team Member Student', '300001', 'student2@example.com', 'student');
        $this->seedUser('Discovery Student', '300002', 'student3@example.com', 'student');
        $this->seedUser('AI Demo Student', '300003', 'student.ai@example.com', 'student');

        $extraStudents = [
            ['300004', 'Nour Alami', 'nour.alami@example.com'],
            ['300005', 'Yazan Hariri', 'yazan.hariri@example.com'],
            ['300006', 'Layla Mansour', 'layla.mansour@example.com'],
            ['300007', 'Karim Darwish', 'karim.darwish@example.com'],
            ['300008', 'Hiba Saad', 'hiba.saad@example.com'],
            ['300009', 'Tarek Jaber', 'tarek.jaber@example.com'],
            ['300010', 'Rana Abboud', 'rana.abboud@example.com'],
            ['300011', 'Fadi Melhem', 'fadi.melhem@example.com'],
            ['300012', 'Salma Khatib', 'salma.khatib@example.com'],
            ['300013', 'Bassel Awad', 'bassel.awad@example.com'],
            ['300014', 'Dina Rahme', 'dina.rahme@example.com'],
            ['300015', 'Ziad Hamdan', 'ziad.hamdan@example.com'],
            ['300016', 'Maya Traboulsi', 'maya.traboulsi@example.com'],
            ['300017', 'Omar Shami', 'omar.shami@example.com'],
            ['300018', 'Lina Barakat', 'lina.barakat@example.com'],
            ['300019', 'Hassan Fadel', 'hassan.fadel@example.com'],
            ['300020', 'Jana Sleiman', 'jana.sleiman@example.com'],
            ['300021', 'Rami Khoury', 'rami.khoury@example.com'],
            ['300022', 'Nadine Aoun', 'nadine.aoun@example.com'],
            ['300023', 'Walid Saab', 'walid.saab@example.com'],
            ['300024', 'Carmen Nader', 'carmen.nader@example.com'],
            ['300025', 'Elie Haddad', 'elie.haddad@example.com'],
            ['300026', 'Rita Ghanem', 'rita.ghanem@example.com'],
            ['300027', 'Samir Abi', 'samir.abi@example.com'],
            ['300028', 'Paula Semaan', 'paula.semaan@example.com'],
            ['300029', 'George Atiyeh', 'george.atiyeh@example.com'],
            ['300030', 'Lara Chami', 'lara.chami@example.com'],
            ['300031', 'Nabil Karam', 'nabil.karam@example.com'],
            ['300032', 'Sara Bou', 'sara.bou@example.com'],
            ['300033', 'Adam Rifai', 'adam.rifai@example.com'],
            ['300034', 'Yara Melki', 'yara.melki@example.com'],
            ['300035', 'Mark Daoud', 'mark.daoud@example.com'],
        ];

        foreach ($extraStudents as [$uni, $name, $email]) {
            $this->seedUser($name, $uni, $email, 'student');
        }
    }

    private function ensureRoles(): void
    {
        foreach (['admin', 'supervisor', 'student'] as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['display_name' => ucfirst($roleName), 'description' => $roleName.' role'],
            );
        }
    }

    private function seedUser(string $name, string $universityNumber, string $email, string $role): User
    {
        $user = User::updateOrCreate(
            ['university_number' => $universityNumber],
            [
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'is_active' => true,
            ],
        );

        DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('user_type', User::class)
            ->delete();

        $user->addRole($role);

        return $user->fresh();
    }

    private function seedSupervisorProfile(string $universityNumber, string $name, string $email): Supervisor
    {
        $user = User::where('university_number', $universityNumber)->firstOrFail();

        return Supervisor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $name,
                'email' => $email,
            ],
        );
    }
}
