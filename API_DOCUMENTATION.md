# Café Gervacio's - Seat Management API Documentation

## Overview

This API provides endpoints for managing the queue system, customer registrations, priority verifications, table management, and analytics for Café Gervacio's Seat Management System.

**Base URL:** `http://localhost:8000` (Local) | `https://api.cafegervacios.com` (Production)

**Version:** 1.0.0

**Rate Limiting:** 120 requests per minute per IP address

**Authentication:** CSRF token required for all POST requests

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Authentication](#authentication)
3. [Rate Limiting](#rate-limiting)
4. [Error Handling](#error-handling)
5. [Endpoints](#endpoints)
   - [Utility](#utility)
   - [Queue Management](#queue-management)
   - [Customer Management](#customer-management)
   - [Priority Verification](#priority-verification)
   - [Table Management](#table-management)
   - [Settings](#settings)
   - [Analytics](#analytics)

---

## Getting Started

### Importing the Postman Collection

1. Download the Postman collection from `/postman/SeatManagement-API.postman_collection.json`
2. Import environment files:
   - Local: `/postman/SeatManagement-Local.postman_environment.json`
   - Production: `/postman/SeatManagement-Production.postman_environment.json`
3. Open Postman and import the collection file
4. Select the appropriate environment (Local or Production)
5. Run "Get CSRF Token" request first to authenticate

### Quick Start Example

```bash
# 1. Get CSRF Token
curl http://localhost:8000/api/csrf-token

# 2. Make authenticated request
curl -X POST http://localhost:8000/api/customer/request-verification \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-token-here" \
  -d '{"customer_id": 1, "priority_type": "senior"}'
```

---

## Authentication

All POST requests require a CSRF token for security.

### Getting a CSRF Token

**Endpoint:** `GET /api/csrf-token`

**Response:**
```json
{
  "csrf_token": "eyJpdiI6ImR3TnRXL..."
}
```

**Usage:**
Include the token in the `X-CSRF-TOKEN` header for all POST requests:

```http
POST /api/customer/request-verification
X-CSRF-TOKEN: eyJpdiI6ImR3TnRXL...
Content-Type: application/json
```

---

## Rate Limiting

**Limit:** 120 requests per minute per IP address

**Headers:**
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Requests remaining in current window

**429 Response (Too Many Requests):**
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "retry_after": 45
}
```

---

## Error Handling

### Standard Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

### HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

---

## Endpoints

## Utility

### Get CSRF Token

Retrieves a CSRF token for authenticated requests.

**Endpoint:** `GET /api/csrf-token`

**Response:**
```json
{
  "csrf_token": "eyJpdiI6ImR3TnRXL..."
}
```

---

## Queue Management

### Get Queue Statistics

Returns current queue statistics.

**Endpoint:** `GET /api/queue/stats`

**Response:**
```json
{
  "success": true,
  "data": {
    "total_waiting": 12,
    "total_called": 3,
    "total_seated": 5,
    "average_wait_time": 25,
    "priority_counts": {
      "normal": 8,
      "senior": 2,
      "pwd": 1,
      "pregnant": 1
    },
    "estimated_wait_time": 30
  }
}
```

**Use Cases:**
- Dashboard displays
- Queue status boards
- Real-time monitoring

---

### Get Queue Summary

Returns a summary for public display boards.

**Endpoint:** `GET /api/queue/summary`

**Response:**
```json
{
  "success": true,
  "data": {
    "waiting": [
      {
        "queue_number": "001",
        "party_size": 4,
        "estimated_wait": 15,
        "is_priority": true
      }
    ],
    "next_up": [
      {
        "queue_number": "003",
        "party_size": 3
      }
    ]
  }
}
```

---

### Get Queue Update

Real-time queue updates for polling.

**Endpoint:** `GET /api/queue/update`

**Polling Interval:** Every 10-30 seconds

---

### Update Wait Times

Recalculates estimated wait times for all customers.

**Endpoint:** `POST /api/queue/update-wait-times`

**Headers:**
```http
Content-Type: application/json
X-CSRF-TOKEN: your-token-here
```

**Request Body:** Empty `{}`

**Response:**
```json
{
  "success": true,
  "message": "Wait times updated successfully",
  "updated_count": 12
}
```

---

## Customer Management

### Get Customer Current Wait Time

Returns current wait time for a specific customer.

**Endpoint:** `GET /api/customer/{customerId}/current-wait`

**Parameters:**
- `customerId` (path, integer, required): Customer ID

**Response (Waiting):**
```json
{
  "success": true,
  "status": "waiting",
  "wait_time_minutes": 25,
  "formatted": "~25 mins",
  "position": 5,
  "queue_number": "042"
}
```

**Response (Seated):**
```json
{
  "success": true,
  "status": "seated",
  "message": "You are now seated. Enjoy your meal!",
  "table_number": "A-5"
}
```

---

### Get Customer Position

Returns queue position by queue number.

**Endpoint:** `GET /api/customer/{queueNumber}/position`

**Parameters:**
- `queueNumber` (path, string, required): Queue number (e.g., "042")

**Response:**
```json
{
  "success": true,
  "queue_number": "042",
  "position": 5,
  "total_ahead": 4,
  "estimated_wait": 25,
  "status": "waiting"
}
```

---

### Request Priority Verification

Submits a priority verification request.

**Endpoint:** `POST /api/customer/request-verification`

**Headers:**
```http
Content-Type: application/json
X-CSRF-TOKEN: your-token-here
```

**Request Body:**
```json
{
  "customer_id": 1,
  "priority_type": "senior",
  "verification_method": "staff_visual"
}
```

**Field Validation:**
- `customer_id` (integer, required): Must exist in database
- `priority_type` (string, required): One of: `senior`, `pwd`, `pregnant`
- `verification_method` (string, optional): One of: `id_scan`, `staff_visual`, `self_declaration`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Verification request submitted. Please wait for staff confirmation.",
  "request_id": 42,
  "pin": "1234",
  "expires_at": "2025-10-24T14:30:00Z"
}
```

**Validation Error (422):**
```json
{
  "message": "The priority type field is invalid.",
  "errors": {
    "priority_type": [
      "Invalid priority type"
    ]
  }
}
```

---

### Check Verification Status

Checks the status of a priority verification request.

**Endpoint:** `GET /api/customer/verification-status/{id}`

**Parameters:**
- `id` (path, integer, required): Verification request ID

**Response (Pending):**
```json
{
  "success": true,
  "status": "pending",
  "message": "Your verification is being reviewed by staff."
}
```

**Response (Verified):**
```json
{
  "success": true,
  "status": "verified",
  "message": "Your priority status has been verified!",
  "verified_at": "2025-10-24T14:25:00Z",
  "verified_by": "John Smith"
}
```

**Response (Rejected):**
```json
{
  "success": true,
  "status": "rejected",
  "message": "Verification request was rejected",
  "reason": "Unable to verify age",
  "rejected_at": "2025-10-24T14:25:00Z"
}
```

---

## Priority Verification (Staff)

### Get Pending Verifications

Returns all pending priority verification requests.

**Endpoint:** `GET /api/verification/pending`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "customer_name": "John Doe",
      "priority_type": "senior",
      "pin": "1234",
      "requested_at": "2025-10-24T14:20:00Z",
      "expires_at": "2025-10-24T14:30:00Z",
      "time_remaining": "8 minutes"
    }
  ],
  "count": 2
}
```

---

### Get Completed Verifications

Returns recently completed verification requests.

**Endpoint:** `GET /api/verification/completed`

**Query Parameters:**
- `limit` (integer, optional): Number of results (default: 20)
- `offset` (integer, optional): Pagination offset

---

### Complete Verification

Marks a verification as approved.

**Endpoint:** `POST /api/verification/complete`

**Headers:**
```http
Content-Type: application/json
X-CSRF-TOKEN: your-token-here
```

**Request Body:**
```json
{
  "verification_id": 1,
  "staff_name": "John Smith",
  "notes": "Valid senior citizen ID presented"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Verification completed successfully.",
  "verified_at": "2025-10-24T14:25:00Z"
}
```

---

### Reject Verification

Rejects a verification request.

**Endpoint:** `POST /api/verification/reject`

**Headers:**
```http
Content-Type: application/json
X-CSRF-TOKEN: your-token-here
```

**Request Body:**
```json
{
  "verification_id": 1,
  "staff_name": "John Smith",
  "reason": "Unable to verify age",
  "notes": "No valid ID presented"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Verification rejected.",
  "rejected_at": "2025-10-24T14:25:00Z"
}
```

---

## Table Management

### Get Table Status

Returns current status of all tables.

**Endpoint:** `GET /api/tables/status`

**Response:**
```json
{
  "success": true,
  "data": {
    "available": 5,
    "occupied": 12,
    "reserved": 2,
    "maintenance": 1,
    "tables": [
      {
        "id": 1,
        "table_number": "A-1",
        "capacity": 4,
        "status": "available",
        "is_vip": false
      },
      {
        "id": 2,
        "table_number": "A-2",
        "capacity": 2,
        "status": "occupied",
        "is_vip": false,
        "occupied_at": "2025-10-24T13:30:00Z",
        "estimated_departure": "2025-10-24T14:30:00Z"
      }
    ]
  }
}
```

---

### Get Table Suggestions

Returns smart table suggestions based on party size.

**Endpoint:** `GET /api/tables/suggestions`

**Query Parameters:**
- `party_size` (integer, required): Number of people

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "table_id": 5,
      "table_number": "B-2",
      "capacity": 4,
      "match_score": 100,
      "reason": "Perfect match for party size"
    },
    {
      "table_id": 8,
      "table_number": "C-1",
      "capacity": 6,
      "match_score": 75,
      "reason": "Can accommodate with extra space"
    }
  ]
}
```

---

### Reserve Table

Reserves a specific table for a customer.

**Endpoint:** `POST /api/tables/{tableId}/reserve`

**Headers:**
```http
Content-Type: application/json
X-CSRF-TOKEN: your-token-here
```

**Parameters:**
- `tableId` (path, integer, required): Table ID

**Request Body:**
```json
{
  "customer_id": 1,
  "party_size": 4
}
```

**Response:**
```json
{
  "success": true,
  "message": "Table reserved successfully",
  "table_number": "B-2",
  "reserved_at": "2025-10-24T14:30:00Z"
}
```

---

## Settings

### Get Public Settings

Returns public system settings.

**Endpoint:** `GET /api/settings/public`

**Response:**
```json
{
  "success": true,
  "data": {
    "restaurant_name": "Café Gervacio's",
    "party_size_min": 1,
    "party_size_max": 20,
    "enable_priority": true,
    "average_wait_time": 25,
    "contact_number": "(082) 123-4567"
  }
}
```

---

### Check If Store Is Open

Checks if restaurant is currently open.

**Endpoint:** `GET /api/settings/is-open`

**Response (Open):**
```json
{
  "success": true,
  "is_open": true,
  "current_time": "14:30:00",
  "opens_at": "09:00:00",
  "closes_at": "21:00:00"
}
```

**Response (Closed):**
```json
{
  "success": true,
  "is_open": false,
  "current_time": "22:30:00",
  "message": "We are currently closed. Please come back during operating hours.",
  "next_opening": "2025-10-25 09:00:00"
}
```

---

### Get Store Hours

Returns operating hours for all days.

**Endpoint:** `GET /api/settings/store-hours`

**Response:**
```json
{
  "success": true,
  "data": {
    "monday": {
      "open": "09:00:00",
      "close": "21:00:00",
      "is_closed": false
    },
    "tuesday": {
      "open": "09:00:00",
      "close": "21:00:00",
      "is_closed": false
    },
    "sunday": {
      "is_closed": true
    }
  }
}
```

---

### Get Today's Hours

Returns store hours for today only.

**Endpoint:** `GET /api/settings/today-hours`

**Response:**
```json
{
  "success": true,
  "day": "monday",
  "open": "09:00:00",
  "close": "21:00:00",
  "is_closed": false,
  "is_open_now": true
}
```

---

### Check Registration Block

Checks if new registrations should be blocked.

**Endpoint:** `GET /api/settings/block-registration`

**Response:**
```json
{
  "success": true,
  "blocked": false,
  "reason": null
}
```

**Response (Blocked):**
```json
{
  "success": true,
  "blocked": true,
  "reason": "Restaurant is at full capacity"
}
```

---

### Update Settings (Admin)

Updates system settings. **Requires admin authentication.**

**Endpoint:** `POST /api/settings/update`

**Headers:**
```http
Content-Type: application/json
X-CSRF-TOKEN: your-token-here
```

**Request Body:**
```json
{
  "party_size_max": 25,
  "average_wait_time": 30
}
```

---

## Analytics

### Get Today's Analytics

Returns analytics for the current day.

**Endpoint:** `GET /api/analytics/today`

**Response:**
```json
{
  "success": true,
  "data": {
    "date": "2025-10-24",
    "total_customers": 145,
    "total_priority": 32,
    "average_wait_time": 28,
    "peak_hour": "13:00",
    "priority_breakdown": {
      "senior": 18,
      "pwd": 8,
      "pregnant": 6
    },
    "hourly_distribution": [
      {"hour": "09:00", "count": 8},
      {"hour": "10:00", "count": 12},
      {"hour": "11:00", "count": 15},
      {"hour": "12:00", "count": 22},
      {"hour": "13:00", "count": 28}
    ]
  }
}
```

---

### Get Analytics by Date

Returns analytics for a specific date.

**Endpoint:** `GET /api/analytics/date/{date}`

**Parameters:**
- `date` (path, string, required): Date in YYYY-MM-DD format

**Example:** `GET /api/analytics/date/2025-10-23`

---

### Get Export History

Returns list of previously exported reports.

**Endpoint:** `GET /api/analytics/export-history`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "date": "2025-10-24",
      "filename": "analytics_2025-10-24.csv",
      "exported_at": "2025-10-24T21:00:00Z",
      "file_size": "125 KB"
    }
  ]
}
```

---

## Webhooks (Future)

Webhook support for real-time notifications is planned for a future release.

**Planned Events:**
- `customer.registered`
- `customer.called`
- `customer.seated`
- `verification.completed`
- `table.available`

---

## Support

For API support or to report issues:

- **Email:** support@cafegervacios.com
- **Phone:** (082) 123-4567
- **GitHub Issues:** https://github.com/Cevastien/SeatManagement/issues

---

## Changelog

### Version 1.0.0 (2025-10-24)
- Initial API release
- Queue management endpoints
- Customer management endpoints
- Priority verification system
- Table management
- Settings configuration
- Analytics reporting
- Rate limiting implementation
- CSRF protection

---

**Last Updated:** October 24, 2025
**API Version:** 1.0.0
**Documentation Version:** 1.0.0
