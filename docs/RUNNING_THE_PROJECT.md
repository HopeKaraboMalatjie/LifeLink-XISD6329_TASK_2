# Running LifeLink — Website + Android App Together

This gets the **website** (PHP/MySQL) and the **Android app** (Kotlin) running
side by side, sharing one database, so anything a donor does in one place shows
up in the other.

**Demo logins** (already seeded in the database):
- Donor — `donor@lifelink.co.za` / `Donor@123`
- Admin — `admin@lifelink.co.za` / `Admin@123`

---

## Part 1 — Website (PHP/MySQL via XAMPP)

### 1. Install XAMPP
Download from [apachefriends.org](https://www.apachefriends.org/) and install it
(Windows/Mac/Linux all work). Open the **XAMPP Control Panel** and click **Start**
next to both **Apache** and **MySQL**.

### 2. Copy the website files
Unzip `lifelink-web.zip`. Copy the whole `lifelink-web` folder into XAMPP's
`htdocs` directory, and rename it to **`lifelink`**:

- Windows: `C:\xampp\htdocs\lifelink\`
- Mac: `/Applications/XAMPP/htdocs/lifelink/`
- Linux: `/opt/lampp/htdocs/lifelink/`

The folder name **must** be `lifelink` — the Android app is already configured
to call `http://10.0.2.2/lifelink/api/...`.

### 3. Import the database
Open **phpMyAdmin** (click "Admin" next to MySQL in XAMPP, or go to
`http://localhost/phpmyadmin`):
1. Click **Import** in the top menu
2. Choose the file `lifelink/database/lifelink.sql`
3. Click **Go**

This creates the `lifelink_db` database with all 8 tables and demo data
(2 donors, 3 hospitals, inventory, alerts, sample appointments).

> **Command-line alternative**, from a terminal:
> ```
> mysql -u root -p < lifelink.sql
> ```
> (default XAMPP MySQL root password is empty — just press Enter)

### 4. Check the database connection settings
Open `lifelink/config/db.php`. The defaults match a fresh XAMPP install
(`localhost`, user `root`, no password). If your MySQL is set up differently,
edit the constants at the top of that file.

### 5. Open the website
Go to **`http://localhost/lifelink/`** in your browser. You should see the
LifeLink landing page with live donor/hospital counts pulled from the database.

Try it:
- Register a new donor, or log in with `donor@lifelink.co.za` / `Donor@123`
- Book an appointment — it'll appear on the Dashboard and Appointments page
- Visit `http://localhost/lifelink/admin/` and log in with the admin account
  to see it on the admin side too (Appointments page), and try editing
  blood inventory numbers on the admin Dashboard

If you see a **"Database connection failed"** message, Apache/MySQL aren't
running, or the database wasn't imported — recheck steps 1 and 3.

---

## Part 2 — Android App (Android Studio)

### 1. Open the project
Unzip `lifelink-android.zip` and open the `lifelink-android` folder in
**Android Studio** (File → Open). Let Gradle sync — this can take a few minutes
the first time.

### 2. Point the app at your website
The app talks to the API at `http://10.0.2.2/lifelink/api/` — this is a special
Android-emulator address that means **"the computer running the emulator"**, so
if you followed Part 1 exactly (folder named `lifelink`), you don't need to
change anything to test on the **emulator**.

**Testing on a real phone instead?** Your phone can't reach `10.0.2.2`. Find your
computer's local network IP (Windows: `ipconfig`, Mac/Linux: `ifconfig`) — e.g.
`192.168.1.42` — and update `app/build.gradle`:
```groovy
buildConfigField "String", "BASE_URL", '"http://192.168.1.42/lifelink/api/"'
```
Your phone and computer must be on the same Wi-Fi network, and your computer's
firewall must allow incoming connections to Apache (port 80).

### 3. Run it
Click the green ▶ Run button in Android Studio, choose an emulator (or your
connected phone), and wait for it to install and launch.

Try it:
- Log in with `donor@lifelink.co.za` / `Donor@123` (same account as the website!)
- Check the Dashboard — it shows the same appointments and alerts you'd see on
  the website, because both read from the same `lifelink_db` database
- Book an appointment on the app, then refresh the website's Appointments page
  — it's there

---

## Troubleshooting

| Problem | Fix |
|---|---|
| Website shows "Database connection failed" | Make sure MySQL is started in XAMPP and you imported `lifelink.sql` |
| Website shows a blank page / PHP error | Check the folder is named exactly `lifelink` in `htdocs`, and that PHP 8.x is what XAMPP is running |
| App shows "Network error" / can't log in | Emulator: confirm Apache is running and the folder is named `lifelink`. Real phone: check the IP address and that both devices are on the same network |
| App login says "Invalid email or password" but website login works | Double-check you typed the demo password exactly: `Donor@123` (capital D, `@`, no trailing space) |
| Changes on one platform don't show on the other | Both must point at the **same** `lifelink_db` — if you reimported the SQL file or changed `config/db.php`, restart both |
| Want to reset all demo data | Re-import `database/lifelink.sql` in phpMyAdmin (it drops and recreates every table) |

---

## What's connected to what

```
Android App  ──HTTP/JSON──▶  lifelink/api/*.php  ──▶  MySQL: lifelink_db
                                                            ▲
Website (browser) ───────────────────────────────────────┘
  lifelink/pages/*.php, lifelink/admin/*.php
```

The website's donor pages and the Android app use **the same tables** but two
different code paths (website: PHP sessions + direct queries; app: REST API in
`api/*.php`) — both were built and tested against the exact same schema, so a
donor's data, blood type, appointments and notifications are always consistent
no matter which platform they use.
