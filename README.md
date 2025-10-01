event-manager-api/ 
│── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/         
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── EventController.php
│   │   │   │   ├── TicketController.php
│   │   │   │   ├── RegistrationController.php
│   │   │   │   └── FeedbackController.php
│   │   ├── Middleware/
│   │   │   ├── JwtMiddleware.php         # Validasi JWT
│   │   │   └── RoleMiddleware.php        # Role-based Access
│   │   ├── Requests/                     # Validasi input
│   │   │   ├── Auth/
│   │   │   │   ├── LoginRequest.php
│   │   │   │   └── RegisterRequest.php
│   │   │   ├── EventRequest.php
│   │   │   ├── TicketRequest.php
│   │   │   ├── RegistrationRequest.php
│   │   │   └── FeedbackRequest.php
│   │   ├── Resources/                    # Transformasi data API (response JSON)
│   │   │   ├── UserResource.php
│   │   │   ├── EventResource.php
│   │   │   ├── TicketResource.php
│   │   │   ├── RegistrationResource.php
│   │   │   └── FeedbackResource.php
│   │   └── Kernel.php
│   │
│   ├── Models/                  # Model Eloquent
│   │   ├── User.php
│   │   ├── Event.php
│   │   ├── Ticket.php
│   │   ├── Registration.php
│   │   └── Feedback.php
│   │
│   ├── Repositories/            # Query logic (DRY)
│   │   ├── Contracts/
│   │   │   ├── UserRepositoryInterface.php
│   │   │   ├── EventRepositoryInterface.php
│   │   │   └── TicketRepositoryInterface.php
│   │   ├── Eloquent/
│   │   │   ├── UserRepository.php
│   │   │   ├── EventRepository.php
│   │   │   └── TicketRepository.php
│   │
│   ├── Services/                # Business logic
│   │   ├── AuthService.php      # bcrypt & JWT logic
│   │   ├── UserService.php
│   │   ├── EventService.php
│   │   ├── TicketService.php
│   │   └── RegistrationService.php
│   │
│   ├── Helpers/                 # Utility functions
│   │   ├── JwtHelper.php
│   │   └── ResponseFormatter.php
│   │
│   └── Providers/
│       └── RepositoryServiceProvider.php
│
│── bootstrap/
│── config/
│   ├── jwt.php                  # Config JWT
│
│── database/
│   ├── factories/
│   ├── migrations/              
│   │   ├── create_users_table.php
│   │   ├── create_events_table.php
│   │   ├── create_tickets_table.php
│   │   ├── create_registrations_table.php
│   │   └── create_feedback_table.php
│   └── seeders/
│       ├── UserSeeder.php
│       ├── EventSeeder.php
│       └── TicketSeeder.php
│
│── routes/
│   ├── api/
│   │   ├── auth.php             # Login, Register, Refresh Token
│   │   ├── users.php            # User CRUD
│   │   ├── events.php           # Event CRUD
│   │   ├── tickets.php          # Ticket CRUD
│   │   ├── registrations.php    # Event Registrations
│   │   └── feedback.php         # Feedback
│   ├── api.php                  # Load semua route di atas
│   └── web.php
│
│── tests/
│   ├── Feature/
│   │   ├── AuthTest.php
│   │   ├── EventTest.php
│   │   └── TicketTest.php
│   └── Unit/
│       ├── EventServiceTest.php
│       └── UserRepositoryTest.php
│
│── .env
│── composer.json
│── artisan
