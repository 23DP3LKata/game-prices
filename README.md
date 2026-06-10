# Game Prices

**Game Prices** ir pilna steka tīmekļa lietotne, kas ļauj lietotājiem sekot līdzi videospēļu cenām dažādos tiešsaistes veikalos, salīdzināt piedāvājumus, veidot vēlmju sarakstu un saņemt paziņojumus par cenu izmaiņām.

## Funkcionalitāte

### Lietotājiem

* Spēļu meklēšana un pārlūkošana
* Cenu salīdzināšana starp dažādiem veikaliem
* Aktuālo un minimālo cenu apskate
* Personīgais vēlmju saraksts (Wishlist)
* Paziņojumi par cenu samazināšanos
* Reģistrācija un autorizācija
* E-pasta verifikācija
* Paroles atjaunošana
* Lietotāja profila pārvaldība

### Administratoriem

* Manuāla cenu sinhronizācija
* Sinhronizācijas žurnālu apskate
* Administrēšanas panelis
* Lietotāju pārvaldība

### Automatizācija

* Automātiska cenu atjaunošana ik pēc 12 stundām
* Laravel Scheduler uzdevumi
* Automātiski e-pasta paziņojumi

## Izmantotās tehnoloģijas

### Frontend

* Vue.js 3
* Vue Router
* Vite
* Axios
* JavaScript
* HTML5
* CSS3

### Backend

* Laravel 12
* PHP 8.4
* REST API
* Laravel Scheduler
* Laravel Mail

### Datubāze

* MySQL 8

### Servera infrastruktūra

* Ubuntu Server 24.04
* Nginx
* PHP-FPM
* Cron
* SSL/TLS (Let's Encrypt)

### E-pastu serviss

* Mailtrap SMTP

## Sistēmas arhitektūra

```text
Vue.js Frontend
        ↓
 Laravel REST API
        ↓
    MySQL
```

Lietotāja saskarne ir izstrādāta ar Vue.js, savukārt servera daļa un API ir veidota ar Laravel. Datu glabāšanai tiek izmantota MySQL datubāze.

## Projekta struktūra

```text
game-prices/
├── backend/      # Laravel API
├── frontend/     # Vue.js lietotne
└── README.md
```

## Galvenās iespējas

### Spēļu cenu uzraudzība

Lietotāji var apskatīt dažādu spēļu cenas vairākos digitālajos veikalos vienuviet.

### Vēlmju saraksts

Katrs lietotājs var pievienot interesējošās spēles vēlmju sarakstam un sekot cenu izmaiņām.

### Paziņojumu sistēma

Sistēma automātiski nosūta e-pasta paziņojumus, ja spēles cena samazinās.

## Uzstādīšana

### Backend

```bash
cd backend

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan optimize
```

### Frontend

```bash
cd frontend

npm install

npm run build
```

## Palaišana izstrādes režīmā

Backend:

```bash
php artisan serve
```

Frontend:

```bash
npm run dev
```

## Produkcijas vide

Projekts ir izvietots uz VPS servera ar šādu konfigurāciju:

* Ubuntu 24.04
* Nginx
* PHP 8.4
* MySQL 8
* Let's Encrypt SSL sertifikāti
* Cron uzdevumi
* Laravel Scheduler

## Automātiskie uzdevumi

Cenu sinhronizācija tiek veikta automātiski ik pēc 12 stundām:

```bash
php artisan itad:sync-prices
```

Laravel Scheduler tiek izpildīts katru minūti ar Cron palīdzību.

## Drošība

* HTTPS šifrēšana
* Paroļu šifrēšana
* CSRF aizsardzība
* E-pasta verifikācija
* Drošas lietotāju sesijas
* Datu validācija
* Aizsargāti API maršruti

## Nākotnes uzlabojumi

* Papildu spēļu veikalu integrācijas
* Paplašināta statistika
* Push paziņojumi
* Mobilā lietotne
* Papildu valodu atbalsts

## Autors

Projekts izstrādāts kā pilna steka tīmekļa lietotne, izmantojot Laravel un Vue.js tehnoloģijas.

## Licence

Projekts paredzēts mācību, demonstrācijas un portfolio vajadzībām.
