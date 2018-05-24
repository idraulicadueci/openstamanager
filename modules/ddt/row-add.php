<?php

include_once __DIR__.'/../../core.php';

// Info contratto
$rs = $dbo->fetchArray('SELECT * FROM dt_ddt WHERE id='.prepare($id_record));
$idanagrafica = $rs[0]['idanagrafica'];

// Leggo il conto dall'ultima riga inserita
$rs = $dbo->fetchArray('SELECT idconto FROM dt_righe_ddt WHERE idddt='.prepare($id_record).' ORDER BY id DESC LIMIT 0,1');
$idconto = $rs[0]['idconto'];

if ($module['name'] == 'Ddt di vendita') {
    $dir = 'entrata';
    
    // Se non ho letto un conto dall'ultima riga inserita, lo leggo dalle impostazioni
    if (empty($idconto )) {
        $idconto = get_var('Conto predefinito fatture di vendita');
    }
} else {
    $dir = 'uscita';
    
    // Se non ho letto un conto dall'ultima riga inserita, lo leggo dalle impostazioni
    if (empty($idconto )) {
        $idconto = get_var('Conto predefinito fatture di acquisto');
    }
}

$_SESSION['superselect']['dir'] = $dir;

// Impostazioni per la gestione
$options = [
    'op' => 'addriga',
    'action' => 'add',
    'dir' => $dir,
    'idanagrafica' => $idanagrafica,
];

// Dati di default
$result = [
    'descrizione' => '',
    'qta' => 1,
    'um' => '',
    'prezzo' => 0,
    'sconto_unitario' => 0,
    'tipo_sconto' => '',
    'idiva' => '',
];

// Leggo l'iva predefinita per l'anagrafica e se non c'è leggo quella predefinita generica
$iva = $dbo->fetchArray('SELECT idiva_'.($dir == 'uscita' ? 'acquisti' : 'vendite').' AS idiva FROM an_anagrafiche WHERE idanagrafica='.prepare($idanagrafica));
$result['idiva'] = $iva[0]['idiva'] ?: get_var('Iva predefinita');

// Sconto unitario
$rss = $dbo->fetchArray('SELECT prc_guadagno FROM mg_listini WHERE id=(SELECT idlistino_'.($dir == 'uscita' ? 'acquisti' : 'vendite').' FROM an_anagrafiche WHERE idanagrafica='.prepare($idanagrafica).')');
if (!empty($rss)) {
    $result['sconto_unitario'] = $rss[0]['prc_guadagno'];
    $result['tipo_sconto'] = 'PRC';
}

// Importazione della gestione dedicata
$file = 'riga';
if (isset($get['is_descrizione'])) {
    $file = 'descrizione';
} elseif (isset($get['is_articolo'])) {
    $file = 'articolo';

    $options['op'] = 'addarticolo';
}

echo App::load($file.'.php', $result, $options);
