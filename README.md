SkyMay – Campus Event Social Platform

SkyMay is a campus event sharing platform that brings workshops, competitions, club activities, talks, and career sessions into one place.

Project Overview

Students often miss campus opportunities because event information is spread across social media, messaging apps, and email groups. SkyMay provides a central platform where students can discover events, register for activities, and interact with organizers.

Objectives

Improve event visibility for students

Make event publishing easier for organizers

Allow students to discover and join events

Support comments and user interaction

Provide participation insights for administrators

Manage the platform using role-based access

Target Users

Students

Browse and discover events

View event details

Register for events

Interact through comments and likes

Organizers

Create and publish events

Manage event information

Receive feedback from participants

Monitor event participation

Administrators

Manage users and platform content

Monitor platform activity

Maintain platform quality and security

System Architecture

Frontend: HTML, CSS, JavaScript
Backend: PHP
Database: MySQL (skymay_db)

The system uses role-based access, secure sessions, dynamic event loading, and structured database relationships for users, events, comments, likes, and registrations.

Main Features

User registration and login

Role-based access control

Event creation and publishing

Event browsing and event details

Event registration

Comments and likes

Organizer dashboard

Admin management

Contact and messaging functions

Project Structure

SkyMay-Campus-Event-Social-Platform/
├── assets/
├── includes/
├── uploads/
├── about.php
├── admin.php
├── contact.php
├── contact_messages.php
├── createevent.php
├── db.php
├── event.php
├── eventdetail.php
├── faq.php
├── get_user_registrations.php
├── homepage.php
└── README.md

Requirements

To run the project locally, you will need:

PHP

MySQL

Apache server (such as XAMPP)

A web browser

Setup

Clone or download this repository.

Place the project folder inside your web server directory, such as htdocs in XAMPP.

Start Apache and MySQL.

Create a MySQL database named skymay_db.

Import the required database tables/data.

Check the database settings in db.php.

Open the project through your local server.

Example:

http://localhost/SkyMay-Campus-Event-Social-Platform/

Project Purpose

SkyMay aims to make campus event discovery and participation easier by providing students, organizers, and administrators with a single platform for managing and engaging with campus activities.

Author

Ryan Tee (Tee Hui Huang)

This project was developed as a campus event social platform project.
