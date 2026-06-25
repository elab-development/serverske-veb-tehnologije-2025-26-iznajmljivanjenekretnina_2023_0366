<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @oa\Info(
 *     title="Real Estate API",
 *     version="1.0.0",
 *     description="REST API za aplikaciju za nekretnine. API koristi JSON, Sanctum Bearer tokene, javne eksterne API pozive i CSV eksport inquiry-ja."
 * )
 *
 * @oa\Server(
 *     url="/api",
 *     description="API base path"
 * )
 *
 * @oa\Tag(name="Auth", description="Registracija, login i logout")
 * @oa\Tag(name="Categories", description="Kategorije nekretnina")
 * @oa\Tag(name="Properties", description="Nekretnine, pretraga, filteri i upravljanje")
 * @oa\Tag(name="Inquiries", description="Upiti korisnika za nekretnine")
 * @oa\Tag(name="External", description="Javne rute koje pozivaju eksterne API-jeve")
 * @oa\Tag(name="Exports", description="Eksport podataka")
 *
 * @oa\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum token"
 * )
 *
 * @oa\Schema(
 *     schema="ErrorMessage",
 *     type="object",
 *     @oa\Property(property="message", type="string", example="Unauthorized")
 * )
 *
 * @oa\Schema(
 *     schema="User",
 *     type="object",
 *     @oa\Property(property="id", type="integer", example=1),
 *     @oa\Property(property="name", type="string", example="Mila Novak"),
 *     @oa\Property(property="email", type="string", format="email", example="mila.novak@example.com"),
 *     @oa\Property(property="role", type="string", enum={"admin","user"}, example="user")
 * )
 *
 * @oa\Schema(
 *     schema="Category",
 *     type="object",
 *     @oa\Property(property="id", type="integer", example=1),
 *     @oa\Property(property="name", type="string", example="Stan"),
 *     @oa\Property(property="description", type="string", nullable=true, example="Stambene jedinice u zgradama.")
 * )
 *
 * @oa\Schema(
 *     schema="Property",
 *     type="object",
 *     @oa\Property(property="id", type="integer", example=1),
 *     @oa\Property(property="category_id", type="integer", example=1),
 *     @oa\Property(property="title", type="string", example="Svetao trosoban stan kod Hrama"),
 *     @oa\Property(property="description", type="string"),
 *     @oa\Property(property="price", type="number", format="float", example=245000),
 *     @oa\Property(property="city", type="string", example="Beograd"),
 *     @oa\Property(property="address", type="string", example="Skerliceva 12"),
 *     @oa\Property(property="area", type="number", format="float", example=82.5),
 *     @oa\Property(property="rooms", type="number", format="float", nullable=true, example=3),
 *     @oa\Property(property="bathrooms", type="integer", nullable=true, example=1),
 *     @oa\Property(property="floor", type="string", nullable=true, example="3"),
 *     @oa\Property(property="total_floors", type="integer", nullable=true, example=6),
 *     @oa\Property(property="year_built", type="integer", nullable=true, example=2008),
 *     @oa\Property(property="listing_type", type="string", enum={"sale","rent"}, example="sale"),
 *     @oa\Property(property="status", type="string", enum={"draft","active","archived"}, example="active"),
 *     @oa\Property(property="is_featured", type="boolean", example=true),
 *     @oa\Property(property="published_at", type="string", format="date-time", nullable=true),
 *     @oa\Property(property="category", ref="#/components/schemas/Category", nullable=true)
 * )
 *
 * @oa\Schema(
 *     schema="Inquiry",
 *     type="object",
 *     @oa\Property(property="id", type="integer", example=1),
 *     @oa\Property(property="user_id", type="integer", example=2),
 *     @oa\Property(property="property_id", type="integer", example=1),
 *     @oa\Property(property="message", type="string", nullable=true),
 *     @oa\Property(property="phone", type="string", nullable=true),
 *     @oa\Property(property="preferred_date", type="string", format="date", nullable=true),
 *     @oa\Property(property="preferred_time", type="string", nullable=true),
 *     @oa\Property(property="status", type="string", enum={"new","contacted","scheduled","cancelled","closed"}),
 *     @oa\Property(property="admin_note", type="string", nullable=true),
 *     @oa\Property(property="user", ref="#/components/schemas/User", nullable=true),
 *     @oa\Property(property="property", ref="#/components/schemas/Property", nullable=true)
 * )
 * * @oa\Post(
 *     path="/register",
 *     tags={"Auth"},
 *     summary="Registracija korisnika",
 *     @oa\RequestBody(required=true, @oa\JsonContent(required={"name","email","password"}, @oa\Property(property="name", type="string"), @oa\Property(property="email", type="string", format="email"), @oa\Property(property="password", type="string", minLength=8))),
 *     @oa\Response(response=201, description="User registered"),
 *     @oa\Response(response=422, description="Validation error")
 * )
 * @oa\Post(
 *     path="/login",
 *     tags={"Auth"},
 *     summary="Login korisnika",
 *     @oa\RequestBody(required=true, @oa\JsonContent(required={"email","password"}, @oa\Property(property="email", type="string", format="email"), @oa\Property(property="password", type="string"))),
 *     @oa\Response(response=200, description="User logged in"),
 *     @oa\Response(response=401, description="Wrong credentials"),
 *     @oa\Response(response=422, description="Validation error")
 * )
 * @oa\Post(
 *     path="/logout",
 *     tags={"Auth"},
 *     summary="Logout korisnika",
 *     security={{"bearerAuth":{}}},
 *     @oa\Response(response=200, description="Logged out"),
 *     @oa\Response(response=401, description="Unauthenticated")
 * )
 *
 * @oa\Get(
 *     path="/external/geocode",
 *     tags={"External"},
 *     summary="Geocoding adrese",
 *     @oa\Parameter(name="address", in="query", required=true, @oa\Schema(type="string")),
 *     @oa\Parameter(name="limit", in="query", required=false, @oa\Schema(type="integer", minimum=1, maximum=5)),
 *     @oa\Response(response=200, description="Geocoding results"),
 *     @oa\Response(response=502, description="External service error"),
 *     @oa\Response(response=422, description="Validation error")
 * )
 * @oa\Get(
 *     path="/external/weather",
 *     tags={"External"},
 *     summary="Vremenska prognoza",
 *     @oa\Parameter(name="latitude", in="query", required=true, @oa\Schema(type="number", format="float")),
 *     @oa\Parameter(name="longitude", in="query", required=true, @oa\Schema(type="number", format="float")),
 *     @oa\Parameter(name="forecast_days", in="query", required=false, @oa\Schema(type="integer", minimum=1, maximum=16)),
 *     @oa\Parameter(name="timezone", in="query", required=false, @oa\Schema(type="string")),
 *     @oa\Response(response=200, description="Weather data"),
 *     @oa\Response(response=502, description="External service error"),
 *     @oa\Response(response=422, description="Validation error")
 * )
 * * @oa\Get(path="/categories", tags={"Categories"}, summary="Lista kategorija", @oa\Response(response=200, description="Categories list"))
 * @oa\Post(path="/categories", tags={"Categories"}, summary="Kreiranje kategorije", security={{"bearerAuth":{}}}, @oa\RequestBody(required=true, @oa\JsonContent(required={"name"}, @oa\Property(property="name", type="string"), @oa\Property(property="description", type="string", nullable=true))), @oa\Response(response=201, description="Category created"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=422, description="Validation error"))
 * @oa\Get(path="/categories/{category}", tags={"Categories"}, summary="Pregled jedne kategorije", @oa\Parameter(name="category", in="path", required=true, @oa\Schema(type="integer")), @oa\Response(response=200, description="Category details"), @oa\Response(response=404, description="Category not found"))
 * @oa\Put(path="/categories/{category}", tags={"Categories"}, summary="Azuriranje kategorije", security={{"bearerAuth":{}}}, @oa\Parameter(name="category", in="path", required=true, @oa\Schema(type="integer")), @oa\RequestBody(required=false, @oa\JsonContent(@oa\Property(property="name", type="string"), @oa\Property(property="description", type="string", nullable=true))), @oa\Response(response=200, description="Category updated"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=404, description="Category not found"), @oa\Response(response=422, description="Validation error"))
 * @oa\Patch(path="/categories/{category}", tags={"Categories"}, summary="Delimicno azuriranje kategorije", security={{"bearerAuth":{}}}, @oa\Parameter(name="category", in="path", required=true, @oa\Schema(type="integer")), @oa\RequestBody(required=false, @oa\JsonContent(@oa\Property(property="description", type="string", nullable=true))), @oa\Response(response=200, description="Category updated"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=404, description="Category not found"), @oa\Response(response=422, description="Validation error"))
 * @oa\Delete(path="/categories/{category}", tags={"Categories"}, summary="Brisanje kategorije", security={{"bearerAuth":{}}}, @oa\Parameter(name="category", in="path", required=true, @oa\Schema(type="integer")), @oa\Response(response=200, description="Category deleted"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=404, description="Category not found"))
 * @oa\Get(path="/categories/{category}/properties", tags={"Categories","Properties"}, summary="Nekretnine jedne kategorije", @oa\Parameter(name="category", in="path", required=true, @oa\Schema(type="integer")), @oa\Parameter(name="search", in="query", required=false, @oa\Schema(type="string")), @oa\Parameter(name="listing_type", in="query", required=false, @oa\Schema(type="string", enum={"sale","rent"})), @oa\Parameter(name="status", in="query", required=false, @oa\Schema(type="string", enum={"draft","active","archived"})), @oa\Parameter(name="city", in="query", required=false, @oa\Schema(type="string")), @oa\Parameter(name="sort_by", in="query", required=false, @oa\Schema(type="string")), @oa\Parameter(name="sort_direction", in="query", required=false, @oa\Schema(type="string", enum={"asc","desc"})), @oa\Parameter(name="per_page", in="query", required=false, @oa\Schema(type="integer")), @oa\Response(response=200, description="Paginated properties"), @oa\Response(response=404, description="Category not found"), @oa\Response(response=422, description="Validation error"))
 *
 * @oa\Get(path="/properties", tags={"Properties"}, summary="Lista nekretnina", @oa\Parameter(name="search", in="query", required=false, @oa\Schema(type="string")), @oa\Parameter(name="category_id", in="query", required=false, @oa\Schema(type="integer")), @oa\Parameter(name="listing_type", in="query", required=false, @oa\Schema(type="string", enum={"sale","rent"})), @oa\Parameter(name="status", in="query", required=false, @oa\Schema(type="string", enum={"draft","active","archived"})), @oa\Parameter(name="city", in="query", required=false, @oa\Schema(type="string")), @oa\Parameter(name="min_price", in="query", required=false, @oa\Schema(type="number")), @oa\Parameter(name="max_price", in="query", required=false, @oa\Schema(type="number")), @oa\Parameter(name="min_area", in="query", required=false, @oa\Schema(type="number")), @oa\Parameter(name="max_area", in="query", required=false, @oa\Schema(type="number")), @oa\Parameter(name="sort_by", in="query", required=false, @oa\Schema(type="string")), @oa\Parameter(name="sort_direction", in="query", required=false, @oa\Schema(type="string", enum={"asc","desc"})), @oa\Parameter(name="per_page", in="query", required=false, @oa\Schema(type="integer")), @oa\Response(response=200, description="Paginated properties"), @oa\Response(response=422, description="Validation error"))
 * @oa\Post(path="/properties", tags={"Properties"}, summary="Kreiranje nekretnine", security={{"bearerAuth":{}}}, @oa\RequestBody(required=true, @oa\JsonContent(ref="#/components/schemas/Property")), @oa\Response(response=201, description="Property created"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=422, description="Validation error"))
 * @oa\Get(path="/properties/{property}", tags={"Properties"}, summary="Pregled jedne nekretnine", @oa\Parameter(name="property", in="path", required=true, @oa\Schema(type="integer")), @oa\Response(response=200, description="Property details"), @oa\Response(response=404, description="Property not found"))
 * @oa\Put(path="/properties/{property}", tags={"Properties"}, summary="Azuriranje nekretnine", security={{"bearerAuth":{}}}, @oa\Parameter(name="property", in="path", required=true, @oa\Schema(type="integer")), @oa\RequestBody(required=false, @oa\JsonContent(ref="#/components/schemas/Property")), @oa\Response(response=200, description="Property updated"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=404, description="Property not found"), @oa\Response(response=422, description="Validation error"))
 * @oa\Patch(path="/properties/{property}", tags={"Properties"}, summary="Delimicno azuriranje nekretnine", security={{"bearerAuth":{}}}, @oa\Parameter(name="property", in="path", required=true, @oa\Schema(type="integer")), @oa\RequestBody(required=false, @oa\JsonContent(@oa\Property(property="status", type="string", enum={"draft","active","archived"}))), @oa\Response(response=200, description="Property updated"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=404, description="Property not found"), @oa\Response(response=422, description="Validation error"))
 * @oa\Delete(path="/properties/{property}", tags={"Properties"}, summary="Brisanje nekretnine", security={{"bearerAuth":{}}}, @oa\Parameter(name="property", in="path", required=true, @oa\Schema(type="integer")), @oa\Response(response=200, description="Property deleted"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=404, description="Property not found"))
 * @oa\Get(path="/properties/{property}/location", tags={"External","Properties"}, summary="Geocoding lokacije nekretnine", @oa\Parameter(name="property", in="path", required=true, @oa\Schema(type="integer")), @oa\Response(response=200, description="Property geocoding result"), @oa\Response(response=404, description="Property not found"), @oa\Response(response=502, description="External service error"))
 * @oa\Get(path="/properties/{property}/weather", tags={"External","Properties"}, summary="Vremenska prognoza za lokaciju nekretnine", @oa\Parameter(name="property", in="path", required=true, @oa\Schema(type="integer")), @oa\Response(response=200, description="Property weather data"), @oa\Response(response=404, description="Property or location not found"), @oa\Response(response=502, description="External service error"))
 * * @oa\Get(path="/inquiries", tags={"Inquiries"}, summary="Lista inquiry-ja", security={{"bearerAuth":{}}}, @oa\Parameter(name="property_id", in="query", required=false, @oa\Schema(type="integer")), @oa\Response(response=200, description="Inquiries list"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=422, description="Validation error"))
 * @oa\Post(path="/inquiries", tags={"Inquiries"}, summary="Kreiranje inquiry-ja", security={{"bearerAuth":{}}}, @oa\RequestBody(required=true, @oa\JsonContent(required={"property_id"}, @oa\Property(property="property_id", type="integer"), @oa\Property(property="message", type="string", nullable=true), @oa\Property(property="phone", type="string", nullable=true), @oa\Property(property="preferred_date", type="string", format="date", nullable=true), @oa\Property(property="preferred_time", type="string", nullable=true))), @oa\Response(response=201, description="Inquiry created"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=422, description="Validation error"))
 * @oa\Get(path="/inquiries/export", tags={"Exports","Inquiries"}, summary="CSV eksport inquiry-ja", security={{"bearerAuth":{}}}, @oa\Parameter(name="property_id", in="query", required=false, @oa\Schema(type="integer")), @oa\Parameter(name="status", in="query", required=false, @oa\Schema(type="string", enum={"new","contacted","scheduled","cancelled","closed"})), @oa\Response(response=200, description="CSV file", @oa\MediaType(mediaType="text/csv", @oa\Schema(type="string", example="id,user_id,user_name,user_email,property_id,property_title,property_city,property_address,category,status,message,phone,preferred_date,preferred_time,admin_note,created_at,updated_at"))), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=422, description="Validation error"))
 * @oa\Put(path="/inquiries/{inquiry}", tags={"Inquiries"}, summary="Azuriranje inquiry-ja", description="Samo admin moze azurirati status i admin_note.", security={{"bearerAuth":{}}}, @oa\Parameter(name="inquiry", in="path", required=true, @oa\Schema(type="integer")), @oa\RequestBody(required=false, @oa\JsonContent(@oa\Property(property="status", type="string", enum={"new","contacted","scheduled","cancelled","closed"}), @oa\Property(property="admin_note", type="string", nullable=true))), @oa\Response(response=200, description="Inquiry updated"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=404, description="Inquiry not found"), @oa\Response(response=422, description="Validation error"))
 * @oa\Patch(path="/inquiries/{inquiry}", tags={"Inquiries"}, summary="Delimicno azuriranje inquiry-ja", security={{"bearerAuth":{}}}, @oa\Parameter(name="inquiry", in="path", required=true, @oa\Schema(type="integer")), @oa\RequestBody(required=false, @oa\JsonContent(@oa\Property(property="admin_note", type="string", nullable=true))), @oa\Response(response=200, description="Inquiry updated"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=403, description="Unauthorized"), @oa\Response(response=404, description="Inquiry not found"), @oa\Response(response=422, description="Validation error"))
 * @oa\Get(path="/properties/{property}/inquiries", tags={"Properties","Inquiries"}, summary="Inquiry-ji jedne nekretnine", security={{"bearerAuth":{}}}, @oa\Parameter(name="property", in="path", required=true, @oa\Schema(type="integer")), @oa\Response(response=200, description="Property inquiries"), @oa\Response(response=401, description="Unauthenticated"), @oa\Response(response=404, description="Property not found"))
 */
class ApiDoc extends Controller
{
}