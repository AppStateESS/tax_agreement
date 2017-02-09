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
           $db = \phpws2\Database::newDB();
           $t = $db->addTable('tax_mainclass');
           $dt = \phpws2\Database\Datatype::factory($t, 'user_id', 'integer', '0');
           $dt->add();
           $content[] = '<pre>1.0.1
------------
+ Added user id to registration table.
</pre>';
       case version_compare($currentVersion, '1.0.2', '<'):
           $content[] = '<pre>1.0.2
------------
+ Fixed clicking Unapprove or Approve checked button when nothing was selected.
+ Output inside Control Panel.
</pre>';
    }
    return true;
}

?>
