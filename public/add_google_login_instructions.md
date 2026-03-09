# Instructions for Enabling Google Login

## 1. Database Update

Since the automatic update script could not connect to your local database, please run the following SQL commands in your database manager (phpMyAdmin, HeidiSQL, etc.) to add the `email_poltek` column:

```sql
-- Add email_poltek to Lecturer table
ALTER TABLE wsia_dosen ADD COLUMN email_poltek VARCHAR(100) DEFAULT NULL AFTER nm_ptk;
ALTER TABLE wsia_dosen ADD INDEX idx_email_poltek (email_poltek);

-- Add email_poltek to Student table
ALTER TABLE wsia_mahasiswa ADD COLUMN email_poltek VARCHAR(100) DEFAULT NULL AFTER nm_pd;
ALTER TABLE wsia_mahasiswa ADD INDEX idx_email_poltek (email_poltek);
```

## 2. Google Cloud Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project or select an existing one.
3. specific "APIs & Services" > "Credentials".
4. Create "OAuth client ID" for "Web application".
5. Add your domain (e.g., `http://localhost`, `https://siakad.indonusa.ac.id`) to Authorized JavaScript origins.
6. Copy the **Client ID**.

## 3. Configuration

Open `public/dosen/halaman/login.php` and `public/mhs/halaman/login.php`.
Find the placeholder `YOUR_GOOGLE_CLIENT_ID_HERE` and replace it with your actual Client ID.

```html
<div id="g_id_onload" data-client_id="YOUR_GOOGLE_CLIENT_ID_HERE" ...></div>
```

## 4. User Data

Manually update the `email_poltek` column in `wsia_dosen` and `wsia_mahasiswa` for the users you want to allow to login via Google. The email must match their Google account email exactly.
