# LifeLink — Blood Donation Management System

**Module:** XISD6329 · **Team:** Ayabonga Hadebe (ST10455760) · Gutshwa Magagula (ST10361206) · Karabo Mojapelo (ST10436116) · Hope Malatjie (ST10444867)

LifeLink is a dual-platform blood donation management system connecting registered
donors with hospitals and donation centres. It consists of this **PHP/MySQL website**
(donor portal + admin portal) and a companion **Kotlin Android app**, sharing one
REST API and MySQL database — book an appointment on the app and it shows up on the
website (and vice versa) instantly.

## 📁 Project Structure

```
lifelink-web/
├── index.php                 ← Landing page (live stats from the DB)
├── config/db.php             ← Database connection (edit if not using default XAMPP)
├── database/lifelink.sql     ← Full schema + seed/demo data — import this first
├── includes/                 ← Shared header/footer/auth-check partials
├── pages/                    ← Donor portal (login, register, dashboard, appointments…)
├── admin/                    ← Admin portal (dashboard, donors, hospitals, alerts…)
├── api/                      ← REST API consumed by the Android app
│   ├── auth.php              ← login / register
│   ├── donor.php             ← profile / donation history
│   ├── appointments.php      ← list / book / cancel
│   ├── hospitals.php         ← donation centres + live inventory
│   ├── alerts.php            ← active emergency blood requests
│   └── notifications.php     ← donor notifications
└── assets/                   ← Shared CSS/JS
```

## ✅ Status

Both the website and the Android app are **fully working prototypes** wired to the
same database — not just wireframes. This has been tested end-to-end (PHP + MySQL
spun up locally, every page and API endpoint exercised with real requests) before
being handed over.

Demo logins (also in `database/lifelink.sql`):
- **Donor:** `donor@lifelink.co.za` / `Donor@123`
- **Admin:** `admin@lifelink.co.za` / `Admin@123`

## 🚀 Getting it running

See the full step-by-step setup guide provided in the project chat (XAMPP install,
importing the database, running the website, pointing the Android app at it, and
troubleshooting).

## 🔜 Planned for Task 2 polish

- PHPUnit tests for the API layer; JUnit/Espresso tests for the Android app
- GitHub Actions / Azure Pipelines for automated build & deploy
- Password-reset flow, pagination on admin tables, hospital geolocation on a real map

## Team workflow

- Task board: Azure DevOps Kanban board (see the Task 1 project plan document)
- Branching: feature branches off `main`, merged via pull request
- Commit and push regularly as work progresses

---
*LifeLink Blood Donation · XISD6329*
