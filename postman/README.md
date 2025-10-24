# Postman API Collection for Seat Management System

This folder contains the Postman collection and environment files for testing and documenting the Café Gervacio's Seat Management API.

## Files

- `SeatManagement-API.postman_collection.json` - Complete API collection with all endpoints
- `SeatManagement-Local.postman_environment.json` - Local development environment
- `SeatManagement-Production.postman_environment.json` - Production environment

## Quick Start

### 1. Import the Collection

1. Open Postman
2. Click **Import** in the top left
3. Drag and drop or select `SeatManagement-API.postman_collection.json`
4. Click **Import**

### 2. Import Environment

1. Click **Import** again
2. Select `SeatManagement-Local.postman_environment.json` (for local testing)
3. Click **Import**
4. Optionally import `SeatManagement-Production.postman_environment.json`

### 3. Select Environment

1. In the top right corner of Postman, click the environment dropdown
2. Select **SeatManagement - Local** (or Production)

### 4. Get CSRF Token

Before making any POST requests:

1. Open the collection
2. Navigate to **Utility** → **Get CSRF Token**
3. Click **Send**
4. The CSRF token will be automatically saved to the environment

### 5. Start Testing

You're now ready to test all API endpoints! The collection includes:

- ✅ Pre-configured requests with example data
- ✅ Response examples
- ✅ Automatic CSRF token extraction
- ✅ Environment variables
- ✅ Organized folders by feature

## Collection Structure

```
SeatManagement API Collection
├── Utility
│   └── Get CSRF Token
├── Queue Management
│   ├── Get Queue Statistics
│   ├── Get Queue Summary
│   ├── Get Queue Update
│   └── Update Wait Times
├── Customer Management
│   ├── Get Customer Current Wait Time
│   ├── Get Customer Position
│   ├── Request Priority Verification
│   └── Check Verification Status
├── Priority Verification (Staff)
│   ├── Get Pending Verifications
│   ├── Get Completed Verifications
│   ├── Complete Verification
│   └── Reject Verification
├── Table Management
│   ├── Get Table Status
│   ├── Get Table Suggestions
│   └── Reserve Table
├── Settings
│   ├── Get Public Settings
│   ├── Check If Store Is Open
│   ├── Get Store Hours
│   ├── Get Today's Hours
│   ├── Check Registration Block
│   └── Update Settings (Admin)
└── Analytics
    ├── Get Today's Analytics
    ├── Get Analytics by Date
    └── Get Export History
```

## Environment Variables

### Local Environment

| Variable | Default Value | Description |
|----------|---------------|-------------|
| `base_url` | `http://localhost:8000` | API base URL |
| `csrf_token` | (auto-filled) | CSRF token for authentication |
| `customer_id` | `1` | Sample customer ID |
| `queue_number` | `001` | Sample queue number |
| `table_id` | `1` | Sample table ID |
| `verification_id` | `1` | Sample verification ID |

### Updating Variables

You can update variables by:
1. Clicking the eye icon (👁️) in the top right
2. Click **Edit** next to your environment
3. Modify values as needed

Or update directly in requests using `{{variable_name}}`

## Example Requests

### Example 1: Get Queue Statistics

```bash
GET http://localhost:8000/api/queue/stats
```

Response:
```json
{
  "success": true,
  "data": {
    "total_waiting": 12,
    "total_called": 3,
    "total_seated": 5,
    "average_wait_time": 25
  }
}
```

### Example 2: Request Priority Verification

```bash
POST http://localhost:8000/api/customer/request-verification
Content-Type: application/json
X-CSRF-TOKEN: your-token-here

{
  "customer_id": 1,
  "priority_type": "senior",
  "verification_method": "staff_visual"
}
```

Response:
```json
{
  "success": true,
  "message": "Verification request submitted",
  "request_id": 42,
  "pin": "1234"
}
```

## Testing Workflow

### Recommended Testing Order:

1. **Setup**
   - Get CSRF Token ✅

2. **Queue Testing**
   - Get Queue Statistics
   - Get Queue Summary

3. **Customer Flow**
   - Get Customer Current Wait Time
   - Get Customer Position
   - Request Priority Verification
   - Check Verification Status

4. **Staff Operations**
   - Get Pending Verifications
   - Complete/Reject Verification

5. **Table Management**
   - Get Table Status
   - Get Table Suggestions
   - Reserve Table

6. **Configuration**
   - Get Public Settings
   - Check If Store Is Open
   - Get Store Hours

7. **Analytics**
   - Get Today's Analytics
   - Get Analytics by Date

## Rate Limiting

**Limit:** 120 requests per minute

