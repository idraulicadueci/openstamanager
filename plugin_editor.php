<?php

include_once __DIR__.'/core.php';

use Models\Plugin;

// Lettura parametri iniziali
$info = Plugin::get($id_plugin);

if (empty($info) || empty($info['enabled'])) {
    die(tr('Accesso negato'));
}

if (!empty($info['script'])) {
    // Inclusione di eventuale plugin personalizzato
    if (file_exists($docroot.'/modules/'.$info['module_dir'].'/plugins/custom/'.$info['script'])) {
        include $docroot.'/modules/'.$info['module_dir'].'/plugins/custom/'.$info['script'];
    } elseif (file_exists($docroot.'/modules/'.$info['module_dir'].'/plugins/'.$info['script'])) {
        include $docroot.'/modules/'.$info['module_dir'].'/plugins/'.$info['script'];
    }

    return;
} else {
    // Caricamento helper modulo (verifico se ci sono helper personalizzati)
    if (file_exists($docroot.'/plugins/'.$info['directory'].'/custom/modutil.php')) {
        include_once $docroot.'/plugins/'.$info['directory'].'/custom/modutil.php';
    } elseif (file_exists($docroot.'/plugins/'.$info['directory'].'/modutil.php')) {
        include_once $docroot.'/plugins/'.$info['directory'].'/modutil.php';
    }

    // Lettura risultato query del modulo
    if (file_exists($docroot.'/plugins/'.$info['directory'].'/custom/init.php')) {
        include $docroot.'/plugins/'.$info['directory'].'/custom/init.php';
    } elseif (file_exists($docroot.'/plugins/'.$info['directory'].'/init.php')) {
        include $docroot.'/plugins/'.$info['directory'].'/init.php';
    }

    // Esecuzione delle operazioni del modulo
    if (file_exists($docroot.'/plugins/'.$info['directory'].'/custom/actions.php')) {
        include $docroot.'/plugins/'.$info['directory'].'/custom/actions.php';
    } elseif (file_exists($docroot.'/plugins/'.$info['directory'].'/actions.php')) {
        include $docroot.'/plugins/'.$info['directory'].'/actions.php';
    }

    if (empty($records)) {
        echo '
		<p>'.tr('Record non trovato').'.</p>';
    } else {
        // Lettura template modulo (verifico se ci sono template personalizzati, altrimenti uso quello base)
        if (file_exists($docroot.'/plugins/'.$info['directory'].'/custom/edit.php')) {
            include $docroot.'/plugins/'.$info['directory'].'/custom/edit.php';
        } elseif (file_exists($docroot.'/plugins/'.$info['directory'].'/custom/edit.html')) {
            include $docroot.'/plugins/'.$info['directory'].'/custom/edit.html';
        } elseif (file_exists($docroot.'/plugins/'.$info['directory'].'/edit.php')) {
            include $docroot.'/plugins/'.$info['directory'].'/edit.php';
        } elseif (file_exists($docroot.'/plugins/'.$info['directory'].'/edit.html')) {
            include $docroot.'/plugins/'.$info['directory'].'/edit.html';
        }
    }

    redirectOperation($id_module, $id_parent);

    $module = Modules::get($info['idmodule_to']);

    if ($module['permessi'] != 'rw') {
        ?>
<script>

    $(document).ready( function(){
        $('input, textarea, select', 'section.content').attr('readonly', 'true');
        $('select.chzn-done').prop('disabled', true).trigger('liszt:updated');
        $('a.btn, button, input[type=button], input[type=submit]', 'section.content').hide();
        $('a.btn-info, button.btn-info, input[type=button].btn-info', 'section.content').show();
    });
</script>

<?php
    }
}

echo '
	<script src="'.$rootdir.'/lib/init.js"></script>';
