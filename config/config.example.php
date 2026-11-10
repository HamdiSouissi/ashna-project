<?php
declare(strict_types=1);
/**
 * LeadFinder - configuration.
 * Copier ce fichier en config/config.php et renseigner les acces MySQL.
 */
return [
    // --- Base de donnees (IONOS : hote "localhost", identifiants fournis par le panel) ---
    'db' => [
        'host'    => 'localhost',
        'name'    => 'db_leadfinder',
        'user'    => 'u_leadfinder',
        'pass'    => 'CHANGER_MOT_DE_PASSE',
        'charset' => 'utf8mb4',
    ],

    // Base URL publique si auto-detection insuffisante, ex: 'https://monsite.com' ('' = auto)
    'base_url' => '',

    'app' => [
        'timezone'        => 'Africa/Tunis',
        'default_country' => 'TN',   // TN / FR / MA / DZ (normalisation telephones)
        'default_lang'    => 'fr',
    ],

    // --- Nominatim (OpenStreetMap) - 1 req/s max, User-Agent identifiable obligatoire ---
    'nominatim' => [
        'url'        => 'https://nominatim.openstreetmap.org/search',
        'user_agent' => 'LeadFinder/1.0 (prospection - contact@mondomaine.tld)',
        'delay'      => 1.1,
    ],

    // --- Overpass (serveurs miroirs, bascule automatique) ---
    'overpass' => [
        'servers'  => [
            'https://overpass-api.de/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter',
            'https://maps.mail.ru/osm/tools/overpass/api/interpreter',
        ],
        'timeout'  => 30,
        'delay'    => 1.0,
    ],

    // --- Grille : decoupage des grosses zones ---
    'grid' => [
        'cell_degrees' => 0.12,   // taille cible d'une sous-zone (degres)
        'max_cells'    => 36,     // plafond de sous-zones par recherche
    ],

    // --- Verification web (DuckDuckGo HTML, gratuit) ---
    'enrichment' => [
        'enabled' => true,
        'delay'   => 2.5,         // secondes entre requetes DDG
        'timeout' => 15,
    ],

    // --- IA optionnelle (endpoint compatible OpenAI : Ollama, Groq, Gemini proxy...)
    //     L'application fonctionne a 100 % sans IA. Surchargable dans Parametres. ---
    'ai' => [
        'enabled'  => false,
        'base_url' => '',         // ex: https://api.groq.com/openai/v1
        'api_key'  => '',
        'model'    => 'llama-3.1-8b-instant',
        'timeout'  => 60,
    ],

    // --- Cache (heures) ---
    'cache_ttl_hours' => 24,
];
