# Projects Hub Demo Script

**Project Name:** Graduation Project Management Platform  
**Base URL:** http://uniproject1.test  
**Default password for all demo accounts:** `password`

---

## 1. Setup Before Presentation

### Pre-demo checklist

Before the official demo, complete all of the following:

- [ ] Run `php artisan migrate:fresh --seed`
- [ ] Start Mailpit
- [ ] Verify login for admin `100000`, supervisor `200000`, and student `300002` (password: `password`)
- [ ] Do **not** accept REQ-0001 before the real demo unless you reset the database again

### Reset database and load demo data

```powershell
cd C:\Users\HP\Herd\UniProject1
php artisan migrate:fresh --seed
```

`DatabaseSeeder` already calls `DemoSeeder`, so `php artisan db:seed --class=DemoSeeder` is optional and redundant.

### Start Mailpit (for forgot-password step)

```powershell
cd $env:USERPROFILE\Desktop
.\mailpit.exe
```

**Mailpit UI:** http://127.0.0.1:8025

---

## 2. Demo Accounts

| Role | University Number | Email | Password |
|------|-------------------|-------|----------|
| Admin | 100000 | admin@example.com | password |
| Supervisor | 200000 | supervisor@example.com | password |
| Student enrolled | 300000 | student@example.com | password |
| Discovery student | 300002 | student3@example.com | password |

---

## Demo Walkthrough

### Step 1 — Welcome Page

**Open:** `/`

**Show:**

- Projects Hub landing page
- Single Sign in entry
- No public registration
- Overview, How it works, Features, and Portals sections

**Say:** The system does not allow public registration. Accounts are created by the administrator, and users sign in using their university number.

---

### Step 2 — Login as Admin

**Open:** `/Login`

**Use:**

- University Number: `100000`
- Password: `password`

**Expected redirect:** `/admin`

**Say:** The admin is responsible for managing accounts, monitoring projects, requests, submissions, and system activity.

---

### Step 3 — Admin Dashboard

**Show:** KPI cards — users count, projects count, requests summary, ideas summary, submissions summary, supervisor workload.

**Use navigation:** Dashboard · Users · Projects · Requests · Ideas · Submissions · Activity Log

**Say:** The dashboard gives the admin an overview of the whole graduation project workflow.

---

### Step 4 — Admin Creates Student / Supervisor

**Open:** `/admin/students/create` — show that the admin can create student accounts.

**Then open:** `/admin/supervisors/create` — show that the admin can create supervisor accounts.

You do not have to actually create accounts if time is short.

**Say:** All users are provisioned by the administrator. This keeps the platform controlled and prevents unauthorized registration.

---

### Step 5 — Admin Activity Log

**Open:** `/admin/activity`

**Show:** Actor · Action · Target · Description · Metadata · Date and time

**Note:** On a fresh seed, the log is empty until workflow actions occur during the demo. Use this step to show the audit-trail structure.

**Say:** The activity log provides an audit trail for important actions such as account creation, request approval, submission upload, and review.

---

### Step 6 — Login as Student

Logout, then login using:

- University Number: `300002`
- Password: `password`

**Expected redirect:** `/StudentDashboard`

**Say:** This is a discovery student who can browse available graduation projects and submit a join request.

---

### Step 7 — Student Submits Project Request

On the student dashboard:

- Browse available projects
- Choose an available project such as Smart Campus Portal
- Submit a join request
- Add team members if needed

**Seeded shortcut:** Student 300002 already has a pending request on Smart Campus Portal.

**Say:** Students can browse available projects and submit a request to join a project with their team members.

---

### Step 8 — Supervisor Sees Pending Request

Logout, then login as supervisor:

- University Number: `200000`
- Password: `password`

**Open:** `/supervisorDashboard` → **Requests** tab

**Show:** Pending request **REQ-0001** on **Smart Campus Portal**

**Note:** A fresh seed does **not** create a `request_submitted` notification — the pending request is seeded directly in the database, not submitted through the live form. Notifications appear after accept, upload, and review steps (Step 14).

**Say:** The supervisor sees pending join requests on the dashboard and can accept or reject them from the Requests tab.

---

### Step 9 — Supervisor Accepts Request

**Open:** `/supervisorDashboard` → Requests

**Accept the pending request:**

- REQ-0001
- Smart Campus Portal

**Say:** The supervisor can accept or reject student requests. When accepted, the project becomes assigned and the students become enrolled.

---

