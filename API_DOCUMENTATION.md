# GymPaddy Dashboard - Backend API Documentation

## Overview
This document outlines all the API endpoints required for the GymPaddy Admin Dashboard. The dashboard manages users, social content, marketplace listings, gym facilities, transactions, subscriptions, verifications, ads, analytics, notifications, and support tickets.

## Base URL
```
http://localhost:5000/api
```

## Authentication
All endpoints (except login/register) require Bearer token authentication:
```
Authorization: Bearer <token>
```

---

## 1. Authentication Endpoints

### POST `/auth/login`
Admin login
- **Request Body:**
```json
{
  "email": "admin@gympaddy.com",
  "password": "password123"
}
```
- **Response:**
```json
{
  "success": true,
  "data": {
    "token": "jwt_token_here",
    "user": {
      "id": "1",
      "fullName": "Admin User",
      "email": "admin@gympaddy.com",
      "role": "admin"
    }
  }
}
```

### POST `/auth/logout`
Admin logout
- **Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### POST `/auth/refresh`
Refresh authentication token
- **Response:**
```json
{
  "success": true,
  "data": {
    "token": "new_jwt_token_here"
  }
}
```

### POST `/auth/forgot-password`
Request password reset
- **Request Body:**
```json
{
  "email": "admin@gympaddy.com"
}
```

### POST `/auth/reset-password`
Reset password with token
- **Request Body:**
```json
{
  "token": "reset_token",
  "newPassword": "newPassword123"
}
```

---

## 2. Dashboard Endpoints

### GET `/dashboard/stats`
Get overall dashboard statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalUsers": 15420,
    "totalRevenue": 125000.50,
    "totalTransactions": 3420,
    "activeSubscriptions": 1250,
    "newUsersToday": 45,
    "revenueToday": 2500.00
  }
}
```

### GET `/dashboard/latest-users`
Get latest registered users (limit 10)
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "fullName": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "phoneNumber": "+1234567890",
      "age": 25,
      "lastLogin": "2026-02-19T18:30:00Z"
    }
  ]
}
```

### GET `/dashboard/latest-posts`
Get latest social posts (limit 15)
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "post_1",
      "description": "New workout completed!",
      "time": "2 hours ago"
    }
  ]
}
```

### GET `/dashboard/user-statistics`
Get user growth statistics for charts
- **Query Parameters:** `?period=7d|30d|90d|1y`
- **Response:**
```json
{
  "success": true,
  "data": {
    "labels": ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    "datasets": [
      {
        "label": "New Users",
        "data": [120, 150, 180, 200, 250, 300]
      }
    ]
  }
}
```

---

## 3. User Management Endpoints

### GET `/users`
Get all users with filtering
- **Query Parameters:**
  - `status` (optional): `online|offline|all`
  - `search` (optional): Search by name, username, or email
  - `page` (optional): Page number (default: 1)
  - `limit` (optional): Items per page (default: 20)
- **Response:**
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 1,
        "fullName": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "phoneNumber": "+1234567890",
        "status": "online",
        "lastLogin": "2026-02-19T18:30:00Z",
        "profile_picture": "https://example.com/image.jpg",
        "gender": "Male",
        "age": 25,
        "createdAt": "2026-01-15T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 10,
      "totalItems": 200,
      "itemsPerPage": 20
    }
  }
}
```

