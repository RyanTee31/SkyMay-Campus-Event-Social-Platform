# SkyMay - Campus Event Social Platform

SkyMay is a campus event social platform that helps students discover and join events, while allowing organizers to publish events and manage participation.

## About the Project

SkyMay brings campus announcements, events, social interaction, and participation tracking into one platform.

### Project Background

Students often miss campus opportunities because information is spread across social media, messaging apps, and email groups. SkyMay provides a central platform where students can easily discover and interact with campus events.

## Objectives

- Improve event visibility for students
- Make event publishing easier for organizers
- Encourage student interaction and participation
- Provide useful participation insights for administrators
- Support better management of the campus event community

## Target Users

### Students
- Discover upcoming events
- View event details
- Join events
- Interact with other users
- Track participation

### Organizers
- Create and publish events
- Manage event information
- Receive audience feedback through comments
- Monitor event participation

### Administrators
- Manage and moderate the platform
- Monitor platform activity
- View useful platform metrics
- Maintain platform quality

## Main Features

- User registration and login
- Role-based access for Students, Organizers, and Admins
- Event creation and management
- Event discovery and event details
- Event registration
- Comments and likes
- User and event management
- Admin dashboard and monitoring

## System Architecture

**Frontend:** **HTML, CSS, JavaScript**  
**Backend:** **PHP**  
**Database:** **MySQL** (`skymay_db`)

The system uses role-based access, secure session handling, dynamic event loading, and structured database relationships for users, events, comments, likes, and registrations.

## Project Structure

```text
SkyMay-Campus-Event-Social-Platform/
├── assets/
├── includes/
├── uploads/
├── about.php
├── admin.php
├── contact.php
├── createevent.php
├── db.php
├── event.php
├── eventdetail.php
├── homepage.php
└── README.md
```

## How to Run

1. Install a local PHP and MySQL environment such as XAMPP.
2. Copy the project folder into the XAMPP `htdocs` folder.
3. Start **Apache** and **MySQL**.
4. Create a MySQL database named `skymay_db`.
5. Import the project database into MySQL.
6. Check the database settings in `db.php`.
7. Open the project in your browser:

```text
http://localhost/SkyMay-Campus-Event-Social-Platform/
```

## Project Value

SkyMay provides:

- **Student Value:** **Easy event discovery, interaction, and participation tracking.**
- **Organizer Value:** **Simple event publishing and audience feedback.**
- **Admin Value:** **Central moderation and platform monitoring.**

## Future Improvements

- Email notifications for event updates
- Mobile responsive improvements
- Advanced event search and filtering
- Event reminders
- More detailed analytics
- Improved security and user authentication

## Author

**Tee Hui Huang (Ryan)**

SkyMay - Campus Event Social Platform
