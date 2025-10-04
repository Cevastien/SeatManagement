# Digital Waitlist - Complete Event Flow (Phases 8-12)
## Supplementary Events for Operational Lifecycle

This document supplements the original 52 events (Phases 1-7) with the missing operational events needed for a production-ready system.

---

## Phase 8: Queue Management & Customer Calling

### Event 53: Queue Status Check Request
When a customer wants to check their position, they can scan their receipt QR code or enter their queue number at the kiosk, triggering the system to retrieve their current status.

**Source:** Customer  
**Processing:** System queries database for customer's current position  
**Output:** Current position, wait time, customers ahead  
**Destination:** Display on kiosk or mobile device

---

### Event 54: Real-Time Queue Position Update
When any customer ahead is called, seated, or cancelled, the system automatically recalculates positions for all waiting customers and broadcasts the updates.

**Source:** System (automatic)  
**Processing:** Triggers on any queue state change  
**Output:** Updated positions for all affected customers  
**Destination:** Active customer displays + staff dashboard

---

### Event 55: Staff Views Queue Dashboard
When staff accesses the queue management screen, the system displays all waiting customers sorted by priority and registration time with color-coded status indicators.

**Source:** Staff  
**Processing:** Query all customers with status = 'waiting'  
**Output:** Ordered list with queue numbers, names, party sizes, wait times, priority badges  
**Destination:** Staff dashboard screen

---

### Event 56: Staff Calls Next Customer
When staff taps "Call Next" or selects a specific customer to call, the system marks that customer as 'called', records the call time, and displays the customer's information prominently.

**Source:** Staff  
**Processing:** 
- Update customer status to 'called'
- Record called_at timestamp
- Create queue event: 'customer_called'
- Start 5-minute grace period timer
- Send notification (if contact provided)

**Output:** Customer queue number displayed on call screen  
**Destination:** Customer display board + staff screen

---

### Event 57: Customer Call Display
When a customer is called, the system displays their queue number on a large TV/monitor with audio alert, cycling through the last 3-5 called customers.

**Source:** System (triggered by Event 56)  
**Processing:** Add to call display queue, play audio alert  
**Output:** Visual display: "NOW CALLING: P001" with audio ding  
**Destination:** Public display screens

---

### Event 58: Customer Responds to Call
When the customer approaches the host stand within the grace period, staff confirms their arrival and proceeds to seating assignment.

**Source:** Staff  
**Processing:** Staff marks customer as "present"  
**Output:** Ready for seating assignment  
**Destination:** Seating assignment screen

---

### Event 59: Customer No-Show Detection
If 5 minutes pass after calling without staff marking customer as present, the system automatically marks them as 'no_show', removes them from active queue, and logs the event.

**Source:** System (automatic timeout)  
**Processing:**
- Update status to 'no_show'
- Record no_show timestamp
- Create queue event: 'no_show'
- Reassign queue numbers
- Update wait times for remaining customers
- Optionally: Send "missed call" notification

**Output:** Customer removed from queue  
**Destination:** Audit log + staff notification

---

### Event 60: Manual No-Show by Staff
Staff can manually mark a customer as no-show if they observe the customer has left or if customer requests cancellation.

**Source:** Staff  
**Processing:** Same as Event 59 but with staff_id recorded  
**Output:** Customer marked as no_show with staff attribution  
**Destination:** Queue management system

---

### Event 61: Customer Requests Cancellation
When a customer approaches staff to cancel their reservation, staff cancels the queue entry and the system removes them from the waiting list.

**Source:** Customer (via staff)  
**Processing:**
- Update status to 'cancelled'
- Record cancellation timestamp and reason
- Create queue event: 'cancelled'
- Reassign queue numbers
- Update wait times

**Output:** Customer removed, queue reordered  
**Destination:** Queue management system + audit log

---

## Phase 9: Seating Assignment

### Event 62: Staff Initiates Table Assignment
When staff is ready to seat a customer, they tap "Assign Table" and the system displays available tables filtered by party size compatibility.

**Source:** Staff  
**Processing:** Query tables with status = 'available' and capacity >= party_size  
**Output:** List of suitable tables with table numbers and capacities  
**Destination:** Staff seating screen

---

### Event 63: Staff Selects Table
When staff selects a specific table from the available list, the system confirms the assignment and asks for final confirmation.

