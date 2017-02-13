<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
function tax_agreement_install(&$content)
{
    $db = \phpws2\Database::newDB();
    $db->begin();

    try {
        $form = new \tax_agreement\Resource\Form;
        $st = $form->createTable($db);
    } catch (\Exception $e) {
        if (isset($st) && $db->tableExists($st->getName())) {
            $st->drop();
        }
        $db->rollback();
        throw $e;
    }
    $db->commit();

    $content[] = 'Tables created';
    return true;
}

?>
