# LifeLink — Blood Donation Management System

**Module:** XISD6329 — Task 1
**Team:** Ayabonga Hadebe (ST10455760) · Gutshwa Magagula (ST10361206) · Karabo Mojapelo (ST10436116) · Hope Malatjie (ST10444867)

LifeLink is a dual-platform blood donation management system connecting registered
donors with hospitals and donation centres. It consists of a PHP/MySQL website
(donor portal + admin portal) and a companion Kotlin Android application, sharing
a REST API and MySQL database.

This repository holds the **website** front end for the project. The Task 1
submission document (updated project plan, site map and wireframes) is
submitted separately as `LifeLink_Task1_ProjectPlan_Sitemap_Wireframes.docx`.

## 📁 Project Structure

```
lifelink-web/
├── index.html              ← Landing page
├── pages/
│   ├── login.html
│   ├── register.html
│   ├── dashboard.html
│   ├── appointments.html
│   ├── book_appointment.html
│   ├── hospitals.html
│   └── profile.html
├── admin/
│   ├── login.html
│   └── dashboard.html
└── assets/
    ├── css/style.css
    ├── js/app.js
    └── img/
```

## 🚧 Status — Task 1

These are static HTML wireframe-accurate prototypes of the website, built to
confirm layout and navigation with the organisation before coding the working
PHP/MySQL application in Task 2. Forms do not submit anywhere yet; there is no
database connection.

## 🔜 Planned for Task 2

- Convert pages to PHP, wire up to `includes/db.php` and the MySQL schema
- Build the REST API (`api/auth.php`, `api/donor.php`, `api/appointments.php`,
  `api/hospitals.php`, `api/alerts.php`, `api/notifications.php`)
- Connect the Android app (Kotlin/Retrofit) to the same API
- Add PHPUnit tests for the API and JUnit/Espresso tests for the Android app
- Automate build/test/deploy via GitHub Actions or Azure Pipelines

## Team workflow

- Task board: Azure DevOps Kanban board (see project plan document)
- Branching: feature branches off `main`, merged via pull request
- Commit regularly and push progress throughout Task 1 and Task 2

---
*LifeLink Blood Donation · XISD6329 · Task 1*
