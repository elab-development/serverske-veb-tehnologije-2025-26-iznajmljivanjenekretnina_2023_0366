# Real Estate API

Laravel 12 API backend za aplikaciju za nekretnine. Projekat pokriva registraciju i prijavu korisnika, role korisnika, kategorije nekretnina, nekretnine, upite za zakazivanje pregleda, javne eksterne API pozive, CSV eksport upita i Swagger dokumentaciju.

## Tehnologije

- PHP 8.2+
- Laravel 12
- Laravel Sanctum
- MySQL
- Pest/PHPUnit testovi
- darkaonline/l5-swagger

## Povlacenje projekta

Kloniraj repozitorijum i udji u folder projekta:

```bash
git clone <repository-url>
cd real_estate
```

Instaliraj PHP zavisnosti:

```bash
composer install
```

Ako zelis da koristis Vite/Laravel frontend alatke koje dolaze uz Laravel skeleton:

```bash
npm install
```

## Podesavanje lokalnog okruzenja

Kopiraj `.env.example` u `.env`:

```bash
cp .env.example .env
```

Na Windows PowerShell-u mozes koristiti:

```powershell
Copy-Item .env.example .env
```

Generisi aplikacioni kljuc:

```bash
php artisan key:generate
```

U `.env` podesi konekciju ka lokalnoj MySQL bazi. Podrazumevano je:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=real_estate
DB_USERNAME=root
DB_PASSWORD=
```

Pre migracija napravi bazu `real_estate` u MySQL-u.

## Migracije i seed podaci

Pokreni migracije:

```bash
php artisan migrate
```

Popuni bazu pocetnim podacima:

```bash
php artisan db:seed
```

Ili sve odjednom za svezu lokalnu bazu:

```bash
php artisan migrate:fresh --seed
```

Seeder kreira admin korisnika, nekoliko obicnih korisnika, realne kategorije, realne nekretnine i upite, a zatim dodaje jos podataka kroz factory-je.

Seed korisnici imaju lozinku:

```text
password
```

Primer admin naloga:

```text
admin@realestate.com
```

## Pokretanje aplikacije

Pokreni Laravel server:

```bash
php artisan serve
```

Aplikacija ce biti dostupna na:

```text
http://127.0.0.1:8000
```

Ako je port 8000 zauzet drugim projektom, koristi drugi port:

```bash
php artisan serve --port=8001
```

API rute su pod `/api`, na primer:

```text
http://127.0.0.1:8000/api/properties
```

## Swagger dokumentacija

Projekat koristi `darkaonline/l5-swagger`.

Generisi OpenAPI dokumentaciju:

```bash
php artisan l5-swagger:generate
```

Swagger UI se otvara na:

```text
http://127.0.0.1:8000/api/documentation
```

Raw OpenAPI JSON je dostupan na:

```text
http://127.0.0.1:8000/docs
```

Za autorizovane rute prvo pozovi `/api/login` ili `/api/register`, kopiraj `access_token`, pa u Swagger UI klikni `Authorize` i unesi token u formatu:

```text
Bearer <token>
```

## Testovi

Pokretanje svih testova:

```bash
php artisan test
```

## Glavne funkcionalnosti

### Autentifikacija

- Registracija korisnika
- Login korisnika
- Logout korisnika
- Sanctum Bearer token autentifikacija
- Role korisnika: `admin`, `user`

Registracija uvek kreira obicnog korisnika sa rolom `user`. Admin korisnik se kreira kroz seeder.

### Kategorije

- Javni pregled svih kategorija
- Javni pregled jedne kategorije
- Javni pregled nekretnina po kategoriji
- Kreiranje, azuriranje i brisanje kategorija samo za admin korisnika
- Nema dodatnih filtera, pretrage, sortiranja ni paginacije

Kategorija ima naziv i opis.

### Nekretnine

- Javni pregled nekretnina
- Javni pregled jedne nekretnine
- Javni pregled lokacije nekretnine preko eksternog geocoding API-ja
- Javni pregled vremenske prognoze za lokaciju nekretnine
- Kreiranje, azuriranje i brisanje nekretnina samo za admin korisnika
- Pregled liste podrzava pretragu, filtere, sortiranje i paginaciju
- Nekretnina pripada jednoj kategoriji
- Nekretnina nema vezu ka korisniku koji ju je kreirao

Tipovi oglasa:

```text
sale
rent
```

Statusi nekretnine:

```text
draft
active
archived
```

Podrzani filteri za listu nekretnina:

```text
search
category_id
listing_type
status
city
min_price
max_price
min_area
max_area
sort_by
sort_direction
per_page
page
```

Podrzana polja za sortiranje:

```text
title
price
area
city
listing_type
status
published_at
created_at
updated_at
```

### Upiti za pregled nekretnine

Upit moze da kreira samo ulogovani korisnik sa rolom `user`. Admin ne kreira upite, vec ih pregleda i azurira.

- Korisnik vidi samo svoje upite
- Admin vidi sve upite
- Admin moze da filtrira upite po nekretnini
- Admin moze da azurira samo `status` i `admin_note`
- Korisnik moze da kreira upit za konkretnu nekretninu
- Pregled jednog upita i brisanje upita nisu predvidjeni
- Jedan korisnik moze imati samo jedan upit sa istim statusom za istu nekretninu

Statusi upita:

```text
new
contacted
scheduled
cancelled
closed
```

Podrzani filteri za listu upita:

```text
property_id
```

### Javni eksterni API-jevi

Projekat ima javne rute koje ne zahtevaju autentifikaciju i pozivaju eksterne API servise bez API kljuca.

Geocoding:

```text
GET /api/external/geocode
GET /api/properties/{property}/location
```

Ruta poziva Nominatim / OpenStreetMap API. Podrzani query parametri za direktan geocoding:

```text
address
limit
```

Primer:

```text
/api/external/geocode?address=Skerliceva%2012,%20Beograd&limit=1
```

Vremenska prognoza:

```text
GET /api/external/weather
GET /api/properties/{property}/weather
```

Ruta poziva Open-Meteo API. Podrzani query parametri za direktnu prognozu:

```text
latitude
longitude
forecast_days
timezone
```

Primer:

```text
/api/external/weather?latitude=44.8125&longitude=20.4612&forecast_days=7
```

### CSV eksport

Ruta:

```text
GET /api/inquiries/export
```

Preuzima CSV fajl sa podacima o upitima. CSV sadrzi korisnika, nekretninu, kategoriju, status, poruku, telefon, zeljeni datum i vreme, admin napomenu i datume kreiranja/azuriranja.

Eksport postuje prava pristupa:

- Admin eksportuje sve upite
- Obican korisnik eksportuje samo svoje upite

Podrzani filteri:

```text
property_id
status
```

## Pregled glavnih ruta

```text
POST      /api/register
POST      /api/login
POST      /api/logout

GET       /api/external/geocode
GET       /api/external/weather

GET       /api/categories
POST      /api/categories
GET       /api/categories/{category}
PUT/PATCH /api/categories/{category}
DELETE    /api/categories/{category}
GET       /api/categories/{category}/properties

GET       /api/properties
POST      /api/properties
GET       /api/properties/{property}
PUT/PATCH /api/properties/{property}
DELETE    /api/properties/{property}
GET       /api/properties/{property}/location
GET       /api/properties/{property}/weather
GET       /api/properties/{property}/inquiries

GET       /api/inquiries
POST      /api/inquiries
PUT/PATCH /api/inquiries/{inquiry}
GET       /api/inquiries/export
```

## Korisne komande

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan l5-swagger:generate
php artisan serve
php artisan test
```