### GET `/users/:id`
Get user by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "fullName": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "phoneNumber": "+1234567890",
    "status": "online",
    "lastLogin": "2026-02-19T18:30:00Z",
    "profile_picture": "https://example.com/image.jpg",
    "gender": "Male",
    "age": 25,
    "bio": "Fitness enthusiast",
    "location": "New York, USA",
    "createdAt": "2026-01-15T10:00:00Z"
  }
}
```

### GET `/users/username/:username`
Get user by username
- **Response:** Same as GET `/users/:id`

### POST `/users`
Create new user
- **Request Body (multipart/form-data):**
```json
{
  "fullName": "Jane Smith",
  "username": "janesmith",
  "email": "jane@example.com",
  "phoneNumber": "+1234567891",
  "gender": "Female",
  "age": 28,
  "password": "password123",
  "profile_picture": "<File>"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 2,
    "username": "janesmith"
  }
}
```

### PUT `/users/:id`
Update user information
- **Request Body:**
```json
{
  "fullName": "Jane Smith Updated",
  "phoneNumber": "+1234567892",
  "age": 29
}
```
- **Response:**
```json
{
  "success": true,
  "message": "User updated successfully"
}
```

### DELETE `/users/:id`
Delete user
- **Response:**
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

### POST `/users/:id/ban`
Ban user
- **Request Body:**
```json
{
  "reason": "Violation of community guidelines",
  "duration": 7
}
```
- **Response:**
```json
{
  "success": true,
  "message": "User banned successfully"
}
```

### POST `/users/:id/unban`
Unban user
- **Response:**
```json
{
  "success": true,
  "message": "User unbanned successfully"
}
```

### GET `/users/stats`
Get user statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalUsers": 15420,
    "activeUsers": 8500,
    "newUsersToday": 45,
    "onlineUsers": 1200,
    "bannedUsers": 120
  }
}
```

---

## 4. Social Management Endpoints

### GET `/social/posts`
Get all social posts
- **Query Parameters:**
  - `type` (optional): `all|post|status|live`
  - `userId` (optional): Filter by user ID
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "posts": [
      {
        "id": "post_1",
        "userId": "1",
        "userName": "John Doe",
        "userAvatar": "https://example.com/avatar.jpg",
        "content": "Just finished my workout!",
        "images": ["https://example.com/post1.jpg"],
        "likes": 150,
        "comments": 25,
        "shares": 10,
        "createdAt": "2026-02-19T15:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 50,
      "totalItems": 1000
    }
  }
}
```

### GET `/social/posts/:id`
Get post by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "post_1",
    "userId": "1",
    "userName": "John Doe",
    "content": "Just finished my workout!",
    "images": ["https://example.com/post1.jpg"],
    "likes": 150,
    "comments": 25,
    "shares": 10,
    "createdAt": "2026-02-19T15:00:00Z"
  }
}
```

### GET `/social/posts/user/:userId`
Get posts by specific user
- **Response:** Same structure as GET `/social/posts`

### DELETE `/social/posts/:id`
Delete a post
- **Response:**
```json
{
  "success": true,
  "message": "Post deleted successfully"
}
```

### GET `/social/statuses`
Get all status updates
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "status_1",
      "userId": "1",
      "userName": "John Doe",
      "content": "Feeling motivated!",
      "createdAt": "2026-02-19T14:00:00Z",
      "expiresAt": "2026-02-20T14:00:00Z"
    }
  ]
}
```

### DELETE `/social/statuses/:id`
Delete a status
- **Response:**
```json
{
  "success": true,
  "message": "Status deleted successfully"
}
```

### GET `/social/live`
Get all live streams
- **Query Parameters:** `status=active|ended|all`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "live_1",
      "userId": "1",
      "userName": "John Doe",
      "title": "Morning Workout Session",
      "viewers": 250,
      "status": "active",
      "startedAt": "2026-02-19T18:00:00Z"
    }
  ]
}
```

### POST `/social/live/:id/end`
End a live stream
- **Response:**
```json
{
  "success": true,
  "message": "Live stream ended successfully"
}
```

### GET `/social/stats`
Get social statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalPosts": 5420,
    "totalStatuses": 1250,
    "totalLiveStreams": 320,
    "activeLiveStreams": 15,
    "postsToday": 120
  }
}
```

---

## 5. Market Management Endpoints

### GET `/market/listings`
Get all marketplace listings
- **Query Parameters:**
  - `status` (optional): `active|sold|pending|all`
  - `boosted` (optional): `true|false|all`
  - `category` (optional): Category filter
  - `search` (optional): Search term
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "listings": [
      {
        "id": "listing_1",
        "name": "Gym Equipment Set",
        "description": "Complete home gym equipment",
        "price": 500.00,
        "category": "Equipment",
        "status": "active",
        "boostedStatus": "boosted",
        "images": ["https://example.com/product1.jpg"],
        "sellerId": "1",
        "sellerName": "John Doe",
        "views": 250,
        "createdAt": "2026-02-15T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 20,
      "totalItems": 400
    }
  }
}
```

