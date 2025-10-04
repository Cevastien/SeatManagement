# Test the Staff Verification System

## Quick Test Guide

### 1. Test the ID Verification Screen (Customer View)

Open in your browser:
```
http://127.0.0.1:8000/kiosk/id-scanner?name=Princess%20Mae%20L.%20Planas&priority_type=senior
```

**Expected Result:**
- ✅ Page loads successfully (no 500 error)
- ✅ Shows customer name: "Princess Mae L. Planas"
- ✅ Shows priority type: "Senior Citizen"
- ✅ Large amber button: "Call Staff for ID Verification"
- ✅ Alternative options visible (Skip Priority, Go Back)

### 2. Test the Staff Dashboard

Open in another tab/window:
```
http://127.0.0.1:8000/admin/priority-pin-dashboard
```

**Expected Result:**
- ✅ Page loads successfully
- ✅ Shows "Priority Verification Dashboard"
- ✅ Two panels: "Pending Verifications" and "Recent Verifications"
- ✅ Initially shows "No pending verifications"

### 3. Test the Complete Workflow (Side-by-Side)

**Setup:**
1. Open staff dashboard in one window
2. Open customer verification screen in another window

**Test Steps:**

#### Step 1: Customer Calls Staff
On the customer screen:
1. Click **"Call Staff for ID Verification"** button
2. **Expected:** Overlay appears with "⏳ Waiting for Staff Verification"

#### Step 2: Staff Receives Request
On the staff dashboard (within 2 seconds):
1. **Expected:** Customer "Princess Mae L. Planas" appears in "Pending Verifications"
2. Shows: Name, Priority type (Senior Citizen), time elapsed

#### Step 3: Staff Verifies
On the staff dashboard:
1. Click on the pending verification
2. **Expected:** Modal opens showing customer details
3. Click **"Confirm & Generate PIN"**
4. **Expected:** 
   - Alert shows PIN (e.g., "Verification complete! Princess Mae L. Planas has been verified with PIN 4829")
   - Modal closes
   - Verification disappears from pending list

#### Step 4: Customer Auto-Redirects
On the customer screen (within 2 seconds after staff verification):
1. **Expected:** Overlay updates to "✓ Verification Complete!"
2. **Expected:** Auto-redirects to `/kiosk/review-details`

---

## API Testing

### Test Customer Request Verification API

```bash
curl -X POST http://127.0.0.1:8000/api/customer/request-verification \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Test Customer",
    "priority_type": "senior"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Verification request submitted successfully",
  "verification": {
    "id": 1,
    "status": "pending",
    "requested_at": "2025-10-04T14:00:00.000000Z"
  }
}
```

### Test Get Pending Verifications API

```bash
curl http://127.0.0.1:8000/api/staff/pending-verifications
```

**Expected Response:**
```json
{
  "success": true,
  "has_pending": true,
  "pending_verifications": [
    {
      "id": 1,
      "customer_name": "Test Customer",
      "priority_type": "senior",
      "priority_display": "Senior Citizen",
      "status": "pending",
      "requested_at": "Oct 04, 2025 02:00 PM",
      "time_elapsed": 5
    }
  ],
  "count": 1
}
```

### Test Verification Status API

```bash
curl http://127.0.0.1:8000/api/customer/verification-status/1
```

**Expected Response (Pending):**
```json
{
  "success": true,
  "verification": {
    "id": 1,
    "customer_name": "Test Customer",
    "priority_type": "senior",
    "status": "pending",
    "pin": null,
    "requested_at": "2025-10-04T14:00:00.000000Z",
    "verified_at": null
  }
}
```

**Expected Response (After Verification):**
```json
{
  "success": true,
  "verification": {
    "id": 1,
    "customer_name": "Test Customer",
    "priority_type": "senior",
    "status": "verified",
    "pin": "4829",
    "requested_at": "2025-10-04T14:00:00.000000Z",
    "verified_at": "2025-10-04T14:02:30.000000Z"
  }
}
```

---

## Test Full Registration Flow

### Complete End-to-End Test:

1. **Start at Registration**
   ```
   http://127.0.0.1:8000/kiosk/registration
   ```

2. **Fill in Form:**
   - Name: "Princess Mae L. Planas"
   - Party Size: 2
   - Contact: 09123456789
   - Select: "Yes, I am..." → Senior Citizen

3. **Submit Form**
   - Should redirect to ID Scanner page automatically

4. **Click "Call Staff"**
   - Overlay appears

5. **Staff Verifies** (on dashboard)
   - Request appears
   - Click and confirm

6. **Customer Screen**
   - Auto-redirects to Review Details
   - Shows customer info with "Verified" badge

7. **Complete Registration**
   - Review details
   - Confirm and print receipt

---

## Troubleshooting

### Issue: "View not found" error
**Solution:** Make sure `staffverification.blade.php` exists in `resources/views/kiosk/`

### Issue: API returns 404
**Solution:** Run `php artisan route:clear` to clear route cache

### Issue: Staff dashboard doesn't show pending requests
**Solution:** Check browser console for errors, verify API endpoint is working

### Issue: Customer screen doesn't auto-redirect
**Solution:** 
- Check browser console for polling errors
- Verify verification_id is set correctly
- Check if staff actually completed verification

### Issue: Database errors
**Solution:** Run migrations:
```bash
php artisan migrate:fresh
php artisan db:seed
```

---

## Database Check

### View Pending Verifications:
```sql
SELECT * FROM priority_verifications WHERE status = 'pending';
```

### View All Verifications:
```sql
SELECT * FROM priority_verifications ORDER BY requested_at DESC LIMIT 10;
```

### Clear Test Data:
```sql
DELETE FROM priority_verifications WHERE customer_name LIKE '%Test%';
```

---

## Success Criteria

✅ Customer can request verification
✅ Request appears on staff dashboard within 2 seconds
✅ Staff can verify and generate PIN
✅ Customer screen auto-redirects after verification
✅ No console errors in browser
✅ All API endpoints return proper responses
✅ Database records created correctly

---

## Performance Notes

- **Polling Frequency:** Every 2 seconds (adjustable in JavaScript)
- **Expected Latency:** < 2 seconds for updates
- **Concurrent Users:** Tested with multiple simultaneous verifications
- **Database Impact:** Minimal with proper indexing

---

## Next Steps After Testing

1. ✅ Test with different priority types (Senior, PWD, Pregnant)
2. ✅ Test "Skip Priority" functionality
3. ✅ Test "Go Back" functionality
4. ✅ Test multiple pending requests
5. ✅ Test edge cases (duplicate requests, network errors)
6. 📝 Add timeout handling (future enhancement)
7. 📝 Add notification sound for staff (future enhancement)

