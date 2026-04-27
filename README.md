# Hospital Management System

A comprehensive Hospital Management System built with PHP.

## Overview

This project is a web-based application designed to manage various aspects of a hospital, including patient registration, doctor appointments, medical records, and administrative tasks.

## Key Features

*   **Role-Based Access Control:** Separate portals and functionalities for Admin, Doctors, Patients, and Medical staff.
*   **Patient Management:** Patient registration, login, and profile management.
*   **Doctor Management:** Doctor scheduling and appointment handling.
*   **Medical Records:** Tracking and managing patient medical history.
*   **Communication:** Integrated chatbot (`chatbot.php`) and email functionality using PHPMailer.
*   **PDF Generation:** Uses TCPDF to generate printable reports and prescriptions.
*   **Authentication:** Secure login (`login.php`) and registration (`register.php`) system.

## Project Structure

*   `/admin`: Administrative panel and functionalities.
*   `/doctor`: Doctor portal for managing appointments and patient records.
*   `/patient`: Patient portal for booking appointments and viewing history.
*   `/medical`: Pharmacy or medical laboratory management.
*   `/config`: Database connection and configuration files.
*   `/assets`, `/css`, `/js`: Static assets, stylesheets, and scripts.
*   `/PHPMailer`, `/tcpdf`: Third-party libraries used in the project.

## Requirements

*   PHP 7.4 or higher
*   MySQL Database
*   Web Server (Apache/Nginx)

## Setup Instructions

1.  **Clone the repository** to your local web server's document root (e.g., `htdocs` or `www`).
2.  **Database Configuration:**
    *   Create a new MySQL database.
    *   Import the provided SQL file (if available) into your new database.
    *   Update the database connection settings in the `/config` directory to match your environment.
3.  **Start your web server** and access the project via your web browser (e.g., `http://localhost/hospital`).

## Contact

For any inquiries or issues, please refer to the `contact.php` page.