### GET `/market/listings/:id`
Get listing by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "listing_1",
    "name": "Gym Equipment Set",
    "description": "Complete home gym equipment",
    "price": 500.00,
    "category": "Equipment",
    "status": "active",
    "boostedStatus": "boosted",
    "images": ["https://example.com/product1.jpg"],
    "sellerId": "1",
    "sellerName": "John Doe",
    "sellerEmail": "john@example.com",
    "views": 250,
    "createdAt": "2026-02-15T10:00:00Z"
  }
}
```

### GET `/market/listings/user/:userId`
Get listings by user
- **Response:** Same structure as GET `/market/listings`

### POST `/market/listings`
Create new listing (multipart/form-data)
- **Request Body:**
```json
{
  "name": "Dumbbells Set",
  "description": "20kg dumbbells",
  "price": 150.00,
  "category": "Equipment",
  "images": ["<File>", "<File>"]
}
```

### PUT `/market/listings/:id`
Update listing
- **Request Body:**
```json
{
  "name": "Updated Name",
  "price": 175.00
}
```

### DELETE `/market/listings/:id`
Delete listing
- **Response:**
```json
{
  "success": true,
  "message": "Listing deleted successfully"
}
```

### POST `/market/listings/:id/boost`
Boost a listing
- **Request Body:**
```json
{
  "duration": 7
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Listing boosted successfully"
}
```

### GET `/market/stats`
Get market statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalListings": 1250,
    "activeListings": 850,
    "boostedListings": 120,
    "totalRevenue": 45000.00,
    "soldToday": 15
  }
}
```

---

## 6. Connect Management Endpoints

### GET `/connect/users`
Get all Connect users
- **Query Parameters:**
  - `subscription` (optional): `true|false|all`
  - `videoVerification` (optional): `verified|pending|rejected|all`
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": "1",
        "fullName": "John Doe",
        "username": "johndoe",
        "age": 25,
        "gender": "Male",
        "relationshipStatus": "Single",
        "interestedIn": "Gym Buddy",
        "subscription": true,
        "videoVerification": "verified",
        "images": ["https://example.com/img1.jpg"],
        "createdAt": "2026-01-15T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 15,
      "totalItems": 300
    }
  }
}
```

### GET `/connect/users/:id`
Get Connect user details
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "1",
    "fullName": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "age": 25,
    "gender": "Male",
    "relationshipStatus": "Single",
    "interestedIn": "Gym Buddy",
    "distance": "20-35",
    "subscription": true,
    "subscriptionStatus": "Subscribed",
    "videoVerification": "verified",
    "interests": 4,
    "dateRegistered": "2026-01-15T10:00:00Z",
    "images": ["https://example.com/img1.jpg"],
    "videos": ["https://example.com/video1.mp4"]
  }
}
```

### GET `/connect/matches/:userId`
Get user's matches
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "match_1",
      "userId1": "1",
      "userId2": "2",
      "user2Name": "Jane Smith",
      "user2Avatar": "https://example.com/avatar2.jpg",
      "matchedAt": "2026-02-18T15:00:00Z",
      "status": "active"
    }
  ]
}
```

### GET `/connect/stats`
Get Connect statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalUsers": 5420,
    "subscribedUsers": 1250,
    "verifiedUsers": 3200,
    "totalMatches": 8500
  }
}
```

---

## 7. Gym Management Endpoints

### GET `/gym/gyms`
Get all gyms
- **Query Parameters:** `page`, `limit`, `search`
- **Response:**
```json
{
  "success": true,
  "data": {
    "gyms": [
      {
        "id": "gym_1",
        "name": "FitZone Gym",
        "location": "New York, USA",
        "rating": 4.5,
        "members": 250,
        "facilities": ["Pool", "Sauna", "Cardio"],
        "createdAt": "2026-01-10T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 5,
      "totalItems": 100
    }
  }
}
```

