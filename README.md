# Anup Dahal — Professional Portfolio

A complete professional portfolio system with a modern frontend (HTML, CSS, JavaScript) and a Node.js/Express backend API connected to a MySQL database.

## Project Structure

```
├── frontend/              # Static frontend (deployable on Netlify)
│   ├── index.html         # Main portfolio page
│   ├── admin.html         # Admin panel
│   ├── css/
│   │   ├── style.css      # Main styles
│   │   └── admin.css      # Admin panel styles
│   ├── js/
│   │   ├── api.js         # API communication layer
│   │   ├── app.js         # Main app logic
│   │   └── admin.js       # Admin panel logic
│   └── assets/            # Images and static assets
├── backend/               # Node.js/Express API server
│   ├── server.js          # Express server entry point
│   ├── config/
│   │   └── db.js          # MySQL database connection pool
│   ├── routes/
│   │   ├── auth.js        # POST /api/login
│   │   ├── projects.js    # CRUD /api/projects
│   │   ├── memories.js    # CRUD /api/memories
│   │   └── blogs.js       # CRUD /api/blogs
│   ├── middleware/
│   │   └── auth.js        # JWT authentication middleware
│   └── database/
│       └── schema.sql     # Database schema + seed data
├── netlify.toml           # Netlify deployment config
└── README.md
```

## Features

### Frontend
- Fully responsive professional UI
- Dark/light mode toggle
- Smooth scroll animations
- Page loader
- Toast notifications
- Sections: Home, About, Skills, Education, Experience, Projects, Memories, Blog, Contact
- Dynamic content loaded from API with fallback
- Project filtering and search
- Blog search
- Memory gallery with modal viewer
- SEO meta tags (Open Graph, Twitter Cards)

### Backend API
- RESTful API with Express.js
- MySQL database with connection pooling
- JWT authentication for admin routes
- CORS handling
- Input validation
- Pagination support

### Admin Panel
- Secure login system
- Dashboard with content stats
- CRUD operations for Projects, Memories, and Blog posts
- Responsive sidebar navigation
- Form modals for adding/editing content

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/login | No | Admin login |
| GET | /api/projects | No | List projects (with search, filter, pagination) |
| POST | /api/projects | Yes | Create project |
| PUT | /api/projects/:id | Yes | Update project |
| DELETE | /api/projects/:id | Yes | Delete project |
| GET | /api/memories | No | List memories (with pagination) |
| POST | /api/memories | Yes | Add memory |
| DELETE | /api/memories/:id | Yes | Delete memory |
| GET | /api/blogs | No | List blogs (with search, pagination) |
| GET | /api/blogs/:id | No | Get single blog post |
| POST | /api/blogs | Yes | Create blog post |
| PUT | /api/blogs/:id | Yes | Update blog post |
| DELETE | /api/blogs/:id | Yes | Delete blog post |
| GET | /api/health | No | Health check |

## Setup

### Database
1. Install MySQL and create the database:
   ```sql
   source backend/database/schema.sql
   ```

### Backend
1. Navigate to the backend directory:
   ```bash
   cd backend
   ```
2. Copy the environment file and configure:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials and JWT secret
   ```
3. Install dependencies and start:
   ```bash
   npm install
   npm start
   ```
   The API will run on `http://localhost:5000`.

### Frontend
1. Update the API URL in `frontend/js/api.js`:
   ```js
   const BASE_URL = window.API_BASE_URL || 'http://localhost:5000/api';
   ```
2. For local development, serve the `frontend/` directory with any static server.
3. For Netlify deployment, the `netlify.toml` is already configured to publish from `frontend/`.

### Default Admin Credentials
- Username: `admin`
- Password: `admin123`

**Change these in production** by updating the `admin_users` table.

## Tech Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: Node.js, Express.js
- **Database**: MySQL
- **Auth**: JWT (JSON Web Tokens)
- **Deployment**: Netlify (frontend), any Node.js host (backend)
