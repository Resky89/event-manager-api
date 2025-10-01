# Event Manager API (Laravel 12)

Base URL: `/api`

## Auth

- POST `/auth/register`
  - Body: `{ name, email, password, role? (admin|organizer|user), is_active? }`
  - 201 Created
- POST `/auth/login`
  - Body: `{ email, password }`
  - 200 OK -> `{ user, access_token, refresh_token, expires_in }`
- POST `/auth/refresh`
  - Body: `{ refresh_token }` atau Header `X-Refresh-Token`
  - 200 OK -> token baru
- POST `/auth/logout`
  - Header: `Authorization: Bearer <access_token>`
  - Body/Header: sertakan `refresh_token` (body atau `X-Refresh-Token`)
  - 200 OK

## Users (Admin only)
Header: `Authorization: Bearer <access_token>` + `role: admin`

- GET `/users` (paginate)
- GET `/users/{id}`
- POST `/users` body: `{ name, email, password, role, is_active? }`
- PUT `/users/{id}` body: `{ name, email, password?, role, is_active? }`
- DELETE `/users/{id}`

## Events
- GET `/events`
- GET `/events/{id}`
- POST `/events` (organizer|admin)
  - Header: `Authorization: Bearer <access_token>`
  - Body: `{ title, description?, location?, start_time (Y-m-d H:i:s), end_time (Y-m-d H:i:s), organizer_id? }`
- PUT `/events/{id}` (organizer|admin)
- DELETE `/events/{id}` (organizer|admin)

## Tickets
- GET `/events/{eventId}/tickets`
- POST `/tickets` (organizer|admin)
  - Header: `Authorization: Bearer <access_token>`
  - Body: `{ event_id, type, price?, quantity }`
- PUT `/tickets/{id}` (organizer|admin)
- DELETE `/tickets/{id}` (organizer|admin)

## Registrations
- POST `/registrations`
  - Header: `Authorization: Bearer <access_token>`
  - Body: `{ event_id, ticket_id }`
- GET `/me/registrations`
  - Header: `Authorization: Bearer <access_token>`

## Feedback
- POST `/feedback`
  - Header: `Authorization: Bearer <access_token>`
  - Body: `{ event_id, rating (1-5), comment? }`
## Response Format Standar
{
  "success": true|false,
  "message": "...",
  "data": { ... } | null,
  "errors": { ... } | null
}

## Advanced Query Params (Search, Sort, Filter)

Semua endpoint list mendukung parameter berikut via query string:

- `per_page`: jumlah item per halaman (default 15)
- `sort`: daftar kolom `sortable` dipisahkan koma; awali `-` untuk desc
  - Contoh: `sort=created_at,-name`
- Filter per kolom `filterable`:
  - `field=value` (exact)
  - `field_in=a,b,c` (IN)
  - `field_not=value` (NOT EQUAL)
  - `field_min=value` / `field_max=value` (range numeric/datetime)

Kolom per resource:

### Users: GET `/users`
- Searchable: `name`, `email`
- Sortable: `id`, `name`, `email`, `role`, `is_active`, `created_at`
- Filterable:
  - `id` (int)
  - `name` (string_like)
  - `email` (string_like)
  - `role` (string)
  - `is_active` (bool)
  - `created_at` (datetime; dukung `_min`/`_max`)
- Contoh:
  - `/users?search=andrew&sort=-created_at`
  - `/users?role=admin&is_active=true&created_at_min=2025-01-01`

### Events: GET `/events`
- Searchable: `title`, `description`, `location`
- Sortable: `id`, `title`, `start_time`, `end_time`, `created_at`
- Filterable:
  - `id` (int)
  - `organizer_id` (int)
  - `title` (string_like)
  - `location` (string_like)
  - `start_time` (datetime; `_min`/`_max`)
  - `end_time` (datetime; `_min`/`_max`)
  - `created_at` (datetime; `_min`/`_max`)
- Contoh:
  - `/events?search=meetup&sort=start_time`
  - `/events?organizer_id=10&start_time_min=2025-10-01 00:00:00&start_time_max=2025-10-31 23:59:59`

### Tickets: GET `/events/{eventId}/tickets`
- Searchable: `type`
- Sortable: `id`, `type`, `price`, `quantity`, `created_at`
- Filterable:
  - `id` (int)
  - `type` (string_like)
  - `price` (numeric; `_min`/`_max`)
  - `quantity` (int; `_min`/`_max`)
  - `created_at` (datetime; `_min`/`_max`)
- Contoh:
  - `/events/5/tickets?type=vip&sort=-price`
  - `/events/5/tickets?price_min=100000&price_max=250000`

### Registrations: GET `/me/registrations`
- Searchable: `status`
- Sortable: `id`, `event_id`, `ticket_id`, `status`, `registered_at`, `created_at`
- Filterable:
  - `id` (int)
  - `event_id` (int)
  - `ticket_id` (int)
  - `status` (string)
  - `registered_at` (datetime; `_min`/`_max`)
  - `created_at` (datetime; `_min`/`_max`)
- Contoh:
  - `/me/registrations?status=confirmed&sort=-registered_at`

### Feedback: GET `/events/{eventId}/feedback`
- Searchable: `comment`
- Sortable: `id`, `rating`, `created_at`
- Filterable:
  - `id` (int)
  - `user_id` (int)
  - `rating` (int)
  - `comment` (string_like)
  - `created_at` (datetime; `_min`/`_max`)
- Contoh:
  - `/events/9/feedback?rating_in=4,5&sort=-created_at`
  - `/events/9/feedback?comment=bagus`
