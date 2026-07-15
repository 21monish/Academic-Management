# TODO - Programme Management Module

## Step 1: Implement Programme Backend (Controller/Service/Requests)
- [x] Create `app/Http/Controllers/Academic/ProgrammeController.php`
- [x] Create `app/Services/ProgrammeService.php`
- [x] Create `app/Http/Requests/Academic/StoreProgrammeRequest.php`
- [x] Create `app/Http/Requests/Academic/UpdateProgrammeRequest.php`

## Step 2: Implement Programme Views
- [x] Create `resources/views/programme/index.blade.php`
- [x] Create `resources/views/programme/create.blade.php`
- [x] Create `resources/views/programme/edit.blade.php`
- [x] Create `resources/views/programme/show.blade.php`
- [x] Create `resources/views/programme/_form.blade.php`

## Step 3: Register Routes
- [x] Add programme routes into `routes/academic.php`
- [x] Ensure programme routes are reachable under the `academic` prefix

## Step 4: Verification
- [x] `php artisan route:list --path=programmes`
- [x] `php artisan test --stop-on-failure`
- [ ] Fix any errors until tests pass

## Step 5: Fix Teaching Staff Assignments Visibility
- [x] Fix `AccessScopeService::forUser()` so Teaching Staff scope uses resolved `staff_id` instead of only existing active assignments
- [x] Verify `/attendance/assignments?staff_id=...` shows existing assignments with no incorrect "No assignments" message
- [x] Clear caches after change with `php artisan optimize:clear`