### GET `/gym/gyms/:id`
Get gym by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "gym_1",
    "name": "FitZone Gym",
    "description": "Premium fitness center",
    "location": "New York, USA",
    "address": "123 Main St",
    "phone": "+1234567890",
    "email": "info@fitzone.com",
    "rating": 4.5,
    "members": 250,
    "facilities": ["Pool", "Sauna", "Cardio"],
    "images": ["https://example.com/gym1.jpg"],
    "createdAt": "2026-01-10T10:00:00Z"
  }
}
```

### POST `/gym/gyms`
Create new gym
- **Request Body:**
```json
{
  "name": "PowerHouse Gym",
  "description": "State-of-the-art facility",
  "location": "Los Angeles, USA",
  "address": "456 Fitness Ave",
  "phone": "+1234567891",
  "email": "info@powerhouse.com",
  "facilities": ["Pool", "Sauna"]
}
```

### PUT `/gym/gyms/:id`
Update gym
- **Request Body:** Same as POST

### DELETE `/gym/gyms/:id`
Delete gym
- **Response:**
```json
{
  "success": true,
  "message": "Gym deleted successfully"
}
```

### GET `/gym/stats`
Get gym statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalGyms": 120,
    "totalMembers": 15000,
    "averageRating": 4.3
  }
}
```

---

## 8. Transaction Endpoints

### GET `/transactions`
Get all transactions
- **Query Parameters:**
  - `type` (optional): `topup|withdrawal|all`
  - `status` (optional): `pending|completed|failed|all`
  - `search` (optional): Search by transaction ID
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": "txn_123456",
        "userId": "1",
        "userName": "John Doe",
        "amount": 100.00,
        "type": "topup",
        "status": "completed",
        "paymentMethod": "Credit Card",
        "createdAt": "2026-02-19T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 30,
      "totalItems": 600
    }
  }
}
```

### GET `/transactions/:id`
Get transaction by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "txn_123456",
    "userId": "1",
    "userName": "John Doe",
    "userEmail": "john@example.com",
    "amount": 100.00,
    "type": "topup",
    "status": "completed",
    "paymentMethod": "Credit Card",
    "description": "Wallet top-up",
    "createdAt": "2026-02-19T10:00:00Z"
  }
}
```

### GET `/transactions/user/:userId`
Get user's transactions
- **Response:** Same structure as GET `/transactions`

### GET `/transactions/stats`
Get transaction statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalTransactions": 3420,
    "totalRevenue": 125000.50,
    "pendingTransactions": 45,
    "completedTransactions": 3200,
    "failedTransactions": 175,
    "revenueToday": 2500.00,
    "revenueThisMonth": 45000.00
  }
}
```

---

## 9. Subscription Endpoints

### GET `/subscriptions`
Get all subscriptions
- **Query Parameters:**
  - `status` (optional): `active|expired|cancelled|all`
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "subscriptions": [
      {
        "id": "sub_1",
        "userId": "1",
        "userName": "John Doe",
        "plan": "Premium",
        "amount": 29.99,
        "status": "active",
        "startDate": "2026-02-01T00:00:00Z",
        "endDate": "2026-03-01T00:00:00Z",
        "createdAt": "2026-02-01T00:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 10,
      "totalItems": 200
    }
  }
}
```

### GET `/subscriptions/:id`
Get subscription by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "sub_1",
    "userId": "1",
    "userName": "John Doe",
    "userEmail": "john@example.com",
    "plan": "Premium",
    "amount": 29.99,
    "status": "active",
    "startDate": "2026-02-01T00:00:00Z",
    "endDate": "2026-03-01T00:00:00Z",
    "autoRenew": true,
    "createdAt": "2026-02-01T00:00:00Z"
  }
}
```

### GET `/subscriptions/user/:userId`
Get user's subscription
- **Response:** Same as GET `/subscriptions/:id`

### POST `/subscriptions`
Create subscription
- **Request Body:**
```json
{
  "userId": "1",
  "plan": "Premium",
  "duration": 30
}
```

### PUT `/subscriptions/:id`
Update subscription
- **Request Body:**
```json
{
  "autoRenew": false
}
```

### POST `/subscriptions/:id/cancel`
Cancel subscription
- **Response:**
```json
{
  "success": true,
  "message": "Subscription cancelled successfully"
}
```

### GET `/subscriptions/stats`
Get subscription statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalSubscriptions": 1250,
    "activeSubscriptions": 980,
    "expiredSubscriptions": 200,
    "cancelledSubscriptions": 70,
    "monthlyRevenue": 29000.00
  }
}
```

---

## 10. Verification Endpoints

