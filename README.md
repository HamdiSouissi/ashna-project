# LeadFinder 🎯

Application PHP native (PHP 8.x, sans framework, sans Composer) de prospection de
commerces locaux **sans site web**, à partir de données OpenStreetMap 100 % gratuites
(Nominatim + Overpass). Compatible hébergement mutualisé **IONOS Web Hosting Plus**
(PHP + cURL + MySQL uniquement).

## Fonctionnalités
- Recherche par ville + catégorie, AJAX avec barre de progression (découpage en sous-zones).
- Détection des établissements SANS site web ; distinction "réseaux sociaux uniquement".
- Vérification web gratuite (DuckDuckGo HTML) + score de confiance.
- Normalisation des téléphones (TN/FR/MA/DZ, format E.164, liens tel: et wa.me).
- Fiche prospect, interactions, notes, statuts, Kanban, carte Leaflet, dashboard,
  export CSV/Excel (UTF-8 BOM).
- IA optionnelle (endpoint compatible OpenAI : Groq, Ollama distant…) : normalisation +
  rédaction de messages. L'app fonctionne à 100 % sans IA (désactivée par défaut).
- Multilingue FR (défaut) / AR (RTL). Mode sombre. Responsive.

## Installation pas à pas (IONOS Web Hosting Plus)

### 1. Créer la base MySQL
1. Panneau IONOS → **Espace web & Bases de données** → *Créer une base de données*.
2. Noter : hôte (`localhost`), nom de base, utilisateur, mot de passe.

### 2. Uploader les fichiers
1. Via SFTP / le Gestionnaire de fichiers IONOS, placez le contenu du dossier
   `leadfinder/` directement dans le dossier web racine (`/` ou `/httpdocs`).
2. Le `.htaccess` racine redirige tout vers `public/` et bloque
   `app/`, `config/`, `database/`, `storage/`.

### 3. Importer la base
1. Panneau IONOS → **phpMyAdmin**.
2. Sélectionnez la base créée → onglet *Importer* → `database/schema.sql`.
3. (Optionnel) Importez ensuite `database/seed.sql` pour des données de démonstration.

### 4. Configurer
1. Copiez `config/config.example.php` en `config/config.php`.
2. Renseignez `db.host`, `db.name`, `db.user`, `db.pass`.
3. **Important** : mettez un vrai User-Agent avec un moyen de contact
   (exigence de Nominatim) : `nominatim.user_agent`.

### 5. Créer le compte admin
Ouvrez `https://votre-domaine.tld/install` → créez le compte administrateur
(mot de passe ≥ 8 caractères, haché avec `password_hash`). L'installation se
désactive automatiquement dès qu'un utilisateur existe.

### 6. Vérifications serveur
IONOS Web Hosting Plus : PHP 8.x + cURL + PDO MySQL actifs par défaut.
Aucune ligne de commande, aucun Composer, aucun module exotique requis.

## Utilisation
1. **Recherche** → saisissez une ville, choisissez une catégorie, lancez.
   Les résultats s'affichent au fil des sous-zones.
2. **Prospects** → filtrez (téléphone, e-mail, statut, score), actions groupées, export.
3. **Fiche prospect** → Appeler / WhatsApp / E-mail / Itinéraire / **Vérifier** (score) /
   Générer un message (si IA activée dans Paramètres) / interactions.
4. **Pipeline** → glisser-déposer entre statuts.
5. **Paramètres** (admin) → serveurs Overpass, délais, IA, catégories personnalisées
   (`Libellé | amenity=restaurant; cuisine=pizza`).

## Respect des conditions d'usage
- Nominatim : User-Agent identifiable, 1 requête/s max (respecté par Throttle).
- Overpass : 3 serveurs miroirs avec bascule, délai configurable, cache 24 h.
- DuckDuckGo : délai de 2,5 s entre vérifications (paramétrable).
- Aucun scraping de Google Maps ni Pages Jaunes. Tuiles de carte OpenStreetMap.
- Les données OSM sont sous licence ODbL : créditez OpenStreetMap dans vos usages.

## Sécurité
Sessions protégées, CSRF sur tous les POST, PDO préparé partout, sorties échappées,
dossiers sensibles bloqués par .htaccess, mots de passe `password_hash()`.

## Dépannage
- *« Configuration manquante »* → créez `config/config.php`.
- *Zone introuvable* → reformulez (ex. « Tunis, Tunisie »).
- *Overpass indisponible* → vérifiez les serveurs dans Paramètres ; réessayez plus tard.
- Logs : `storage/logs/app-YYYY-MM-DD.log`.

## Structure du projet
```
├── .htaccess
├── README.md
├── config/            (config.example.php, categories.php, i18n/fr.php, i18n/ar.php)
├── database/          (schema.sql, seed.sql)
├── public/            (index.php, .htaccess, assets/css, assets/js)
├── app/
│   ├── Core/          (Router, Database, Auth, Csrf, Lang, Controller, Helpers)
│   ├── Models/        (User, Search, Prospect, Interaction, Setting)
│   ├── Services/      (HttpClient, Throttle, CacheService, Nominatim, Overpass,
│   │                   PhoneNormalizer, Enrichment, AiService, Export)
│   ├── Controllers/   (Auth, Install, Dashboard, Search, Prospect, Pipeline,
│   │                   Export, Settings, Api)
│   └── Views/         (layout + 11 vues)
└── storage/           (cache/, logs/)
```
