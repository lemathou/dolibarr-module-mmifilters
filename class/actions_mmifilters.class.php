<?php

dol_include_once('custom/mmicommon/class/mmi_actions.class.php');

class ActionsMMIFilters extends MMI_Actions_1_0
{
	const MOD_NAME = 'mmifilters';

	function printFieldPreListTitle($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['propallist']))
		{
			$no_user = GETPOST('search_no_user', 'bool');
			//var_dump($no_user);
			
			$print .= '<div class="divsearchfield">';
			$print .= '<input type="checkbox" name="search_no_user" value="1"'.($no_user ?' checked="checked"' :'').' /> Sans commercial';
			$print .= '</div>';
		}
		if ($this->in_context($parameters, ['propallist', 'orderlist', 'invoicelist']))
		{
			$thirdparty_pro = GETPOST('search_thirdparty_pro', 'bool');
			//var_dump($no_user);
			
			$print .= '<div class="divsearchfield">';
			$print .= '<input type="checkbox" name="search_thirdparty_pro" value="1"'.($thirdparty_pro ?' checked="checked"' :'').' /> Uniquement les pro';
			$print .= '</div>';
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function printFieldListWhere($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;

		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['propallist', 'orderlist', 'invoicelist']))
		{
			if (GETPOST('search_no_user', 'bool'))
				$print .= " AND c2.fk_socpeople IS NULL AND sc2.fk_user IS NULL";
			if (GETPOST('search_thirdparty_pro', 'bool')) {
				$l = [];
				if (!empty($conf->global->MMIFILTERS_PRO_USE_SIREN)) {
					foreach(['siren', 'siret', 'tva_intra'] as $fieldname)
						$l[] = '(s.'.$fieldname.' IS NOT NULL AND s.'.$fieldname.' != "")';
				}
				$l[] = '(s2.pro = 1)';
				
				$print .= ' AND ('.implode(' OR ', $l).')';
			}
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function printFieldListFrom($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['invoicelist']))
		{
			//
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function printFieldListJoinSoc($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['propallist', 'orderlist', 'invoicelist']))
		{
			if (GETPOST('search_no_user', 'bool'))
				$print .= ' LEFT JOIN '.MAIN_DB_PREFIX."societe_commerciaux as sc2 ON sc2.fk_soc=s.rowid";
			if (GETPOST('search_thirdparty_pro', 'bool'))
				$print .= ' LEFT JOIN '.MAIN_DB_PREFIX."societe_extrafields as s2 ON s2.fk_object=s.rowid";
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function printFieldListJoinPropal($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['propallist']))
		{
			if (GETPOST('search_no_user', 'bool'))
				$print .= ' LEFT JOIN '.MAIN_DB_PREFIX.'element_contact as c2 ON c2.element_id=p.rowid AND c2.fk_c_type_contact=31';
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function printFieldListJoinInvoice($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['invoicelist']))
		{
			//
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	function printFieldListSearchParam($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$print = '';
		
		if ($this->in_context($parameters, ['propallist', 'orderlist', 'invoicelist']))
		{
			if (GETPOST('search_no_user', 'bool')) {
				$print .= '&search_no_user=1';
			}
			if (GETPOST('search_thirdparty_pro', 'bool')) {
				$print .= '&search_thirdparty_pro=1';
			}
		}

		if (! $error)
		{
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs;

		$langs->load('mmiprestasync@mmiprestasync');

		if ($this->in_context($parameters, ['propallist', 'orderlist', 'invoicelist']))
		{
			//var_dump($parameters);
			//$this->results = [];
			$this->resprints = '<option value="assign">'.img_picto('', 'user', 'class="pictofixedwidth"').$langs->trans("ASSIGN_COMMERCIAL").'</option>';
			//var_dump($this->resprints);
		}
		return 0;
	}

	function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $user;
		
		$error = 0; // Error counter
		$myvalue = 'test'; // A result value
		$print = '';
		
		//print_r($parameters);
		//echo "action: " . $action;
		//print_r($object);

		if ($this->in_context($parameters, ['propallist']))
		{
			$action     = GETPOST('action', 'aZ09') ?GETPOST('action', 'aZ09') : 'view';
			$massaction = GETPOST('massaction', 'alpha');
			$confirm    = GETPOST('confirm', 'alpha');
			$toselect    = GETPOST('toselect', 'array');
			
			$fk_commercial = GETPOST('fk_user', 'alpha');

			$permissiontoassign = $user->rights->propale->creer;
			
			//var_dump($parameters);
			//var_dump($action); var_dump($confirm); var_dump($_POST); die();
			if (($action == 'confirm_assign' && $confirm == 'yes') && $permissiontoassign) {
			
				$objectclass = 'Propal';
				require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
				require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
				require_once DOL_DOCUMENT_ROOT.'/societe/class/client.class.php';
				
				$print .= '<p>Commercial assigné !</p>';
				//var_dump($_POST); die();
				
				foreach ($toselect as $toselectid) {
					// Propal
					$propal = new Propal($db);
					$result = $propal->fetch($toselectid);
					if (!($result > 0))
						continue;
					
					//var_dump($propal);
					//$result = $propal->add_contact($fk_commercial, 31, 'internal');
					if (! ($result > 0)) {
						$print = 'Erreur';
					}
					
					$societe_assign = GETPOST('societe_assign', 'bool');
					$societe_assign2 = GETPOST('societe_assign2', 'bool');
					if ($societe_assign) {
						// Client
						$soc = new Client($db);
						$result = $soc->fetch($propal->socid);
						if (!($result > 0))
							continue;
						
						$commerciaux = $soc->getSalesRepresentatives($user);
						// Test si déjà
						if (!empty($commerciaux)) {
							foreach($commerciaux as $commercial) {
								if ($commercial['id']==$fk_commercial)
									continue;
							}
							// Test si pas overload
							if (!$societe_assign2)
								continue;
						}
						 $soc->add_commercial($user, $fk_commercial);
					}
				}
			}
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	function doPreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$myvalue = 'test'; // A result value
		$print = '';

		//print_r($parameters);
		//echo "action: " . $action;
		//print_r($object);
		
		$db = $GLOBALS['db'];
		
		if ($this->in_context($parameters, ['propallist', 'orderlist', 'invoicelist']))
		{
			//var_dump($parameters);
			if ($_POST['massaction']=='assign') {
				$print .= dol_get_fiche_head(null, '', '');
				$print .= '<p>Quel commercial assigner au Devis ?</p>';
				$print .= '<input type="hidden" name="action" value="confirm_assign">';
				
				// Form sélection
				$print .= '<p><input type="checkbox" name="societe_assign" value="1" checked="checked" /> Assigner aussi le commercial au tiers</p>';
				$print .= '<p><input type="checkbox" name="societe_assign2" value="1" /> Assigner le commercial même si le tiers a déja un autre commercial<br />(il peut y en avoir plusieurs)</p>';
				
				// Form sélection
				$print .= '<p>Commercial : <select name="fk_user">';
				$print .= '<option value="">-- Choisir --</option>';
				$sql = 'SELECT u.rowid, u.login, u.lastname, u.firstname
					FROM '.MAIN_DB_PREFIX.'user as u
					WHERE u.statut=1';
				$resql = $db->query($sql);
				//var_dump($resql); die();
				while($row=$resql->fetch_assoc()) {
					$print .= '<option value="'.$row['rowid'].'">'.$row['firstname'].' '.$row['lastname'].'</option>';
				}
				$print .= '</select></p>';
				$print .= '<p>Confirmation : <select class="flat width75 marginleftonly marginrightonly" id="confirm" name="confirm"><option value="yes">Oui</option>
<option value="no" selected="">Non</option></select>';
				$print .= '<input class="button valignmiddle confirmvalidatebutton" type="submit" value="Mettre à jour" /></p>';
				$print .= dol_get_fiche_end();
			}
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = $print;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		//print_r($parameters);
		//echo "action: " . $action;
		//print_r($object);

		if (in_array($parameters['currentcontext'], ['somecontext']))
		if (in_array('somecontext', explode(':', $parameters['context'])))
		{
		  // do something only for the context 'somecontext'
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
}

ActionsMMIFilters::__init();
