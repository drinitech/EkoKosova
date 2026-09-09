<?php
// Lista e 38 komunave te Kosoves, perdoret per dropdown-in e raportimit
// dhe per shfaqjen e emrit te sakte te qytetit (me theksat shqip).

$eko_cities = [
    'decan' => 'Deçan',
    'dragash' => 'Dragash',
    'drenas' => 'Drenas',
    'ferizaj' => 'Ferizaj',
    'fushekosove' => 'Fushë Kosovë',
    'gjakove' => 'Gjakovë',
    'gjilan' => 'Gjilan',
    'gracanice' => 'Graçanicë',
    'hanielezit' => 'Hani i Elezit',
    'istog' => 'Istog',
    'junik' => 'Junik',
    'kacanik' => 'Kaçanik',
    'kamenice' => 'Kamenicë',
    'kline' => 'Klinë',
    'kllokot' => 'Kllokot',
    'leposaviq' => 'Leposaviq',
    'lipjan' => 'Lipjan',
    'malisheve' => 'Malishevë',
    'mamushe' => 'Mamushë',
    'mitrovice' => 'Mitrovicë',
    'mitrovicaveriut' => 'Mitrovica e Veriut',
    'novoberde' => 'Novobërdë',
    'obiliq' => 'Obiliq',
    'partesh' => 'Partesh',
    'peje' => 'Pejë',
    'podujeve' => 'Podujevë',
    'prishtina' => 'Prishtinë',
    'prizren' => 'Prizren',
    'rahovec' => 'Rahovec',
    'ranillug' => 'Ranillug',
    'shterpce' => 'Shtërpcë',
    'shtime' => 'Shtime',
    'skenderaj' => 'Skenderaj',
    'suhareke' => 'Suharekë',
    'viti' => 'Viti',
    'vushtrri' => 'Vushtrri',
    'zubinpotok' => 'Zubin Potok',
    'zvecan' => 'Zveçan',
];

// Kthen emrin e plote te qytetit nga slug-u i ruajtur ne DB.
function city_label($slug)
{
    global $eko_cities;
    return $eko_cities[$slug] ?? ucfirst($slug);
}
