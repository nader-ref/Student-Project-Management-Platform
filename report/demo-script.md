# Projects Hub Demo Script

**Project Name:** Graduation Project Management Platform  
**Base URL:** http://uniproject1.test  
**Default password for all demo accounts:** `password`

---

## 1. Setup Before Presentation

Run these commands before the official demo to reset the database and load clean demo data:

```powershell
cd C:\Users\HP\Herd\UniProject1
php artisan migrate:fresh --seed
php artisan db:seed --class=DemoSeeder

Start Mailpit before testing the forgot password flow:

cd $env:USERPROFILE\Desktop
.\mailpit.exe

Mailpit UI:

http://127.0.0.1:8025



2. Demo Accounts
Role	        University Number   	      Email	            Password
Admin	            100000	          admin@example.com	        password
Supervisor	        200000	        supervisor@example.com	    password
Student enrolled	300000	        student@example.com	        password
Discovery student	300002	        student3@example.com	    password
Demo Walkthrough


Step 1 — Welcome Page

Open:

/

Show:

Projects Hub landing page
Single Sign in entry
No public registration
Overview section
How it works section
Features section
Portals section

Say:

The system does not allow public registration. Accounts are created by the administrator, and users sign in using their university number.


Step 2 — Login as Admin

Open:

/Login

Use:

University Number: 100000
Password: password

Expected redirect:

/admin

Say:

The admin is responsible for managing accounts, monitoring projects, requests, submissions, and system activity.
Step 3 — Admin Dashboard

Show:

KPI cards
Users count
Projects count
Requests summary
Ideas summary
Submissions summary
Supervisor workload

Use navigation:

Dashboard
Users
Projects
Requests
Ideas
Submissions
Activity Log

Say:

The dashboard gives the admin an overview of the whole graduation project workflow.


Step 4 — Admin Creates Student / Supervisor

Open:

/admin/students/create

Show that the admin can create student accounts.

Then open:

/admin/supervisors/create

Show that the admin can create supervisor accounts.

You do not have to actually create accounts if time is short.

Say:

All users are provisioned by the administrator. This keeps the platform controlled and prevents unauthorized registration.


Step 5 — Admin Activity Log

Open:

/admin/activity

Show:

Actor
Action
Target
Description
Metadata
Date and time

Say:

The activity log provides an audit trail for important actions such as account creation, request approval, submission upload, and review.


Step 6 — Login as Student

Logout, then login using:

University Number: 300002
Password: password

Expected redirect:

/StudentDashboard

Say:

This is a discovery student who can browse available graduation projects and submit a join request.


Step 7 — Student Submits Project Request

On the student dashboard:

Browse available projects
Choose an available project such as Smart Campus Portal
Submit a join request
Add team members if needed

Seeded shortcut:

Student 300002 already has a pending request on Smart Campus Portal.

Say:

Students can browse available projects and submit a request to join a project with their team members.


Step 8 — Supervisor Receives Notification

Logout, then login as supervisor:

University Number: 200000
Password: password

Open:

/notifications

Show notification:

request_submitted

Say:

The supervisor receives an internal notification when a student submits a project request.


Step 9 — Supervisor Accepts Request

Open supervisor dashboard:

/supervisorDashboard

Go to Requests.

Accept the pending request:

REQ-0001
Smart Campus Portal

Say:

The supervisor can accept or reject student requests. When accepted, the project becomes assigned and the students become enrolled.


Step 10 — Student Sees Assigned Project

Logout, then login again as student:

University Number: 300002
Password: password

Open:

/StudentDashboard

Show:

Assigned project
Supervisor
Team members
Milestone dates
Project workspace

Open:

/StudentDashboard/acceptance

Show accepted request.

Say:

After approval, the student dashboard changes from project discovery mode to enrolled project workspace.


Step 11 — Student Uploads Submission

On the student dashboard:

Open Submissions tab
Upload a file for Seminar 1
Confirm status becomes:
submitted

Seeded shortcut:

Student 300000 already has a submission called "Seminar One Proposal" on Mobile Attendance System.

Say:

Students can upload milestone submissions, and the supervisor can review them later.


Step 12 — Supervisor Reviews Submission

Logout, then login as supervisor:

University Number: 200000
Password: password

Open the supervisor submissions section.

Review a submission using one of these statuses:

Approved

or:

Needs revision

If choosing Needs revision, write feedback.

Say:

The supervisor can approve submissions or request revisions with feedback.


Step 13 — Student Sees Feedback / Progress / Timeline

Logout, then login as student again.

Open:

/StudentDashboard

Show:

Overview
Progress tab
Timeline tab
Submissions tab
Review feedback
Milestone status

Say:

The student can track project progress based on approved submissions, not only dates.


Step 14 — Notifications and Activity Log Proof

Show notifications:

/notifications

Expected examples:

request_submitted
request_accepted
submission_reviewed

Then login as admin:

University Number: 100000
Password: password

Open:

/admin/activity

Expected examples:

project_request.submitted
project_request.accepted
submission.uploaded
submission.reviewed

Say:

Notifications help users follow workflow updates, while the admin activity log provides accountability and traceability.


Step 15 — Forgot Password by University Number

Logout and open:

/Login

Click:

Forgot password?

Open:

/ForgetPassword

Enter:

University Number: 300000

Expected message:

If this university number is linked to an email address, a password reset link will be sent.

Open Mailpit:

http://127.0.0.1:8025

Open the reset email and click the reset link.

The reset page should ask for:

University Number
New Password
Confirm Password

Say:

Password recovery is also based on the university number. The email is used internally only as a delivery channel and is never shown to the user.
Fast Path If Time Is Short

Use this shorter order if the presentation time is limited:

1. Welcome page
2. Login as Admin
3. Admin dashboard
4. Activity Log
5. Login as Student 300002
6. Show pending project request
7. Login as Supervisor 200000
8. Show notification
9. Accept request
10. Login as Student 300002
11. Show assigned project
12. Upload submission
13. Supervisor reviews submission
14. Student sees progress, timeline, and feedback
15. Forgot Password using university number and Mailpit
Important Notes

Before every official demo, reset the database:

php artisan migrate:fresh --seed
php artisan db:seed --class=DemoSeeder

Make sure Mailpit is running before showing the forgot password flow:

.\mailpit.exe

Do not accept the seeded request before the real demo unless you reset the database again.

Test Coverage Summary

The following areas are covered by tests:

Public auth flow
Unified login
Forgot password by university number
Reset password by university number
Admin student provisioning
Admin supervisor provisioning
Admin activity log
Internal notifications
Project request workflow
Submission upload and review
Timeline and progress logic

Latest result:

Tests: 265 passed
Demo Goal

The goal of this demo is to show that the platform supports the complete graduation project workflow:

Admin creates accounts
Student submits project request
Supervisor receives notification
Supervisor accepts request
Student becomes enrolled
Student uploads submission
Supervisor reviews submission
Student tracks progress and feedback
Admin monitors all important actions through activity log
Password recovery works using university number