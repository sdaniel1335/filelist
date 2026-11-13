# Filelist 0.1.0

Egyszerű, saját szerveren futó fájlkezelő a Todolist, Playlist és Lifelist mellé, azonos jellegű belépéssel és jelszócserével. A VC Framework és a közös topbar változatlan.

## Használat

- Egyszerre egy fájl tölthető fel a B, P vagy W csoportba, legfeljebb 25 MB méretben.
- A kezdőlapon csak azok a csoportok látszanak külön táblázatként, amelyekben van saját fájl.
- A fájlok letölthetők, átnevezhetők és véglegesen törölhetők. Az átnevezés a teljes fájlnevet érinti, a kiterjesztéssel együtt.
- Minden felhasználó csak a saját fájljait látja és kezelheti. Azonos nevű fájlok is feltölthetők, nincs felülírás.

## Telepítés

1. PHP 5.3–8.x, MySQL/MariaDB, MySQLi és HTTPS szükséges. Régi PHP-verzió helyett támogatott PHP-verzió ajánlott. A biztonságos véletlengeneráláshoz OpenSSL vagy `/dev/urandom` kell; ezek nélkül az alkalmazás nem készít gyenge tokent.
2. Másold a `private_html/app/config/db-sample.php` fájlt `db.php` néven, és állítsd be a kapcsolatot, lehetőleg `DB_CHAR = utf8mb4` értékkel.
3. Importáld az `install.sql` fájlt egy külön adatbázisba. Ha `DB_PREFIX` értéket használsz, az SQL-ben is add hozzá a táblanevekhez. A meglévő appok adatbázisát ne írd felül.
4. A `private_html/app/config/app.php` fájlban állítsd be a `FILE_STORAGE_PATH` értékét egy abszolút elérési útra **a teljes webes document rooton kívül**. Például ha a webgyökér `/home/user/domains/example.hu/public_html`, a tároló lehet `/home/user/filelist-storage`. Az alapértelmezett útvonal a tényleges document root szülőkönyvtárában lévő `filelist-storage`; `/home/danielco/public_html` webgyökér esetén `/home/danielco/filelist-storage`. A program ellenőrzi ezt, és publikus tárolóval nem enged fájlműveletet. A könyvtárat a PHP-t futtató felhasználó írhatja/olvashatja; más rendszerfelhasználónak ne adj hozzáférést. Linuxon a létrehozási jogosultság könyvtárnál 0700, fájlnál 0600; Windowson állíts be megfelelő NTFS-jogosultságokat.
5. A szerveren állítsd a `upload_max_filesize` értékét legalább `25M`, a `post_max_size` értékét például `30M` értékre. A webszerver kérésméretkorlátja is engedje ezt.
6. Állítsd a document rootot a `public_html` könyvtárra, vagy használd a mellékelt Apache rewrite szabályokat az app alkönyvtáras telepítéséhez. Nginx esetén a privát könyvtárakat külön tiltsd le, és a kéréseket a publikus belépési pontra irányítsd.
7. Első belépés: **admin / admin**. Külső hozzáférés engedélyezése előtt változtasd meg a jelszót a Jelszó oldalon. Nincs nyilvános regisztráció; további felhasználók az adatbázisban vehetők fel az app `auth_password_make()` függvényével előállított jelszóhash-sel.

A fájlok eredeti tartalommal, titkosítás nélkül, véletlenszerű, kiterjesztés nélküli tárolási néven kerülnek a privát könyvtárba. Kizárólag tulajdonos-ellenőrzött letöltési végponton keresztül érhetők el, mindig mellékletként, futtatás és böngészős előnézet nélkül. Ez más webes felhasználók hozzáférését akadályozza meg; a szerver rendszergazdája továbbra is hozzáférhet. Minden módosító művelet POST és CSRF-védett. Az adatbázist és a privát tárolót együtt mentsd.

## Ellenőrzés

`php tests/security.php` futtatja az adatbázis nélküli biztonsági és nézetellenőrzéseket. A teljes működéshez beállított adatbázis és webszerver kell. Két külön felhasználóval ellenőrizd a feltöltés–letöltés–átnevezés–törlés folyamatot, a másik felhasználó fájlazonosítójának elutasítását, a hibás CSRF-tokent, az üres mappák elrejtését és a szerver méretkorlátját.