# Call Records API Documentation

## Overview
The Call Records API provides endpoints for managing telecall activities in the system. All endpoints (except the test endpoint) require authentication using Laravel Sanctum tokens.

## Base URL
```
/api
```

## Authentication
All API endpoints (except `/test`) require authentication using Bearer tokens. Include the token in the Authorization header:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

## Endpoints

### Test Endpoint
**GET** `/test`

Test endpoint to verify API is working.

**Response:**
```json
{
  "success": true,
  "message": "API is working!",
  "timestamp": "2025-11-14T21:47:09.665484Z"
}
```

### Call Records

#### Create Call Record
**POST** `/call-records`

Create a new call record.

**Request Body:**
```json
{
  "user_id": 1,
  "pengundi_ic": "123456789012",
  "phone_number": "0123456789",
  "kod_cula": "VA",
  "notes": "Optional call notes"
}
```

**Validation Rules:**
- `user_id`: Required, must be valid user ID
- `pengundi_ic`: Required, string, max 12 characters
- `phone_number`: Required, string, max 20 characters
- `kod_cula`: Optional, string, max 2 characters, must be one of: VA, VB, VC, VD, VN, VS, VT, VR, VW, VX, VY, VZ
- `notes`: Optional, string, max 1000 characters

**Success Response (201):**
```json
{
  "success": true,
  "message": "Call record created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "pengundi_ic": "123456789012",
    "phone_number": "0123456789",
    "kod_cula": "VA",
    "notes": "Test call via API",
    "called_at": "2025-11-14T21:47:10.000000Z",
    "created_at": "2025-11-14T21:47:10.000000Z",
    "updated_at": "2025-11-14T21:47:10.000000Z",
    "user_name": "Test User"
  }
}
```

#### List Call Records
**GET** `/call-records`

Get all call records (paginated).

**Query Parameters:**
- `pengundi_ic`: Filter by pengundi IC
- `user_id`: Filter by user ID
- `date_from`: Filter from date (YYYY-MM-DD)
- `date_to`: Filter to date (YYYY-MM-DD)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "total": 100,
    "per_page": 50
  }
}
```

#### Get Call Record
**GET** `/call-records/{id}`

Get a specific call record.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "pengundi_ic": "123456789012",
    "phone_number": "0123456789",
    "kod_cula": "VA",
    "notes": "Test call via API",
    "called_at": "2025-11-14T21:47:10.000000Z",
    "created_at": "2025-11-14T21:47:10.000000Z",
    "updated_at": "2025-11-14T21:47:10.000000Z",
    "user_name": "Test User"
  }
}
```

#### Update Call Record
**PUT/PATCH** `/call-records/{id}`

Update a call record (only kod_cula and notes can be updated).

**Request Body:**
```json
{
  "kod_cula": "VB",
  "notes": "Updated call notes"
}
```

#### Delete Call Record
**DELETE** `/call-records/{id}`

Delete a call record.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Call record deleted successfully"
}
```

### Additional Endpoints

#### Get Call Records by Pengundi IC
**GET** `/call-records/pengundi/{pengundi_ic}`

Get all call records for a specific pengundi.

#### Get Call Records by User
**GET** `/users/{user_id}/call-records`

Get all call records created by a specific user.

#### Call Statistics
**GET** `/call-records-statistics`

Get call statistics and analytics.

**Query Parameters:**
- `date_from`: Filter from date (YYYY-MM-DD)
- `date_to`: Filter to date (YYYY-MM-DD)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total_calls": 150,
    "calls_by_user": [
      {
        "name": "John Doe",
        "total_calls": 50
      }
    ],
    "calls_by_cula": [
      {
        "kod_cula": "VA",
        "total_calls": 75
      }
    ],
    "daily_calls": [
      {
        "date": "2025-11-14",
        "total_calls": 25
      }
    ]
  }
}
```

## Error Handling

All endpoints return consistent error responses:

### Validation Error (422):
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Not Found Error (404):
```json
{
  "success": false,
  "message": "Call record not found"
}
```

### Server Error (500):
```json
{
  "success": false,
  "message": "Failed to create call record",
  "error": "Detailed error message"
}
```

## Usage Examples

### Creating a Token (for authentication)
First, create a token for a user:

```php
$user = \App\Models\User::find(1);
$token = $user->createToken('api-token')->plainTextToken;
```

### cURL Examples

#### Create Call Record:
```bash
curl -X POST http://localhost/api/call-records \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 1,
    "pengundi_ic": "123456789012",
    "phone_number": "0123456789",
    "kod_cula": "VA",
    "notes": "Test call"
  }'
```

#### Get Call Records:
```bash
curl -X GET "http://localhost/api/call-records?pengundi_ic=123456789012" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

#### Get Statistics:
```bash
curl -X GET "http://localhost/api/call-records-statistics?date_from=2025-11-01&date_to=2025-11-30" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```