### GET `/verifications`
Get all verification requests
- **Query Parameters:**
  - `status` (optional): `pending|approved|rejected|all`
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "verifications": [
      {
        "id": "verify_1",
        "userId": "1",
        "userName": "John Doe",
        "businessName": "FitZone Gym",
        "category": "Gym",
        "status": "pending",
        "documents": ["https://example.com/doc1.pdf"],
        "createdAt": "2026-02-18T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 5,
      "totalItems": 100
    }
  }
}
```

### GET `/verifications/:id`
Get verification by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "verify_1",
    "userId": "1",
    "userName": "John Doe",
    "userEmail": "john@example.com",
    "businessName": "FitZone Gym",
    "category": "Gym",
    "status": "pending",
    "documents": ["https://example.com/doc1.pdf"],
    "notes": "",
    "createdAt": "2026-02-18T10:00:00Z"
  }
}
```

### POST `/verifications/:id/approve`
Approve verification
- **Request Body:**
```json
{
  "notes": "All documents verified"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Verification approved successfully"
}
```

### POST `/verifications/:id/reject`
Reject verification
- **Request Body:**
```json
{
  "reason": "Incomplete documentation"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Verification rejected successfully"
}
```

### GET `/verifications/stats`
Get verification statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalVerifications": 450,
    "pendingVerifications": 45,
    "approvedVerifications": 350,
    "rejectedVerifications": 55
  }
}
```

---

## 11. Ads Management Endpoints

### GET `/ads`
Get all ads
- **Query Parameters:**
  - `status` (optional): `active|paused|ended|all`
  - `type` (optional): Ad type filter
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "ads": [
      {
        "id": "ad_1",
        "name": "Summer Fitness Campaign",
        "type": "Banner",
        "status": "active",
        "impressions": 15000,
        "clicks": 450,
        "budget": 500.00,
        "spent": 250.00,
        "startDate": "2026-02-01T00:00:00Z",
        "endDate": "2026-02-28T23:59:59Z",
        "createdAt": "2026-01-25T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 8,
      "totalItems": 160
    }
  }
}
```

### GET `/ads/:id`
Get ad by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "ad_1",
    "name": "Summer Fitness Campaign",
    "description": "Promote summer fitness programs",
    "type": "Banner",
    "status": "active",
    "impressions": 15000,
    "clicks": 450,
    "conversions": 25,
    "budget": 500.00,
    "spent": 250.00,
    "targetAudience": "Fitness enthusiasts",
    "startDate": "2026-02-01T00:00:00Z",
    "endDate": "2026-02-28T23:59:59Z",
    "createdAt": "2026-01-25T10:00:00Z"
  }
}
```

### POST `/ads`
Create new ad
- **Request Body:**
```json
{
  "name": "New Campaign",
  "description": "Campaign description",
  "type": "Banner",
  "budget": 1000.00,
  "targetAudience": "All users",
  "startDate": "2026-03-01T00:00:00Z",
  "endDate": "2026-03-31T23:59:59Z"
}
```

### PUT `/ads/:id`
Update ad
- **Request Body:**
```json
{
  "budget": 1500.00,
  "endDate": "2026-04-15T23:59:59Z"
}
```

### DELETE `/ads/:id`
Delete ad
- **Response:**
```json
{
  "success": true,
  "message": "Ad deleted successfully"
}
```

### POST `/ads/:id/pause`
Pause ad
- **Response:**
```json
{
  "success": true,
  "message": "Ad paused successfully"
}
```

### POST `/ads/:id/resume`
Resume ad
- **Response:**
```json
{
  "success": true,
  "message": "Ad resumed successfully"
}
```

### GET `/ads/stats`
Get ads statistics
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalAds": 160,
    "activeAds": 45,
    "totalImpressions": 250000,
    "totalClicks": 7500,
    "totalSpent": 15000.00,
    "averageCTR": 3.0
  }
}
```

---

## 12. Analytics Endpoints

### GET `/analytics`
Get overall analytics
- **Query Parameters:** `period=7d|30d|90d|1y`
- **Response:**
```json
{
  "success": true,
  "data": {
    "users": {
      "total": 15420,
      "growth": 5.2
    },
    "revenue": {
      "total": 125000.50,
      "growth": 8.5
    },
    "transactions": {
      "total": 3420,
      "growth": 3.2
    }
  }
}
```

