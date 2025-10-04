# Staff Verification Workflow

## Overview
This document explains the complete workflow for Priority Customer ID Verification in the Seat Management system.

## Complete User Flow

### 1. Customer Registration (Kiosk)
**Route:** `/kiosk/registration`
- Customer fills in their details
- If they select **Senior Citizen** or **PWD** priority type
- System redirects to → `/kiosk/id-scanner?name=CustomerName&priority_type=senior`

### 2. ID Verification Screen (Kiosk)
**Route:** `/kiosk/id-scanner`
**View:** `resources/views/kiosk/staffverification.blade.php`

**What Customer Sees:**
- Welcome message with their name and priority type
- Instructions for ID verification
- **"Call Staff for ID Verification"** button (prominent amber button)
- Alternative options (skip priority or go back)

**What Happens When Customer Clicks "Call Staff":**
1. Frontend sends POST request to `/api/customer/request-verification`
2. Creates a `PriorityVerification` record with status `pending`
3. Shows overlay: "⏳ Waiting for Staff Verification"
4. Starts polling `/api/customer/verification-status/{id}` every 2 seconds
5. Waits for staff to verify...

### 3. Staff Dashboard (Admin)
**Route:** `/admin/priority-pin-dashboard`
**View:** `resources/views/admin/priority-pin-dashboard.blade.php`

**What Staff Sees:**
- **Pending Verifications** section (left panel)
  - Shows all customers waiting for verification
  - Updates automatically every 2 seconds
  - Displays: Customer name, priority type, time elapsed

**When Staff Clicks on a Pending Verification:**
1. Modal opens showing customer details
2. Staff verifies customer's ID physically
3. Staff clicks "Confirm & Generate PIN"
4. Backend:
   - Generates 4-digit PIN (e.g., 4829)
   - Updates `PriorityVerification` status to `verified`
   - Updates `Customer` record with verification details
5. Modal closes, verification disappears from pending list

### 4. Automatic Redirect (Kiosk)
**When staff completes verification:**
1. Customer's kiosk screen detects status change (via polling)
2. Shows: "✓ Verification Complete!"
3. Automatically redirects to → `/kiosk/review-details`
4. Customer continues with normal registration flow

---

## Technical Implementation

### API Endpoints

#### Customer Endpoints
```
POST /api/customer/request-verification
- Creates new verification request
- Parameters: customer_name, priority_type
- Returns: verification_id, status

GET /api/customer/verification-status/{id}
- Checks current verification status
- Returns: status, pin (if verified), timestamps
```

#### Staff Endpoints
```
GET /api/staff/pending-verifications
- Returns all pending verification requests
- Polls every 2 seconds by dashboard
- Returns: array of pending verifications

POST /api/staff/verify-and-generate-pin
- Completes verification and generates PIN
- Parameters: verification_id, verified_by
- Returns: verification details with PIN
```

### Database Structure

**Table:** `priority_verifications`
```sql
- id (primary key)
- customer_name (string)
- priority_type (enum: senior, pwd, pregnant)
- status (enum: pending, verified, rejected)
- pin (string, 4 digits, nullable, unique)
- requested_at (timestamp)
- verified_at (timestamp, nullable)
- verified_by (string, nullable)
- timestamps
```

### Key Files

**Controllers:**
- `app/Http/Controllers/VerificationController.php` - Handles all API requests
- `app/Http/Controllers/RegistrationController.php` - Handles registration flow

**Models:**
- `app/Models/PriorityVerification.php` - Verification model with helper methods
- `app/Models/Customer.php` - Customer model

**Views:**
- `resources/views/kiosk/staffverification.blade.php` - Customer verification screen
- `resources/views/admin/priority-pin-dashboard.blade.php` - Staff dashboard

**Routes:**
- `routes/web.php` - Web routes for views
- `routes/api.php` - API routes for AJAX requests

---

## Workflow Diagram

```
┌─────────────────────┐
│  Customer Arrives   │
│   at Kiosk          │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Registration Form  │
│  /kiosk/registration│
└──────────┬──────────┘
           │
           ▼
    Is Priority?
    (Senior/PWD)
           │
      Yes  │  No
    ┌──────┴──────┐
    │             │
    ▼             ▼
┌────────────┐  ┌──────────────────┐
│ID Scanner  │  │ Review Details   │
│Verification│  │ /review-details  │
└─────┬──────┘  └──────────────────┘
      │
      ▼
┌────────────────────────┐
│ Customer Clicks        │
│ "Call Staff"           │
└─────┬──────────────────┘
      │
      ▼
┌────────────────────────┐
│ Creates Verification   │
│ Request (API)          │
└─────┬──────────────────┘
      │
      ▼
┌────────────────────────┐
│ Waiting Screen         │
│ (Polling every 2s)     │
└─────┬──────────────────┘
      │
      │ (Meanwhile...)
      │
      ▼
┌──────────────────────────────┐
│  Staff Dashboard             │
│  /admin/priority-pin-dashboard│
└─────┬────────────────────────┘
      │
      ▼
┌────────────────────────┐
│ Staff Sees Pending     │
│ Verification Request   │
└─────┬──────────────────┘
      │
      ▼
┌────────────────────────┐
│ Staff Verifies ID      │
│ Generates PIN          │
└─────┬──────────────────┘
      │
      ▼
┌────────────────────────┐
│ Customer Screen        │
│ Detects Verification   │
└─────┬──────────────────┘
      │
      ▼
┌────────────────────────┐
│ Auto Redirect to       │
│ Review Details         │
└────────────────────────┘
```

---

## Alternative Actions

### Customer Can:
1. **Skip Priority** - Continue as regular customer (no priority benefits)
2. **Go Back** - Cancel and restart registration

### Staff Can:
1. **Verify** - Generate PIN and approve
2. **Reject** - (Future feature) Deny verification

---

## Testing the Workflow

### Test as Customer:
1. Go to: `http://127.0.0.1:8000/kiosk/registration`
2. Fill in details with "Senior" or "PWD" priority
3. Click "Continue"
4. Should redirect to ID Scanner page
5. Click "Call Staff for ID Verification"
6. Wait for staff verification...

### Test as Staff:
1. Open: `http://127.0.0.1:8000/admin/priority-pin-dashboard`
2. Wait for pending verification to appear
3. Click on the pending request
4. Click "Confirm & Generate PIN"
5. Verification should be removed from pending list

### Verify Integration:
1. Have kiosk screen and staff dashboard open side-by-side
2. Click "Call Staff" on kiosk
3. Within 2 seconds, request should appear on staff dashboard
4. Verify on staff dashboard
5. Within 2 seconds, kiosk should show "Verification Complete" and redirect

---

## Notes

- **Polling Interval:** 2 seconds (can be adjusted)
- **PIN Format:** 4-digit random number (1000-9999)
- **Timeout:** Currently no timeout (customer can wait indefinitely)
- **Multiple Requests:** System prevents duplicate pending requests for same customer
- **Session Handling:** Registration data persists in session until confirmed

---

## Future Enhancements

1. **Timeout Handling** - Auto-cancel verification after X minutes
2. **Staff Rejection** - Allow staff to reject verification with reason
3. **Notification Sound** - Alert staff when new request arrives
4. **Video Call** - Enable video verification instead of physical presence
5. **QR Code** - Customer scans QR instead of waiting
6. **Analytics** - Track verification times and staff performance