### Step 10 — Student Sees Assigned Project

Logout, then login again as student:

- University Number: `300002`
- Password: `password`

**Open:** `/StudentDashboard`

**Show:** Assigned project · Supervisor · Team members · Milestone dates · Project workspace

**Then open:** `/StudentDashboard/acceptance` — show accepted request.

**Say:** After approval, the student dashboard changes from project discovery mode to enrolled project workspace.

---

### Step 11 — Student Uploads Submission

On the student dashboard:

1. Open **Submissions** tab
2. Upload a file for **Seminar 1**
3. Confirm status becomes **submitted**

**Upload file types:** Use a small **PDF, DOC, DOCX, PPT, PPTX, ZIP, or RAR** file. Do **not** use `.txt` — it is not an allowed upload type.

**Seeded shortcut (review only):** Student 300000 already has a submission called "Seminar One Proposal" on Mobile Attendance System if you need a pre-existing item for supervisor review in Step 12.

**Say:** Students can upload milestone submissions, and the supervisor can review them later.

---

### Step 12 — Supervisor Reviews Submission

Logout, then login as supervisor:

- University Number: `200000`
- Password: `password`

Open the supervisor **Submissions** section.

Review a submission using one of these statuses:

- **Approved**, or
- **Needs revision** (requires feedback text)

**Say:** The supervisor can approve submissions or request revisions with feedback.

---

### Step 13 — Student Sees Feedback / Progress / Timeline

Logout, then login as student again.

**Open:** `/StudentDashboard`

**Show:** Overview · Progress tab · Timeline tab · Submissions tab · Review feedback · Milestone status

**Say:** The student can track project progress based on approved submissions, not only dates.

---

### Step 14 — Notifications and Activity Log Proof

After the live workflow steps above (accept, upload, review), show proof that events were recorded.

**Show notifications:** `/notifications`

**Expected examples (from actions performed during the demo):**

- `request_accepted`
- `submission_uploaded`
- `submission_reviewed`

Then login as admin:

- University Number: `100000`
- Password: `password`

**Open:** `/admin/activity`

**Expected examples (from actions performed during the demo):**

- `project_request.accepted`
- `submission.uploaded`
- `submission.reviewed`

**Say:** Notifications help users follow workflow updates, while the admin activity log provides accountability and traceability.

---

### Step 15 — Forgot Password by University Number

Logout and open `/Login` → click **Forgot password?**

**Open:** `/ForgetPassword`

**Enter:** University Number: `300000`

**Expected message:** If this university number is linked to an email address, a password reset link will be sent.

**Open Mailpit:** http://127.0.0.1:8025

Open the reset email and click the reset link.

The reset page should ask for: University Number · New Password · Confirm Password

**Say:** Password recovery is also based on the university number. The email is used internally only as a delivery channel and is never shown to the user.

---

## Fast Path If Time Is Short

1. Welcome page
2. Login as Admin
3. Admin dashboard
4. Activity Log (structure only — empty on fresh seed)
5. Login as Student 300002
6. Show pending project request
7. Login as Supervisor 200000
8. Show pending request on Requests tab (REQ-0001)
9. Accept request
10. Login as Student 300002
11. Show assigned project
12. Upload submission (PDF or other allowed type)
13. Supervisor reviews submission
14. Student sees progress, timeline, and feedback; then notifications + Activity Log proof
15. Forgot Password using university number and Mailpit

---

## Important Notes

- Before every official demo: `php artisan migrate:fresh --seed` (DemoSeeder is included automatically).
- Make sure Mailpit is running before the forgot-password step.
- Do not accept REQ-0001 before the real demo unless you reset the database again.
- Use allowed file types for uploads (PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR) — not `.txt`.

---

## Test Coverage Summary

The following areas are covered by tests:

- Public auth flow
- Unified login
- Forgot password by university number
- Reset password by university number
- Admin student provisioning
- Admin supervisor provisioning
- Admin activity log
- Internal notifications
- Project request workflow
- Submission upload and review
- Timeline and progress logic

**Latest result:** Tests: **272 passed**

---

## Demo Goal

The goal of this demo is to show that the platform supports the complete graduation project workflow:

1. Admin creates accounts
2. Student submits project request
3. Supervisor sees and accepts the request
4. Student becomes enrolled
5. Student uploads submission
6. Supervisor reviews submission
7. Student tracks progress and feedback
8. Admin monitors important actions through the activity log
9. Password recovery works using university number