### GET `/analytics/users`
Get user analytics
- **Query Parameters:** `period=7d|30d|90d|1y`
- **Response:**
```json
{
  "success": true,
  "data": {
    "chartData": {
      "labels": ["Week 1", "Week 2", "Week 3", "Week 4"],
      "datasets": [
        {
          "label": "New Users",
          "data": [120, 150, 180, 200]
        },
        {
          "label": "Active Users",
          "data": [5000, 5200, 5500, 5800]
        }
      ]
    },
    "summary": {
      "totalUsers": 15420,
      "newUsers": 650,
      "activeUsers": 5800,
      "growth": 5.2
    }
  }
}
```

### GET `/analytics/revenue`
Get revenue analytics
- **Query Parameters:** `period=7d|30d|90d|1y`
- **Response:**
```json
{
  "success": true,
  "data": {
    "chartData": {
      "labels": ["Jan", "Feb", "Mar", "Apr"],
      "datasets": [
        {
          "label": "Revenue",
          "data": [25000, 30000, 35000, 35000.50]
        }
      ]
    },
    "summary": {
      "totalRevenue": 125000.50,
      "averageRevenue": 31250.13,
      "growth": 8.5
    }
  }
}
```

### GET `/analytics/ads`
Get ads analytics
- **Query Parameters:** `period=7d|30d|90d|1y`
- **Response:**
```json
{
  "success": true,
  "data": {
    "chartData": {
      "labels": ["Week 1", "Week 2", "Week 3", "Week 4"],
      "datasets": [
        {
          "label": "Impressions",
          "data": [50000, 60000, 65000, 75000]
        },
        {
          "label": "Clicks",
          "data": [1500, 1800, 2000, 2200]
        }
      ]
    },
    "summary": {
      "totalImpressions": 250000,
      "totalClicks": 7500,
      "averageCTR": 3.0,
      "totalSpent": 15000.00
    }
  }
}
```

---

## 13. Notification Endpoints

### GET `/notifications`
Get all notifications
- **Query Parameters:**
  - `type` (optional): `socials|connect|market|gym|all`
  - `status` (optional): `sent|pending|failed|all`
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": "notif_1",
        "title": "New Feature Alert",
        "message": "Check out our new marketplace!",
        "type": "market",
        "status": "sent",
        "recipients": 5000,
        "sentAt": "2026-02-19T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 10,
      "totalItems": 200
    }
  }
}
```

### GET `/notifications/:id`
Get notification by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "notif_1",
    "title": "New Feature Alert",
    "message": "Check out our new marketplace!",
    "type": "market",
    "status": "sent",
    "recipients": 5000,
    "opened": 3200,
    "clicked": 450,
    "sentAt": "2026-02-19T10:00:00Z"
  }
}
```

### POST `/notifications/send`
Send notification to specific users
- **Request Body:**
```json
{
  "title": "Important Update",
  "message": "System maintenance scheduled",
  "type": "socials",
  "targetUsers": ["1", "2", "3"]
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Notification sent successfully",
  "data": {
    "id": "notif_2",
    "recipients": 3
  }
}
```

### POST `/notifications/send-bulk`
Send bulk notification
- **Request Body:**
```json
{
  "title": "Weekly Update",
  "message": "Check out this week's highlights!",
  "type": "socials",
  "userType": "all"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Bulk notification sent successfully",
  "data": {
    "id": "notif_3",
    "recipients": 15420
  }
}
```

### POST `/notifications/:id/read`
Mark notification as read
- **Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

---

## 14. Support Endpoints

### GET `/support/tickets`
Get all support tickets
- **Query Parameters:**
  - `status` (optional): `open|in-progress|closed|all`
  - `priority` (optional): `low|medium|high|all`
  - `page`, `limit`
- **Response:**
```json
{
  "success": true,
  "data": {
    "tickets": [
      {
        "id": "ticket_1",
        "userId": "1",
        "userName": "John Doe",
        "subject": "Payment Issue",
        "status": "open",
        "priority": "high",
        "createdAt": "2026-02-19T08:00:00Z",
        "lastUpdated": "2026-02-19T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 15,
      "totalItems": 300
    }
  }
}
```