**Source:** Staff  
**Processing:** Display confirmation dialog with table details  
**Output:** "Seat [Customer Name] (party of N) at Table X?"  
**Destination:** Staff confirmation modal

---

### Event 64: Table Assignment Confirmed
When staff confirms the table assignment, the system marks the customer as 'seated', updates table status to 'occupied', and records all seating metrics.

**Source:** Staff  
**Processing:**
- Update customer status to 'seated'
- Record seated_at timestamp
- Update table status to 'occupied'
- Link customer to table
- Create queue event: 'seated'
- Calculate actual wait time vs estimated
- Reassign queue numbers for remaining customers

**Output:** Customer seated, table occupied, metrics recorded  
**Destination:** Queue system, table management system, analytics

---

### Event 65: Override Seating (Priority Jump)
If staff needs to seat someone out of order (VIP, urgent situation), they can override the queue order with a required reason code.

**Source:** Staff  
**Processing:**
- Display reason selection modal
- Record override reason and staff ID
- Proceed with seating as Event 64
- Flag in audit log as override

**Output:** Customer seated out of order with audit trail  
**Destination:** Queue system + audit log with warning flag

---

### Event 66: Table Not Available Error
If the selected table becomes unavailable between selection and confirmation (e.g., another staff member seated someone there), the system shows an error and refreshes the table list.

**Source:** System  
**Processing:** Detect table status change conflict  
**Output:** "Table X is no longer available. Please select another table."  
**Destination:** Staff screen with refreshed table list

---

## Phase 10: Dining & Checkout

### Event 67: Customer Dining Start
When the table assignment is confirmed, the system starts tracking the dining duration for table turnover analytics.

**Source:** System (automatic from Event 64)  
**Processing:** Record start time, track duration  
**Output:** Active dining session started  
**Destination:** Table management system

---

### Event 68: Staff Marks Table as "Preparing to Leave"
When staff observes customers requesting the bill or showing signs of leaving, they can mark the table status to alert other staff.

**Source:** Staff  
**Processing:** Update table status to 'preparing_to_leave'  
**Output:** Table flagged in system  
**Destination:** Staff dashboard (table status updates)

---

### Event 69: Table Cleared and Available
When customers leave and staff clears/cleans the table, they mark it as available, completing the customer's session.

**Source:** Staff  
**Processing:**
- Update customer status to 'completed'
- Record completed_at timestamp
- Update table status to 'available'
- Calculate total dining duration
- Create queue event: 'completed'
- Update analytics (actual vs estimated times)

**Output:** Customer completed, table available, metrics logged  
**Destination:** Queue system, table management, analytics

---

### Event 70: Extended Dining Alert
If a customer has been seated for longer than expected duration threshold (e.g., 90 minutes), the system alerts staff for awareness (not for action, just FYI).

**Source:** System (automatic)  
**Processing:** Check dining duration every 15 minutes  
**Output:** Notification: "Table X has been occupied for 95 minutes"  
**Destination:** Staff notification panel (low priority)

---

## Phase 11: Customer Communications

### Event 71: SMS Notification - Registration Confirmed
Immediately after successful registration, if customer provided contact number, the system sends an SMS with queue number and estimated wait time.

**Source:** System (triggered by Event 46 - Registration Confirmed)  
**Processing:** 
- Format SMS message
- Send via SMS gateway
- Log delivery status

**Output:** SMS message sent  
**Message:** "Thanks for joining! Your queue: P001. Estimated wait: 20-30 min. Track status: [short link]"  
**Destination:** Customer's mobile number

---

### Event 72: SMS Notification - Almost Your Turn (3 Customers Ahead)
When there are exactly 3 customers ahead, the system sends an alert to prepare the customer to return to restaurant vicinity.

**Source:** System (automatic)  
**Processing:** Triggered when position = 4  
**Output:** SMS notification  
**Message:** "You're almost up! 3 customers ahead. Please return to [Restaurant Name]."  
**Destination:** Customer's mobile number

---

### Event 73: SMS Notification - Customer Called
When staff calls the customer (Event 56), the system immediately sends an SMS notification.

**Source:** System (triggered by Event 56)  
**Processing:** Send urgent SMS  
**Output:** High-priority SMS  
**Message:** "🔔 NOW CALLING: P001! Please proceed to host stand. You have 5 minutes."  
**Destination:** Customer's mobile number

---

