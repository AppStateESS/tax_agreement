<?php

/**
 * Uninstall file for blog
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
function tax_agreement_uninstall(&$content)
{
    $db = \phpws2\Database::newDB();

    if ($db->tableExists('tax_mainclass')) {
        $tbl = $db->buildTable('tax_mainclass');
        $tbl->drop();
        $content[] = 'Dropping table ta_mainclass';
    }
    return true;
}

?>
