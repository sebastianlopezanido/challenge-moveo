# Challenge drvn API

Proyecto de api-backend utilizando **laravel** .

## Project Description

Develop a blog web application where users can register, log in, create posts, comment on other users' posts,
and manage their own content. The application must expose a RESTful API to handle operations and ensure
proper protection and query optimization.

## Installation and Setup

To set up and run the server locally, follow these steps:

Clone the repository.

> git clone {{repo_url}}

Install project dependencies (this creates the vendor folder)

> composer install

Add environment variables: Create a .env file and add necessary environment variables

> touch .env

Generate the application key

> php artisan key:generate

Set up and configure the database

> php artisan migrate

Run database seeders

> php artisan db:seed

Run the development server

> php artisan serve

(Optional) Run Tests

> php artisan test

# comments to requirements

**User Management**

-   User roles: Defined using the `Role` model and `RoleMiddleware`.
-   Route protection based on roles: Example route /admin showcases role-based access.

**Post Management**

-   associate post with users: Implemented in `PostService::createNewPost`.
-   Paginated listing of posts: Implemented in `PostController::index`.

**Comment Management**

-   associate comment with post and users: Managed in `CommentController` and with shallow routes.
-   Paginated listing of posts: Implemented in `PostController::index`.

**RESTful API**

-   Request validation: Implemented with `BaseApiRequest` as an abstract class and model-specific requests.
-   Structured and sanitized JSON responses: Managed via `JsonResponseTrait`.

**Security**

-   Protection against common attacks (SQL Injection, XSS, CSRF). Basic protection is set up in `AppServiceProvider` using `RateLimiter`.
-   Authentication and authorization: Managed through `Sanctum`.

**Optimization and Best Practices**

-   Use of Eloquent ORM and Query Builder. Standardized throughout the application.
-   Prevention of N+1 query problems. Managed via eager loading with with in `PostRepository::getAllPosts`.
-   Implementation of design patterns: Followed with Service and Repository layers (`PostController`, `PostService`, `PostRepository`).
-   Use of Middlewares, Jobs, and Queues. Includes `RoleMiddleware`, `SendCommentNotification` job for background processing.

**Testing**

-   Unit tests using PHPUnit or PEST: `AdminAccessTest`, `AuthControllerTest`, `CommentControllerTest`, `PostControllerTest`

**Testing**

-   Documentation: route /api/documentation implements `Swagger` documentation
