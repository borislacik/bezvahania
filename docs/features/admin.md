# Administrácia (Filament 5)

Beží na `ADMIN_DOMAIN` (lokálne http://admin.bezvahania.localhost), na koreni subdomény. Posiela
`X-Robots-Tag: noindex, nofollow`. Registrácia neexistuje, administrátora vytvára `php artisan app:create-admin`.
Dvojfaktorové overenie (aplikácia, TOTP, s obnovovacími kódmi) je povinné, ak `ADMIN_MFA_REQUIRED=true`
(produkcia). Každý admin si ho môže zapnúť v profile.

## Menu
| Sekcia | Čo sa tu robí |
|---|---|
| **Nástenka** | počet budúcich rezervácií, workshopy tento mesiac, nevybavené správy, najbližšie workshopy |
| Rezervácie → **Rezervácie** | zoznam (predvolene budúce), filtre podľa stavu, workshopu a dátumu, detail, úprava kontaktu, telefonická rezervácia, akcie *Zrušiť* (s dôvodom, e-mail zákazníkovi), *Uskutočnená*, *Neuskutočnená*, **export CSV** (aktuálny filter, UTF-8, oddeľovač `;` pre Excel) |
| Rezervácie → **Dostupnosť termínov** | mesačný kalendár; kliknutím deň zablokujete / uvoľníte. Deň s rezerváciou zablokovať nejde. |
| Rezervácie → **Správy z webu** | správy z kontaktného formulára, označenie „vybavená“ |
| Obsah webu → **Workshopy** | názov, adresa, štítok, cena, trvanie, vybavenie, popisy, obrázok, poradie, viditeľnosť, SEO |
| Obsah webu → **Lektori** | meno, funkcia, citát, text, fotografia |
| Obsah webu → **Časté otázky**, **Referencie** | texty, poradie (ťahaním), viditeľnosť |
| Obsah webu → **Stránky a SEO** | SEO titulok, popis a obrázok pri zdieľaní pre každú stránku; texty právnych stránok |
| Nastavenia → **Kontakt a prevádzkovateľ** | telefón, e-mail na webe, e-mail pre upozornenia, údaje živnosti, najskorší termín rezervácie |

## Obrázky
Všetky nahrávané obrázky (JPG, PNG, WebP, GIF, AVIF, max. 15 MB) sa automaticky **konvertujú na WebP**
a zmenšia na šírku max. 1920 px (`App\Support\WebpImage`, `App\Filament\Support\WebpUpload`).
Pozri [seo-and-images.md](seo-and-images.md).

## Preložiteľný obsah
Textové polia workshopov, lektorov, FAQ, referencií a stránok sú uložené ako preklady
(`spatie/laravel-translatable`). Formuláre upravujú slovenčinu (`App\Filament\Support\Translatable`).
Pri pridaní ďalšieho jazyka pribudne vo formulároch prepínač jazyka.

## Poznámky k implementácii
- Widgety nástenky sa nenačítavajú „lazy“ – súbežné načítanie spôsobovalo odhlásenie (session v databáze).
- Vlastné stránky adminu (`app/Filament/Pages`) používajú tému `resources/css/filament/admin/theme.css`.
