# Exam Timetable Optimization Platform

A complete Laravel 11 application for managing and optimizing exam timetables in educational institutions. Built with MySQL 8.0 and Orchid Platform admin panel.

## Features

- **Complete Database Schema**: Full implementation of academic entities (Universities, Faculties, Departments, Formations, Modules, Students, Professors, Rooms, Equipment)
- **Planning Runs Workflow**: Generate → Submit → Approve/Reject → Publish
- **Heuristic Optimizer**: Automated schedule generation with conflict detection
- **Conflict Detection**: Real-time detection of room conflicts, student overlaps, and constraint violations
- **Role-Based Access Control**: Admin, Dean, Department Head, Professor, Student roles
- **Orchid Admin Panel**: Beautiful and intuitive admin interface for all CRUD operations

## Tech Stack

- **Laravel 11**
- **MySQL 8.0**
- **Orchid Platform** (latest stable)
- **PHP 8.2+**

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0
- Node.js and NPM (for frontend assets)

### Step 1: Clone and Install Dependencies

```bash
git clone <repository-url>
cd projet_bda
composer install
npm install
```

### Step 2: Environment Configuration

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Update the `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exam_timetable
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 3: Generate Application Key

```bash
php artisan key:generate
```

### Step 4: Run Migrations

```bash
php artisan migrate
```

### Step 5: Seed Database

```bash
php artisan db:seed
```

This will create:
- 1 University
- 1 Faculty
- 7 Departments
- Multiple Formations (L, M, D levels)
- 6-9 Modules per Formation
- 500+ Students
- 60+ Professors
- 30 Rooms with equipment
- 3 Exam Periods
- Student enrollments (inscriptions)
- Demo users with different roles

### Step 6: Create Admin User (Orchid)

```bash
php artisan orchid:admin
```

Follow the prompts to create an admin user, or use the seeded users below.

### Step 7: Build Frontend Assets

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

### Step 8: Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000/admin` to access the admin panel.

## Seeded Users

The following users are created by the seeder (password: `password`):

| Email | Role | Description |
|-------|------|-------------|
| `admin@demo.test` | admin_examens | Full system access |
| `doyen@demo.test` | doyen | Can approve/reject planning runs |
| `chef@demo.test` | chef_dept | Department head access |
| `prof@demo.test` | prof | Professor access |
| `etudiant@demo.test` | etudiant | Student access |

## Project Structure

### Models

All Eloquent models are located in `app/Models/`:
- `Universite`, `Faculte`, `Departement`, `Formation`
- `Etudiant`, `Professeur`, `Module`
- `Salle`, `Equipement`, `SalleEquipement`
- `PeriodeExamen`, `Examen`, `SessionExamen`, `Surveillance`
- `Creneau`, `PlanningRun`, `PlanningItem`
- `Inscription`, `UsersMeta`

### Services

- `app/Services/PlanningOptimizerService.php`: Heuristic optimizer for generating schedules
- `app/Services/ConflictService.php`: Conflict detection and reporting

### Orchid Screens

Admin screens are organized in `app/Orchid/Screens/`:
- **Referentiel**: University, Faculty, Department, Formation, Module management
- **Examens**: Students, Professors, Rooms, Equipment, Periods, Enrollments
- **Planning**: Planning Runs, Planning Items, Conflict Dashboard

### Migrations

All database migrations are in `database/migrations/`:
- `2026_01_15_100000_create_universite_and_faculte_tables.php`
- `2026_01_15_100100_create_departement_and_formation_tables.php`
- `2026_01_15_100200_create_etudiant_and_professeur_tables.php`
- `2026_01_15_100300_create_module_and_inscription_tables.php`
- `2026_01_15_100400_create_equipement_and_salle_tables.php`
- `2026_01_15_100500_create_periode_examen_and_examen_related_tables.php`
- `2026_01_15_100600_create_creneau_planning_and_users_meta_tables.php`

### Seeders

Seeders are in `database/seeders/`:
- `DatabaseSeeder.php`: Main seeder that calls all others
- Individual seeders for each entity

## Usage Guide

### Planning Runs Workflow

1. **Create a Planning Run**
   - Navigate to Planning → Planning Runs
   - Click "Nouveau Run"
   - Select scope (global/departement/formation)
   - Select department/formation if applicable
   - Save

2. **Run Optimizer**
   - Open the planning run
   - Click "Lancer Optimiseur"
   - The system will generate a feasible schedule
   - View metrics and conflicts

3. **Submit to Dean**
   - Once optimization is complete
   - Click "Soumettre au Doyen"
   - Status changes to "submitted"

4. **Approve/Reject (Dean only)**
   - Dean can approve or reject the run
   - If rejected, provide a reason

5. **Publish (Admin only)**
   - After approval, admin can publish
   - Published runs are visible to all

### Conflict Detection

Navigate to Planning → Conflits to view:
- Room conflicts (double-booking)
- Student overlaps (same student, same time slot)
- Max exams per day violations
- Max surveillances per day violations

### View Planning Items

Navigate to Planning → Planning Items to see:
- Calendar-like view of all scheduled exams
- Filter by run, department, formation, date
- View room assignments and surveillants

## Database Schema Notes

### Key Constraints

- **UUID Generation**: `planning_runs` and `planning_items` use UUIDs (CHAR(36))
- **ENUM Types**: Used for `scope`, `status`, `type`, `role`, `niveau`, `statut`
- **JSON Columns**: `metrics` and `surveillants` stored as JSON
- **Composite Primary Keys**: `inscription`, `surveillance`, `salle_equipement`

### Indexes

All foreign keys and frequently queried columns are indexed for optimal performance.

## Development

### Running Tests

```bash
php artisan test
```

### Code Style

```bash
./vendor/bin/pint
```

## Troubleshooting

### Migration Issues

If you encounter migration errors:
```bash
php artisan migrate:fresh
php artisan db:seed
```

### Permission Issues

Ensure storage and bootstrap/cache directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
```

### Orchid Admin Not Loading

Clear cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and questions, please open an issue on the repository.
