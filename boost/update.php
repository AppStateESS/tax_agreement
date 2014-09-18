<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
function tax_agreement_update(&$content, $currentVersion)
{
    $home_directory = PHPWS_Boost::getHomeDir();

    switch ($currentVersion) {
       case version_compare($currentVersion, '1.0.1', '<'):
           $db = \Database::newDB();
           $t = $db->addTable('tax_mainclass');
           $dt = \Database\Datatype::factory($t, 'user_id', 'integer', '0');
           $dt->add();
           $content[] = '<pre>1.0.1
------------
+ Added user id to registration table.
</pre>';
    }
    return true;
}

?>
