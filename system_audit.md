# Help Desk System — Full Audit Report
**Date:** 2026-03-15 | **Scope:** All roles (Admin, Manager, Agent, User), routing, models, middleware, controllers

---

## Overall Verdict
The system is **well-structured and production-grade in its core design**. The role/permission layering, SLA engine, auto-assignment, activity logging, and Inertia/Eloquent usage are all solid. However, there are several gaps — some security-relevant — that should be addressed before this goes live.

---

## ✅ What's Done Well

| Area | Notes |
|---|---|
| **Role-based routing** | Clean separation: `role:admin`, `role:manager`, `role:agent`, `role:user` middlewares on all route groups |
| **Permission middleware** | Manager and User routes have granular `permission:view_ticket`, `create_ticket`, `edit_ticket` guards |
| **Activity logging** | Every mutation (status change, assignment, SLA change, message, attachment) is logged with old/new values |
| **SLA engine** | Dept-specific SLA takes precedence over global; `due_at` auto-computed; SLA recalculated on dept transfer |
| **Auto-assignment** | Load-balanced (fewest open tickets) within the department — correct for a help desk |
| **Ticket number generation** | Cache lock + DB transaction ensures no duplicate ticket numbers under concurrent load |
| **Soft deletes** | Tickets, Users, SLA policies all use `SoftDeletes` — data is never permanently destroyed by accident |
| **Eager loading** | [show()](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Manager/ManagerTicketController.php#147-243) methods eager-load all related models in a single query chain — no N+1 issues |
| **FormRequest** | [StoreTicketRequest](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Requests/StoreTicketRequest.php#9-59) encapsulates validation + priority/status resolution cleanly |
| **Inertia shared state** | Role, permissions, flash messages, and impersonation data all shared globally |
| **Impersonation** | Admin can impersonate any user with session-based guard and a stop-impersonate route |
| **Google OAuth** | Handled gracefully alongside password auth; avatar fallback chain works correctly |

---

## 🔴 Critical / Security Issues

### 1. Admin ticket routes have NO permission middleware
**File:** [routes/admin.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/routes/admin.php)

```php
// ALL admin ticket routes have no permission guards:
Route::get('/tickets/{ticket}', ...)   // show
Route::patch('/tickets/{ticket}', ...) // update
Route::post('/tickets/{ticket}/messages', ...) // storeMessage
```
The Manager and User route files wrap mutations in `permission:edit_ticket` — but the Admin routes rely solely on `role:admin`. If a rogue admin role exists or role checking is bypassed, there's no permission-level fallback.

**Fix:** Either add `permission:` middleware to admin ticket mutations OR add `$this->authorize()` calls in the controller.

---

### 2. [update()](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Manager/ManagerTicketController.php#244-256) does not authorize ownership — any admin can edit any ticket
**File:** [AdminTicketController.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Admin/AdminTicketController.php) line 162

```php
public function update(Request $request, int $id)
{
    $ticket = Ticket::findOrFail($id); // no ownership check
```
Any admin or manager can PATCH any ticket's assigned_to, status, or department — including tickets belonging to another department they have no access to. The Manager controller _does_ call `$this->authorizeTicketAccess($id)`, but this is absent in Admin.

**Recommendation:** For multi-tenant/multi-department setups, add a department-scope check even for admins, or document explicitly that admins are globally trusted.

---

### 3. Assignment activity log stores raw user IDs, not names
**File:** [AdminTicketController.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Admin/AdminTicketController.php) line 190

```php
$this->logTicketActivity($ticket->id, 'assignment_changed',
    (string) $oldAssigned,   // ← "5"  (just an ID)
    (string) $ticket->assigned_to // ← "12" (just an ID)
);
```
The activity log UI displays `old_value → new_value` directly to users. Showing `"5 → 12"` is meaningless. When a user is soft-deleted later, the ID becomes unresolvable.

**Fix:** Resolve and store names at log time:
```php
$oldName = $oldAssigned ? User::find($oldAssigned)?->full_name : 'Unassigned';
$newName = $ticket->assigned_to ? User::find($ticket->assigned_to)?->full_name : 'Unassigned';
$this->logTicketActivity($ticket->id, 'assignment_changed', $oldName, $newName);
```

Similarly for `department_changed` — currently logs `"3 → 7"` instead of department names, and `sla_applied` logs the SLA ID instead of the SLA name.

---

### 4. No CSRF consideration for the attachment download route
**File:** [routes/admin.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/routes/admin.php) line 50

```php
Route::get('/tickets/{ticket}/attachments/{attachment}', ...)
    ->name('tickets.attachments.download');
```
GET routes streaming files are generally fine, but there's no check that the `attachment.ticket_id` matches the [ticket](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Models/TicketActivityLog.php#22-26) route parameter in the URL — an attacker could enumerate `GET /admin/tickets/1/attachments/999` to download attachments from ticket 999 while only needing access to ticket 1.

**Fix (already partially done):** The controller uses `->where('ticket_id', $ticketId)` which is correct. ✅ Just make sure this check stays in place.

---

## 🟡 Medium Priority Issues

### 5. `first_response_at` is missing from the `$casts` and `$fillable` on [Ticket](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Models/Ticket.php#10-88)
**File:** [app/Models/Ticket.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Models/Ticket.php)

```php
protected $casts = [
    'first_response_at' => 'datetime', // ✅ cast is present
    ...
];
protected $fillable = [
    // ❌ 'first_response_at' is NOT in fillable
];
```
In [storeMessage()](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Admin/AdminTicketController.php#231-259), `$ticket->first_response_at = now()` followed by `$ticket->save()` works because you're using direct property assignment, but if you ever try `Ticket::create([..., 'first_response_at' => ...])` or `$ticket->fill([...])`, it will be silently ignored.

**Fix:** Add `'first_response_at'` to `$fillable` in [Ticket.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Models/Ticket.php).

---

### 6. User route permission is wrong — `create_ticket` guards the [storeMessage](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Admin/AdminTicketController.php#231-259) route
**File:** [routes/user.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/routes/user.php) lines 25-34

```php
Route::middleware(['permission:create_ticket'])->group(function () {
    Route::post('/tickets/{ticket}/messages', ...); // ← should be edit_ticket or reply_ticket
    Route::post('/tickets/{ticket}/attachments', ...);
});
```
Replying to an existing ticket is semantically an *edit*, not a *creation*. A user with `create_ticket` permission but not `edit_ticket` could still reply — but a user with only `edit_ticket` could not reply to their own tickets.

**Fix:** Move message and attachment sub-routes under `permission:edit_ticket`, or create a `reply_ticket` permission.

---

### 7. Agent routes have no permission middleware at all
**File:** [routes/agent.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/routes/agent.php)

All agent ticket routes — including [resolve](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Agent/TicketController.php#261-271), [update](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Manager/ManagerTicketController.php#244-256), [storeAttachment](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/User/UserTicketController.php#310-348) — are protected only by `role:agent`. There are no `permission:` guards. This is inconsistent with how Manager and User routes work, and means you can't granularly revoke agent capabilities without removing the entire role.

**Recommendation:** Add `permission:edit_ticket` at minimum to `patch`, [resolve](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Agent/TicketController.php#261-271), [reopen](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Agent/TicketController.php#272-282), and [storeMessage](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Admin/AdminTicketController.php#231-259) agent routes — consistent with manager routing.

---

### 8. Admin `stats` in [getTickets()](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Manager/ManagerTicketController.php#23-124) includes soft-deleted tickets
**File:** [AdminTicketController.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Admin/AdminTicketController.php) line 516

```php
$statusCounts = Ticket::select('status_id', ...)->groupBy('status_id')->pluck(...);
$stats = ['total' => Ticket::count(), ...];
```
The [Ticket](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Models/Ticket.php#10-88) model uses `SoftDeletes`. Eloquent automatically excludes soft-deleted records from queries — so this is actually fine. ✅ However, it's worth noting that the stats counts and the ticket list counts will align correctly only because both queries exclude soft-deleted records.

---

### 9. [getAssignableUsers()](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Admin/AdminTicketController.php#369-396) has a logic gap when `$departmentId` is null
**File:** [AdminTicketController.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Admin/AdminTicketController.php) lines 372-395

When `$departmentId` is `null`, the join to `user_departments` is skipped entirely, so **all** managers and agents system-wide are returned as assignable. This is correct for the Admin show page (admin can assign anyone), but could be surprising if called in a non-admin context.

**Status:** Acceptable for admin use, but document the behavior.

---

### 10. [TicketActivityLog](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Models/TicketActivityLog.php#9-32) uses `SoftDeletes` unnecessarily
**File:** [app/Models/TicketActivityLog.php](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Models/TicketActivityLog.php) line 11

Activity logs are audit records — they should be **immutable and never deletable**. Soft-deleting them means they could be hidden from audits. A `deleted_at` column on an audit log undermines the integrity guarantee of the log.

**Fix:** Remove `use SoftDeletes` from [TicketActivityLog](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Models/TicketActivityLog.php#9-32). Add a DB-level constraint or rely on the append-only nature of the model.

---

## 🟢 Low Priority / Best Practice Gaps

### 11. Controllers share too much serialization logic — no API Resource layer
Every [show()](file:///c:/xampp/htdocs/HELP-DESK-laravel/app/Http/Controllers/Manager/ManagerTicketController.php#147-243) controller manually maps Eloquent models to arrays (60-80 lines of repeated `'id' => $ticket->id` style code). This duplicates format logic across Admin, Manager, Agent, and User controllers.

**Recommendation:** Create Laravel API Resources:
- `TicketResource`, `MessageResource`, `AttachmentResource`, `ActivityLogResource`
- This ensures a single source of truth for the serialized shape of each model

---

### 12. No notifications on ticket events
The system has an `App\Notifications` directory but ticket events (new ticket, assignment, reply, status change) don't appear to trigger any user notifications.

**Recommendation for production:** Add `Notification::send()` calls on:
- Ticket assigned → notify assignee
- New reply (non-internal) → notify ticket creator
- Status changed to Resolved/Closed → notify creator
- SLA near breach → notify assignee (requires a scheduled job)

---

### 13. `updated_at` is missing from the Manager ticket show page payload
**File:** `ManagerTicketController.php` show method

The Admin controller passes `updated_at` to the frontend, but the Manager controller does **not** include it in the ticket payload. The Manager's `Show.vue` now uses `formatDate(ticket.updated_at)` which will silently render `"—"`.

**Fix:** Add `'updated_at' => $ticket->updated_at?->toDateTimeString()` to the Manager `show()` method's ticket array.

---

### 14. Ticket number collision risk under certain edge cases
**File:** `AdminTicketController.php` lines 327-364

The ticket number generation uses `Cache::lock()` with a 10-second block and `DB::transaction()`. This is solid. However, the `MAX` derivation:
```php
$maxNum = $existing->max(fn ($num) => (int) Str::afterLast($num, '-'));
```
...loads **all ticket numbers for the year** into memory. For a busy system with 10,000+ tickets/year, this becomes expensive. Consider using a DB sequence or a `MAX(ticket_number)` SQL query with index instead.

---

### 15. `first_response_at` only tracks the *first admin/agent reply*, not first meaningful SLA response
**File:** `AdminTicketController.php` line 250

```php
if (!$ticket->first_response_at && !$isInternal) {
    $ticket->first_response_at = now();
}
```
This correctly ignores internal notes. However, it doesn't distinguish between *who* replied — if the customer replies to their own ticket first (re-opening it), that would count. In a strict SLA context, `first_response_at` should only be set when an **agent/manager/admin** sends a non-internal reply. Consider checking `Auth::user()->isAgent() || isManager() || isAdmin()`.

---

## Summary Table

| # | Severity | Area | Fix Required |
|---|---|---|---|
| 1 | 🔴 Critical | Admin route permission middleware missing | Yes |
| 2 | 🔴 Critical | No ownership check in Admin `update()` | Yes (document or fix) |
| 3 | 🔴 Critical | Activity log stores raw IDs not names | Yes |
| 4 | 🟡 Medium | Attachment download cross-ticket enumeration | Already mitigated ✅ |
| 5 | 🟡 Medium | `first_response_at` missing from `$fillable` | Yes |
| 6 | 🟡 Medium | User `storeMessage` under wrong permission | Yes |
| 7 | 🟡 Medium | Agent routes lack permission middleware | Recommended |
| 8 | 🟢 Low | Stats include soft-deleted check | N/A (already correct) ✅ |
| 9 | 🟢 Low | `getAssignableUsers(null)` behavior undocumented | Document |
| 10 | 🟡 Medium | `TicketActivityLog` should not use SoftDeletes | Yes |
| 11 | 🟢 Low | No API Resource layer — repeated serialization | Recommended |
| 12 | 🟢 Low | No email notifications on ticket events | Recommended |
| 13 | 🔴 Critical | Manager show missing `updated_at` in payload | Yes |
| 14 | 🟢 Low | Ticket number MAX query scales poorly | Recommended for >5K tickets/yr |
| 15 | 🟢 Low | `first_response_at` doesn't check who replied | Recommended |