If you exceed the rate limit, you'll receive:
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "retry_after": 45
}
```

Wait for the `retry_after` seconds before making more requests.

## Authentication

All POST requests require a CSRF token:

1. Run **Utility → Get CSRF Token**
2. Token is automatically saved to `{{csrf_token}}`
3. Token is included in all POST request headers

**Manual Token Usage:**
```http
POST /api/customer/request-verification
X-CSRF-TOKEN: {{csrf_token}}
Content-Type: application/json
```

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { /* response data */ },
  "message": "Optional success message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

## Tips & Tricks

### 1. Using Variables in Requests

Replace hardcoded values with variables:
```
Before: GET /api/customer/1/current-wait
After:  GET /api/customer/{{customer_id}}/current-wait
```

### 2. Saving Responses to Variables

Add a test script to save response data:
```javascript
pm.test("Save customer ID", function() {
    var jsonData = pm.response.json();
    pm.environment.set("customer_id", jsonData.data.id);
});
```

### 3. Running the Entire Collection

Use Postman's **Collection Runner**:
1. Click on the collection name
2. Click **Run**
3. Select environment
4. Click **Run Collection**

### 4. Generating Code Snippets

Postman can generate code in multiple languages:
1. Click the **Code** icon (<>) in the request
2. Select your language (JavaScript, Python, PHP, etc.)
3. Copy the generated code

### 5. Organizing Requests

Create folders for different testing scenarios:
- Happy Path Testing
- Error Handling
- Edge Cases
- Performance Testing

## Troubleshooting

### Issue: "CSRF Token Mismatch"

**Solution:**
1. Run **Get CSRF Token** again
2. Ensure token is saved to environment
3. Check token is included in request headers

### Issue: "Rate Limit Exceeded"

**Solution:**
1. Wait for the retry period
2. Reduce request frequency
3. Use Collection Runner with delays

### Issue: "404 Not Found"

**Solution:**
1. Verify `base_url` in environment
2. Check endpoint path spelling
3. Ensure Laravel server is running

### Issue: "Connection Refused"

**Solution:**
1. Start your Laravel server: `php artisan serve`
2. Check `base_url` matches server address
3. Verify firewall settings

## Advanced Features

### Pre-request Scripts

Add logic before requests execute:
```javascript
// Generate random queue number
pm.environment.set("queue_number",
    String(Math.floor(Math.random() * 1000)).padStart(3, '0')
);
```

### Test Scripts

Validate responses automatically:
```javascript
pm.test("Status code is 200", function() {
    pm.response.to.have.status(200);
});

pm.test("Response has data", function() {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});
```

### Dynamic Variables

Postman provides built-in dynamic variables:
- `{{$timestamp}}` - Current Unix timestamp
- `{{$randomInt}}` - Random integer
- `{{$guid}}` - Random GUID
- `{{$randomFirstName}}` - Random first name

## Automated Testing

### Running Tests in CI/CD

Use Newman (Postman's CLI):

```bash
# Install Newman
npm install -g newman

# Run collection
newman run SeatManagement-API.postman_collection.json \
  -e SeatManagement-Local.postman_environment.json \
  --reporters cli,json

# With HTML report
newman run SeatManagement-API.postman_collection.json \
  -e SeatManagement-Local.postman_environment.json \
  --reporters cli,htmlextra \
  --reporter-htmlextra-export report.html
```

### GitHub Actions Example

```yaml
name: API Tests
on: [push]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Install Newman
        run: npm install -g newman
      - name: Run API Tests
        run: newman run postman/SeatManagement-API.postman_collection.json
```

## Documentation

For detailed API documentation, see:
- **API_DOCUMENTATION.md** - Complete API reference
- **ARCHITECTURE_IMPROVEMENTS.md** - Technical architecture
- **DESIGN_GUIDELINES.md** - UI/UX guidelines

## Contributing

When adding new endpoints:

1. Add the endpoint to the Postman collection
2. Include request examples
3. Include response examples
4. Add to appropriate folder
5. Update this README
6. Update API_DOCUMENTATION.md

## Support

For questions or issues:
- **GitHub Issues:** https://github.com/Cevastien/SeatManagement/issues
- **Email:** support@cafegervacios.com
- **Phone:** (082) 123-4567

## Version History

### v1.0.0 (2025-10-24)
- ✅ Initial release
- ✅ 35+ documented endpoints
- ✅ Complete request/response examples
- ✅ Local and production environments
- ✅ Automated CSRF token handling
- ✅ Pre-configured variables
- ✅ Rate limiting documentation

---

**Last Updated:** October 24, 2025
**Collection Version:** 1.0.0
**Maintained by:** Development Team
