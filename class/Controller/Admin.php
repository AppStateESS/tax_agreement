<?php

namespace tax_agreement\Controller;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Admin extends \Http\Controller 
{
    public function get(\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Response($view);
        return $response;
    }

    public function post(\Request $request)
    {
		if (isset($_POST["id_num"]))
		{
			if (filter_input(INPUT_POST, 'id_num', FILTER_VALIDATE_INT))
			{
				$id = $_POST["id_num"];
				$con = mysqli_connect('localhost','phpwebsite','phpwebsite', 'phpwebsite');
				$command = $request->shiftCommand();
				if (isset($_POST["approve"]))
				{
					$today = gettimeofday(true);
					$query = "select approved_date from tax_mainclass where id = $id";
					$result = mysqli_query($con,$query);
					while($row=mysqli_fetch_row($result))
					{
						$date = $row[0];
					}
					$query = "update tax_mainclass set approved_date = $today where id = $id";
					mysqli_query($con,$query);
					if (empty($date))
					{
						header('Location: ./unapproved');
					}
					else
					{
						header('Location: ./approved');
					}
				}
				else if (isset($_POST["delete"]))
				{
					$query = "select approved_date from tax_mainclass where id = $id";
					$result = mysqli_query($con,$query);
					while($row=mysqli_fetch_row($result))
					{
						$date = $row[0];
					}
					$query = "delete from tax_mainclass where id = $id";
					mysqli_query($con,$query);
					if (empty($date))
					{
						header('Location: ./unapproved');
					}
					else
					{
						header('Location: ./approved');
					}
				}
				mysqli_close($con);
			}
		}
		exit;
    }

    public function getHtmlView($data, \Request $request)
    {
        if (!\Current_User::isLogged()) 
		{
			\Current_User::requireLogin();
		}
        $cmd = $request->shiftCommand();
        $template = new \Template;
		if (empty($cmd)) 
		{
			$cmd = 'unapproved';
		}		
		switch ($cmd)
		{
			case 'approved':
				$template = $this->approved($request);
				break;
			case 'unapproved':
				$template = $this->unapproved($request);
				break;
		}
        return $template;
    }

    public function getController(\Request $request)
    {
        return $this;
    }

    public function unapproved(\Request $request)
    {
		$db = \Database::newDB();
        $t = $db->addTable('tax_mainclass');
        $result = $db->select();
        $rows = array();
		if ($result)
		{
			foreach ($result as $i)
			{
				if (empty($i['approved_date']))
				{
					$i['event_date'] = strftime('%D', $i['event_date']);
					$i['approved_date'] = 'Not approved';
					$rows[] = $i;
				}
			}
		}
        $tpl['rows'] = $rows;
        $template = new \Template($tpl);
        $template->setModuleTemplate('tax_agreement', 'Admin/Listing.html');
        return $template;
    }
    
    public function approved(\Request $request)
	{
		$db = \Database::newDB();
		$t = $db->addTable('tax_mainclass');
		$result = $db->select();
		$rows = array();  
	 	if ($result)
		{
			foreach ($result as $i)
			{
				if ($i['approved_date'])
				{
					$i['event_date'] = strftime('%D', $i['event_date']);
					$i['approved_date'] = strftime('%D', $i['approved_date']);
					$rows[] = $i; 
				}
			}	
		}
		$tpl['rows'] = $rows;
		$template = new \Template($tpl);
		$template->setModuleTemplate('tax_agreement', 'Admin/Listing.html');
		return $template; 
	}
}
?>
