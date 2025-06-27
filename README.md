<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## API Testing Instructions (Postman)

All `/api/user/*` endpoints require the `Authorization: Bearer {token}` header. The authenticated user is determined from this token. You do **not** need to send `user_id` in the request body for creating or updating resources; it is automatically set from the token.

### Auth

- **POST** `/api/auth/register`
  - Body (JSON):
    ```json
    {
      "username": "johndoe",
      "fullname": "John Doe",
      "email": "john@example.com",
      "phone": "1234567890",
      "age": 25,
      "gender": "male",
      "password": "yourpassword",
      "password_confirmation": "yourpassword"
    }
    ```
- **POST** `/api/auth/login`
  - Body (JSON):
    ```json
    {
      "email": "john@example.com",
      "password": "yourpassword"
    }
    ```
- **POST** `/api/auth/forgot-password`
  - Body (JSON):
    ```json
    {
      "email": "john@example.com"
    }
    ```
- **POST** `/api/auth/verify-otp`
  - Body (JSON):
    ```json
    {
      "email": "john@example.com",
      "token": "reset-token-from-email",
      "password": "newpassword",
      "password_confirmation": "newpassword"
    }
    ```

### Personal Access Tokens

- **GET** `/api/personal-access-tokens`
- **POST** `/api/personal-access-tokens`
  - Body (JSON):
    ```json
    {
      "tokenable_type": "App\\Models\\User",
      "tokenable_id": 1,
      "name": "MyToken",
      "token": "sometoken",
      "abilities": [],
      "last_used_at": null,
      "expires_at": null
    }
    ```
- **GET** `/api/personal-access-tokens/{id}`
- **PUT** `/api/personal-access-tokens/{id}`
  - Body (JSON):
    ```json
    {
      "name": "UpdatedToken",
      "abilities": [],
      "last_used_at": null,
      "expires_at": null
    }
    ```
- **DELETE** `/api/personal-access-tokens/{id}`

### User Endpoints (require Bearer token)

#### Posts

- **GET** `/api/user/posts`
  - Returns posts belonging to the authenticated user.
- **POST** `/api/user/posts`
  - Body (JSON):
    ```json
    {
      "title": "My First Post",
      "content": "Hello world!",
      "media_url": "https://example.com/photo.jpg"
    }
    ```
- **GET** `/api/user/posts/{post}`
- **PUT** `/api/user/posts/{post}`
  - Body (JSON):
    ```json
    {
      "title": "Updated Title",
      "content": "Updated content"
    }
    ```
- **DELETE** `/api/user/posts/{post}`

#### Comments

- **GET** `/api/user/comments?post_id={post_id}`
- **POST** `/api/user/comments`
  - Body (JSON):
    ```json
    {
      "post_id": 1,
      "content": "Nice post!",
      "parent_id": null
    }
    ```
- **GET** `/api/user/comments/{comment}`
- **PUT** `/api/user/comments/{comment}`
  - Body (JSON):
    ```json
    {
      "content": "Updated comment"
    }
    ```
- **DELETE** `/api/user/comments/{comment}`

#### Wallets

- **GET** `/api/user/wallets`
- **POST** `/api/user/wallets`
  - Body (JSON):
    ```json
    {
      "balance": 0
    }
    ```
- **GET** `/api/user/wallets/{wallet}`
- **PUT** `/api/user/wallets/{wallet}`
  - Body (JSON):
    ```json
    {
      "balance": 100
    }
    ```
- **DELETE** `/api/user/wallets/{wallet}`
- **POST** `/api/user/wallet/topup`
  - Body (JSON):
    ```json
    {
      "amount": 100
    }
    ```
- **POST** `/api/user/wallet/withdraw`
  - Body (JSON):
    ```json
    {
      "amount": 50
    }
    ```

#### Gifts

- **POST** `/api/user/gifts`
  - Body (JSON):
    ```json
    {
      "to_user_id": 2,
      "name": "Gift Card",
      "value": 10,
      "message": "Congrats!"
    }
    ```
- **PUT** `/api/user/gifts/{gift}`
  - Body (JSON):
    ```json
    {
      "name": "Updated Gift",
      "value": 20
    }
    ```

#### Transactions

- **POST** `/api/user/transactions`
  - Body (JSON):
    ```json
    {
      "wallet_id": 1,
      "amount": 100,
      "type": "topup"
    }
    ```