### GET `/support/tickets/:id`
Get ticket by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "ticket_1",
    "userId": "1",
    "userName": "John Doe",
    "userEmail": "john@example.com",
    "subject": "Payment Issue",
    "description": "Unable to complete payment",
    "status": "open",
    "priority": "high",
    "messages": [
      {
        "id": "msg_1",
        "sender": "user",
        "message": "I can't complete my payment",
        "timestamp": "2026-02-19T08:00:00Z"
      }
    ],
    "createdAt": "2026-02-19T08:00:00Z",
    "lastUpdated": "2026-02-19T10:00:00Z"
  }
}
```

### POST `/support/tickets`
Create support ticket
- **Request Body:**
```json
{
  "userId": "1",
  "subject": "Account Issue",
  "description": "Cannot access my account",
  "priority": "medium"
}
```

### PUT `/support/tickets/:id`
Update ticket
- **Request Body:**
```json
{
  "status": "in-progress",
  "priority": "high"
}
```

### POST `/support/tickets/:id/close`
Close ticket
- **Response:**
```json
{
  "success": true,
  "message": "Ticket closed successfully"
}
```

---

## 15. Admin Management Endpoints

### GET `/admin`
Get all admins
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "admin_1",
      "fullName": "Admin User",
      "username": "adminuser",
      "email": "admin@gympaddy.com",
      "role": "super_admin",
      "status": "active",
      "createdAt": "2026-01-01T00:00:00Z"
    }
  ]
}
```

### GET `/admin/:id`
Get admin by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": "admin_1",
    "fullName": "Admin User",
    "username": "adminuser",
    "email": "admin@gympaddy.com",
    "role": "super_admin",
    "permissions": ["all"],
    "status": "active",
    "lastLogin": "2026-02-19T18:00:00Z",
    "createdAt": "2026-01-01T00:00:00Z"
  }
}
```

### POST `/admin`
Create new admin
- **Request Body:**
```json
{
  "fullName": "New Admin",
  "username": "newadmin",
  "email": "newadmin@gympaddy.com",
  "password": "securePassword123",
  "role": "admin"
}
```

### PUT `/admin/:id`
Update admin
- **Request Body:**
```json
{
  "fullName": "Updated Name",
  "role": "moderator"
}
```

### DELETE `/admin/:id`
Delete admin
- **Response:**
```json
{
  "success": true,
  "message": "Admin deleted successfully"
}
```

---

## Error Responses

All endpoints return errors in the following format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message"
  }
}
```

### Common Error Codes:
- `UNAUTHORIZED` (401): Invalid or missing authentication token
- `FORBIDDEN` (403): Insufficient permissions
- `NOT_FOUND` (404): Resource not found
- `VALIDATION_ERROR` (400): Invalid request data
- `SERVER_ERROR` (500): Internal server error

---

## Notes for Backend Team

1. **Pagination**: All list endpoints should support pagination with `page` and `limit` query parameters
2. **Filtering**: Implement filtering as specified in query parameters
3. **Search**: Search functionality should be case-insensitive and support partial matches
4. **File Uploads**: Use multipart/form-data for endpoints that accept files
5. **Authentication**: All endpoints except auth endpoints require Bearer token
6. **Date Format**: Use ISO 8601 format for all dates (e.g., "2026-02-19T18:30:00Z")
7. **Response Format**: Maintain consistent response structure with `success`, `data`, and optional `pagination` fields
8. **Error Handling**: Return appropriate HTTP status codes and error messages
9. **Rate Limiting**: Consider implementing rate limiting for API endpoints
10. **CORS**: Configure CORS to allow requests from the dashboard domain

---

## Environment Variables Required

```env
API_BASE_URL=http://localhost:5000/api
JWT_SECRET=your_jwt_secret_key
DATABASE_URL=your_database_connection_string
UPLOAD_PATH=/uploads
MAX_FILE_SIZE=10485760
```

---

## Testing Recommendations

1. Test all CRUD operations for each resource
2. Verify authentication and authorization
3. Test pagination and filtering
4. Validate file upload functionality
5. Test error handling scenarios
6. Verify data validation
7. Test concurrent requests
8. Performance testing for list endpoints with large datasets

---

**Document Version:** 1.0  
**Last Updated:** February 19, 2026  
**Contact:** For questions or clarifications, please contact the frontend team.
