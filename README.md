# GTU-ITR Academic Management System

GTU-ITR is a Laravel academic ERP for managing institutions, academics, staff, students, attendance, exams, fees, leave, notices, reports, permissions, chatbot support, and system health.

## Stack

- PHP 8.2+
- Laravel 12
- MySQL
- Vite, Tailwind CSS, Alpine.js
- Pest for tests

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

Default local URL:

```text
http://localhost:8000
```

Fresh installs create a Super Admin user:

```text
Username: admin
Password: ChangeMe123!
```

The account is forced to change its password on first login.

## Main Modules

- Institution: universities, colleges, departments
- Academic: categories, academic years, programmes, semesters, subjects, curriculum, elective groups
- People: staff, students, users, roles, direct permissions
- Attendance: staff assignments, timetable slots, lectures, marking, summaries, defaulters
- Exams: exams, subjects, grades, marks, results, backlogs, promotions
- Exam logistics: rooms, seating, invigilators, hall tickets, practical schedules, batches, marks
- Fees: categories, structures, ledgers, collections, receipts, concessions, scholarships, reports
- Leave: types, balances, applications, approvals, cancellations, substitutes, holidays
- Notices: categories, notices, audiences, attachments, acknowledgements
- Reports: student, staff, attendance, result cards, fee receipts, hall tickets, activity
- System: settings, branding, health checks, chatbot knowledge

## Access Control

The app uses custom role and permission tables with route-level permission middleware. Data visibility is scoped by `AccessScopeService` across university, college, department, programme, staff assignment, and own-record levels.

When adding a new module, apply both:

- Route permission middleware, for example `permission:student.view`
- Scope checks through `AccessScopeService` before showing, updating, or deleting records

## Useful Commands

```bash
php artisan route:list
php artisan test
php artisan config:clear
php artisan optimize:clear
npm run build
```

System health checks are available inside the application at:

```text
/system/health
```

## Documentation

See `APPLICATION_USER_GUIDE.txt` for the full user-facing guide and module walkthrough.

## Sevalla Deployment

This repo is ready for Sevalla Git deployment.

Use:

```bash
composer install --no-dev --optimize-autoloader --no-interaction && npm ci && npm run build && composer run app:cache
```

Start command:

```bash
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
```

After first deploy:

```bash
composer run app:post-deploy
```

Copy `.env.sevalla.example` into Sevalla environment variables and fill in the real database and app URL values. See `docs/DEPLOYMENT.md` for the full checklist.