- **PUT** `/api/user/transactions/{transaction}`
  - Body (JSON):
    ```json
    {
      "amount": 50,
      "type": "withdraw"
    }
    ```
- **DELETE** `/api/user/transactions/{transaction}`

#### Businesses

- **GET** `/api/user/businesses`
- **POST** `/api/user/businesses`
  - Body (JSON):
    ```json
    {
      "name": "My Gym Business",
      "description": "Best gym in town"
    }
    ```
- **GET** `/api/user/businesses/{business}`
- **PUT** `/api/user/businesses/{business}`
  - Body (JSON):
    ```json
    {
      "name": "Updated Business",
      "description": "Updated description"
    }
    ```
- **DELETE** `/api/user/businesses/{business}`

#### Ad Campaigns

- **GET** `/api/user/ad-campaigns`
- **POST** `/api/user/ad-campaigns`
  - Body (JSON):
    ```json
    {
      "name": "Summer Promo",
      "budget": 500
    }
    ```
- **GET** `/api/user/ad-campaigns/{ad_campaign}`
- **PUT** `/api/user/ad-campaigns/{ad_campaign}`
  - Body (JSON):
    ```json
    {
      "name": "Updated Promo",
      "budget": 600
    }
    ```
- **DELETE** `/api/user/ad-campaigns/{ad_campaign}`

#### Ad Insights

- **GET** `/api/user/ad-insights`
- **GET** `/api/user/ad-insights/{ad_insight}`

#### Marketplace Listings

- **GET** `/api/user/marketplace-listings`
- **POST** `/api/user/marketplace-listings`
  - Body (JSON):
    ```json
    {
      "title": "Dumbbells Set",
      "category_id": 1,
      "price": 100,
      "status": "pending"
    }
    ```
- **GET** `/api/user/marketplace-listings/{marketplace_listing}`
- **PUT** `/api/user/marketplace-listings/{marketplace_listing}`
  - Body (JSON):
    ```json
    {
      "title": "Updated Dumbbells Set",
      "price": 90
    }
    ```
- **DELETE** `/api/user/marketplace-listings/{marketplace_listing}`

#### Marketplace Categories

- **GET** `/api/user/marketplace-categories`
- **POST** `/api/user/marketplace-categories`
  - Body (JSON):
    ```json
    {
      "name": "gymEquipment"
    }
    ```
- **GET** `/api/user/marketplace-categories/{marketplace_category}`
- **PUT** `/api/user/marketplace-categories/{marketplace_category}`
  - Body (JSON):
    ```json
    {
      "name": "supplement"
    }
    ```
- **DELETE** `/api/user/marketplace-categories/{marketplace_category}`

#### Live Streams

- **GET** `/api/user/live-streams`
- **POST** `/api/user/live-streams`
  - Body (JSON):
    ```json
    {
      "title": "Morning Workout Live"
    }
    ```
- **GET** `/api/user/live-streams/{live_stream}`
- **PUT** `/api/user/live-streams/{live_stream}`
  - Body (JSON):
    ```json
    {
      "title": "Evening Workout Live"
    }
    ```
- **DELETE** `/api/user/live-streams/{live_stream}`

#### Reels

- **GET** `/api/user/reels`
- **POST** `/api/user/reels`
  - Body (JSON):
    ```json
    {
      "title": "My Gym Reel",
      "media_url": "https://example.com/reel.mp4"
    }
    ```
- **GET** `/api/user/reels/{reel}`
- **PUT** `/api/user/reels/{reel}`
  - Body (JSON):
    ```json
    {
      "title": "Updated Reel"
    }
    ```
- **DELETE** `/api/user/reels/{reel}`

#### Likes

- **GET** `/api/user/likes`
- **POST** `/api/user/likes`
  - Body (JSON):
    ```json
    {
      "likeable_id": 1,
      "likeable_type": "Post"
    }
    ```
- **GET** `/api/user/likes/{like}`
- **PUT** `/api/user/likes/{like}`
  - Body (JSON):
    ```json
    {
      "likeable_id": 2,
      "likeable_type": "Comment"
    }
    ```
- **DELETE** `/api/user/likes/{like}`

#### Shares

- **GET** `/api/user/shares`
- **POST** `/api/user/shares`
  - Body (JSON):
    ```json
    {
      "shareable_id": 1,
      "shareable_type": "Post"
    }
    ```
- **GET** `/api/user/shares/{share}`
- **PUT** `/api/user/shares/{share}`
  - Body (JSON):
    ```json
    {
      "shareable_id": 2,
      "shareable_type": "Reel"
    }
    ```