### Event 74: SMS Notification - Missed Call Warning
If customer doesn't respond within 3 minutes of being called, send a warning SMS before no-show.

**Source:** System (automatic, 3 minutes after Event 56)  
**Processing:** Check if customer still not present  
**Output:** Warning SMS  
**Message:** "⚠️ URGENT: You were called 3 minutes ago (P001). Please respond within 2 minutes or your spot will be released."  
**Destination:** Customer's mobile number

---

### Event 75: SMS Notification - No Show Confirmation
After automatic no-show (Event 59), send confirmation SMS to customer.

**Source:** System (triggered by Event 59)  
**Processing:** Send notification SMS  
**Output:** Informational SMS  
**Message:** "Your reservation P001 has been cancelled due to no response. Please register again if you'd like to join the queue."  
**Destination:** Customer's mobile number

---

## Phase 12: System Management & Error Handling

### Event 76: Concurrent Registration Detection
When multiple customers attempt to register with the same contact number within 5 minutes, the system detects potential duplicate and requires disambiguation.

**Source:** System  
**Processing:** Check for recent registrations with same contact  
**Output:** Warning modal to second customer  
**Message:** "This number was just used 2 minutes ago for queue P001. Is this a different party?"  
**Destination:** Kiosk screen

---

### Event 77: Network Connectivity Loss
When the kiosk loses connection to the server, the system detects the loss and displays a reconnection screen.

**Source:** System  
**Processing:** 
- Detect failed API calls
- Preserve session data locally
- Attempt auto-reconnect every 5 seconds
- Display user-friendly message

**Output:** Connection lost screen  
**Message:** "Connecting to server... Please wait. Your data is safe."  
**Destination:** Kiosk screen

---

### Event 78: Network Connectivity Restored
When connection is re-established, the system syncs any pending data and resumes normal operation.

**Source:** System  
**Processing:**
- Sync local session data to server
- Refresh queue information
- Resume normal flow

**Output:** Connection restored, continue registration  
**Destination:** Resume previous screen

---

### Event 79: System Crash Recovery
If the kiosk application crashes or browser closes during registration, the system recovers the session when reopened within 10 minutes.

**Source:** System  
**Processing:**
- Check for incomplete session in local storage
- Retrieve session data
- Display recovery modal

**Output:** "We detected an incomplete registration. Would you like to continue where you left off?"  
**Destination:** Recovery modal on kiosk

---

### Event 80: Kiosk Hardware Error
If hardware fails (touchscreen, printer, camera), the system detects the failure and guides customer to alternative registration method.

**Source:** System  
**Processing:** Hardware diagnostics check  
**Output:** Error message with QR code to mobile registration  
**Message:** "Kiosk temporarily unavailable. Scan QR code to register on your phone, or see host stand."  
**Destination:** Kiosk screen

---

### Event 81: Database Connection Failure
If the database becomes unavailable, the system switches to read-only mode and prevents new registrations.

**Source:** System  
**Processing:**
- Detect database connection failure
- Display maintenance message
- Alert system administrators

**Output:** "System temporarily offline for maintenance. Please see host stand for assistance."  
**Destination:** Kiosk screen + admin alert

---

### Event 82: Staff Dashboard Session Timeout
If staff dashboard is inactive for 15 minutes, the system locks the screen and requires re-authentication.

**Source:** System (automatic)  
**Processing:**
- Detect inactivity
- Lock screen
- Preserve session data

**Output:** Lock screen: "Session locked. Please re-authenticate."  
**Destination:** Staff dashboard

---

### Event 83: Queue Reset at End of Day
At closing time or when staff initiates end-of-day, the system marks all waiting customers as 'cancelled', archives the day's data, and resets queue numbers.

**Source:** Staff or System (scheduled)  
**Processing:**
- Mark all waiting customers as 'day_end_cancelled'
- Archive day's transactions
- Reset queue counters
- Generate daily report

**Output:** Queue cleared, new day ready  
**Destination:** Database + daily report

---

### Event 84: Emergency Queue Clear
If staff needs to clear the entire queue due to emergency (power outage, evacuation, etc.), they can trigger emergency clear with required confirmation.

**Source:** Staff  
**Processing:**
- Display confirmation: "This will cancel ALL waiting customers. Are you sure?"
- Require supervisor PIN
- Mark all as 'emergency_cancelled'
- Send SMS to all affected customers
- Log emergency event

