# Chatbot Application

A full-stack chat application built with Laravel (backend) and Nuxt.js (frontend) that enables real-time communication between users and agents.

## Features

- Real-time chat functionality
- User authentication and authorization
- Agent interface for customer support
- Feedback system
- Role-based access control (users and agents)
- Persistent chat history
- Real-time agent status updates

## Tech Stack

### Backend
- Laravel 10.x
- PHP 8.x
- SQLite database
- Laravel Events/Broadcasting for real-time features
- Laravel Authentication

### Frontend
- Nuxt.js 3
- TypeScript
- Pinia for state management
- Vue 3 Components
- Tailwind CSS (assumed based on common stack)

## Project Structure

```
backend/           # Laravel backend application
├── app/          # Application core code
├── database/     # Migrations and seeders
└── routes/       # API and web routes

frontend/         # Nuxt.js frontend application
├── components/   # Vue components
├── pages/       # Application pages
└── stores/      # Pinia state stores
```

## Setup Instructions

### Backend Setup

1. Navigate to the backend directory:
   ```bash
   cd backend
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Run database migrations:
   ```bash
   php artisan migrate
   ```

5. Start the server:
   ```bash
   php artisan serve
   ```

### Frontend Setup

1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm run dev
   ```

## Features Details

### Authentication
- User registration and login
- Token-based authentication
- Role-based authorization (users/agents)

### Chat System
- Real-time messaging
- Message history
- Agent status tracking
- Chat session management

### Agent Interface
- Dedicated agent dashboard
- Real-time status updates
- Chat session management
- User interaction tools

## API Endpoints

The backend provides the following main API endpoints:

- `POST /api/login` - User authentication
- `POST /api/logout` - User logout
- `GET /api/user` - Get authenticated user details

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