- **DELETE** `/api/user/shares/{share}`

#### Follows

- **GET** `/api/user/follows`
- **POST** `/api/user/follows`
  - Body (JSON):
    ```json
    {
      "follower_id": 1,
      "followable_id": 2,
      "followable_type": "User"
    }
    ```
- **GET** `/api/user/follows/{follow}`
- **PUT** `/api/user/follows/{follow}`
  - Body (JSON):
    ```json
    {
      "followable_id": 3,
      "followable_type": "Business"
    }
    ```
- **DELETE** `/api/user/follows/{follow}`

#### Notifications

- **GET** `/api/user/notifications`
- **POST** `/api/user/notifications`
  - Body (JSON):
    ```json
    {
      "message": "Welcome to GymPaddy!"
    }
    ```
- **GET** `/api/user/notifications/{notification}`
- **PUT** `/api/user/notifications/{notification}`
  - Body (JSON):
    ```json
    {
      "message": "Updated notification"
    }
    ```
- **DELETE** `/api/user/notifications/{notification}`

#### Chat Messages

- **GET** `/api/user/chat-messages`
- **POST** `/api/user/chat-messages`
  - Body (JSON):
    ```json
    {
      "sender_id": 1,
      "receiver_id": 2,
      "message": "Hello!"
    }
    ```
- **GET** `/api/user/chat-messages/{chat_message}`
- **PUT** `/api/user/chat-messages/{chat_message}`
  - Body (JSON):
    ```json
    {
      "message": "Updated message"
    }
    ```
- **DELETE** `/api/user/chat-messages/{chat_message}`

#### Tickets

- **GET** `/api/user/tickets`
- **POST** `/api/user/tickets`
  - Body (JSON):
    ```json
    {
      "subject": "Support Needed",
      "message": "I need help with my account."
    }
    ```
- **GET** `/api/user/tickets/{ticket}`
- **PUT** `/api/user/tickets/{ticket}`
  - Body (JSON):
    ```json
    {
      "subject": "Updated Subject",
      "message": "Updated message"
    }
    ```
- **DELETE** `/api/user/tickets/{ticket}`

#### Video Calls

- **GET** `/api/user/video-calls`
- **POST** `/api/user/video-calls`
  - Body (JSON):
    ```json
    {
      "caller_id": 1,
      "receiver_id": 2,
      "channel_name": "call-channel"
    }
    ```
- **GET** `/api/user/video-calls/{video_call}`
- **PUT** `/api/user/video-calls/{video_call}`
  - Body (JSON):
    ```json
    {
      "status": "ended"
    }
    ```
- **DELETE** `/api/user/video-calls/{video_call}`

#### User Profile

- **GET** `/api/user/profile`
  - Returns the current authenticated user's profile
  - Headers: `Authorization: Bearer {token}`

- **POST** `/api/user/edit-profile`
  - Updates user profile with optional image upload
  - Headers: `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`
  - Body (multipart/form-data):
    ```
    username: johndoe_updated (optional)
    fullname: John Doe Updated (optional)
    age: 26 (optional)
    gender: male (optional, values: male|female|other)
    profile_picture: [file] (optional, max 2MB, formats: jpeg,jpg,png,gif)
    ```

#### Storage Setup

Before testing file uploads, run the following command to create symbolic links for public storage access:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`, making uploaded files publicly accessible via URLs like `http://yourapp.com/storage/profile_pictures/filename.jpg`.

#### Example Profile Edit Request (Postman)

1. Set method to `POST`
2. URL: `http://yourapp.com/api/user/edit-profile`
3. Headers:
   - `Authorization: Bearer {your_token}`
4. Body (form-data):
   - Key: `username`, Value: `johndoe_updated`
   - Key: `fullname`, Value: `John Doe Updated`
   - Key: `age`, Value: `26`
   - Key: `gender`, Value: `male`
   - Key: `profile_picture`, Type: File, Value: [select image file]

#### Response Format

```json
{
  "status": "success",
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "username": "johndoe_updated",
    "fullname": "John Doe Updated",
    "email": "john@example.com",
    "phone": "1234567890",
    "age": 26,
    "gender": "male",
    "role": "user",
    "profile_picture": "profile_pictures/1703123456_abc123.jpg",
    "profile_picture_url": "http://yourapp.com/storage/profile_pictures/1703123456_abc123.jpg",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T12:30:45.000000Z"
  }
}
```
