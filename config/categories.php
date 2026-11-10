<?php
declare(strict_types=1);
/** Mapping categories -> tags OSM. Surcharge par les categories personnalisees (Parametres). */
return [
    'restaurant'  => ['label' => 'Restaurant / Fast food',    'icon' => '🍽️', 'tags' => [['amenity' => 'restaurant'], ['amenity' => 'fast_food']]],
    'cafe'        => ['label' => 'Café / Bar',                 'icon' => '☕', 'tags' => [['amenity' => 'cafe'], ['amenity' => 'bar']]],
    'coiffure'    => ['label' => 'Salon de coiffure',          'icon' => '💈', 'tags' => [['shop' => 'hairdresser']]],
    'beaute'      => ['label' => 'Institut de beauté',         'icon' => '💅', 'tags' => [['shop' => 'beauty']]],
    'boulangerie' => ['label' => 'Boulangerie / Pâtisserie',   'icon' => '🥖', 'tags' => [['shop' => 'bakery'], ['shop' => 'pastry']]],
    'garage'      => ['label' => 'Garage auto',                'icon' => '🔧', 'tags' => [['shop' => 'car_repair']]],
    'pharmacie'   => ['label' => 'Pharmacie',                  'icon' => '💊', 'tags' => [['amenity' => 'pharmacy']]],
    'hotel'       => ['label' => 'Hôtel / Hébergement',         'icon' => '🏨', 'tags' => [['tourism' => 'hotel'], ['tourism' => 'hostel'], ['tourism' => 'guest_house']]],
    'fitness'     => ['label' => 'Salle de sport',             'icon' => '🏋️', 'tags' => [['leisure' => 'fitness_centre']]],
    'epicerie'    => ['label' => 'Épicerie / Alimentation',    'icon' => '🛒', 'tags' => [['shop' => 'convenience'], ['shop' => 'supermarket']]],
    'fleuriste'   => ['label' => 'Fleuriste',                  'icon' => '🌸', 'tags' => [['shop' => 'florist']]],
    'dentiste'    => ['label' => 'Dentiste',                   'icon' => '🦷', 'tags' => [['amenity' => 'dentist']]],
    'medecin'     => ['label' => 'Médecin / Cabinet',          'icon' => '🩺', 'tags' => [['amenity' => 'doctors']]],
    'vetement'    => ['label' => 'Boutique vêtements',         'icon' => '👕', 'tags' => [['shop' => 'clothes']]],
];