**Output:** All customers notified, queue cleared, incident logged  
**Destination:** All affected customers + audit log

---

## Phase 13: Analytics & Reporting

### Event 85: Daily Statistics Generation
At midnight or when requested, the system generates comprehensive daily statistics.

**Source:** System (automatic) or Staff  
**Processing:**
- Calculate average wait times (priority vs regular)
- Count registrations by priority type
- Calculate seat turnover rates
- Identify peak hours
- Calculate no-show percentages
- Compare estimated vs actual wait times

**Output:** Daily analytics report  
**Destination:** Staff dashboard + database

---

### Event 86: Wait Time Accuracy Calculation
After each customer is seated, the system calculates the difference between estimated and actual wait time to improve future estimates.

**Source:** System (automatic from Event 64)  
**Processing:**
- Calculate: actual_wait_time - estimated_wait_time
- Store variance for machine learning
- Update historical averages for this time slot

**Output:** Accuracy metric stored  
**Destination:** Analytics database

---

### Event 87: Peak Hour Detection
When the queue reaches a threshold length (e.g., 15+ customers), the system flags this as a peak period for staffing alerts.

**Source:** System (automatic)  
**Processing:** Monitor queue length continuously  
**Output:** Alert: "Peak period detected - 18 customers waiting"  
**Destination:** Staff notification + manager alert

---

### Event 88: Customer Flow Rate Monitoring
The system continuously monitors registration rate, seating rate, and average dining duration to predict bottlenecks.

**Source:** System (automatic, every 5 minutes)  
**Processing:**
- Calculate: registrations per hour
- Calculate: seatings per hour
- Compare rates and predict queue growth
- Alert if backlog forming

**Output:** Proactive alert if queue growing faster than seating rate  
**Destination:** Staff dashboard

---

## Summary of Additions

**New Events Added:** 36 (Events 53-88)  
**Total System Events:** 88 (Original 52 + New 36)

### Event Distribution:
- **Phase 8:** Queue Management (9 events)
- **Phase 9:** Seating Assignment (5 events)
- **Phase 10:** Dining & Checkout (4 events)
- **Phase 11:** Customer Communications (5 events)
- **Phase 12:** System Management & Error Handling (9 events)
- **Phase 13:** Analytics & Reporting (4 events)

---

## Updated QA Assessment

### ✅ **Now Complete:**
- ✅ Queue management lifecycle (calling, seating, no-shows)
- ✅ Staff dashboard operations
- ✅ Customer communication via SMS
- ✅ System error recovery scenarios
- ✅ Table management integration
- ✅ Real-time updates and notifications
- ✅ Analytics and reporting

### **QA Verdict: ✅ APPROVED FOR DEVELOPMENT**

This expanded specification now covers:
1. **Complete customer lifecycle** (registration → waiting → called → seated → completed)
2. **Staff operational workflows** (queue viewing, calling, seating, table management)
3. **Error handling and recovery** (network, hardware, crashes)
4. **Customer communications** (SMS notifications at key points)
5. **System monitoring** (analytics, peak detection, performance tracking)

**Status:** Production-ready specification with comprehensive event coverage.

---

## Implementation Priority

### **Phase 1 (MVP):** Events 1-52 (Original - Onboarding)
**Status:** ✅ COMPLETED (just fixed all 5 critical blockers!)

### **Phase 2 (Core Operations):** Events 53-64 (Queue Management + Seating)
**Priority:** HIGH  
**Estimated Effort:** 2-3 weeks  
**Required For:** Basic restaurant operations

### **Phase 3 (Communications):** Events 71-75 (SMS Notifications)
**Priority:** MEDIUM  
**Estimated Effort:** 1 week  
**Required For:** Enhanced customer experience

### **Phase 4 (Advanced):** Events 76-88 (Error Handling + Analytics)
**Priority:** MEDIUM-LOW  
**Estimated Effort:** 2 weeks  
**Required For:** Production stability and insights

---

## Next Steps

1. **Review** this supplementary specification with stakeholders
2. **Prioritize** which phases to implement first
3. **Design** database schema for new events (tables, call_logs, etc.)
4. **Implement** Phase 2 (Queue Management) as next sprint
5. **Set up** SMS gateway integration for Phase 3
6. **Plan** monitoring and analytics infrastructure for Phase 4

---

**Document Version:** 2.0  
**Last Updated:** October 4, 2025  
**Status:** Ready for Development Planning

