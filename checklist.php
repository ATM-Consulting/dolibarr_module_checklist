<?php

	require 'config.php';
	
	dol_include_once('/checklist/class/checklist.class.php');
	
	$langs->load('checklist@checklist');
	
	$type_object = GETPOST('type_object');
	$fk_object = GETPOST('fk_object');
	
	$action =GETPOST('action');
	$PDOdb=new TPDOdb;
	$c = new TChecklist; 
	$c->loadBy($PDOdb, $type_object, $fk_object);
	
	switch ($action) {
		case 'yesido':
			//$PDOdb->debug=true;
			$c->iDoIt(GETPOST('k'));
			$c->setCheck();
			$c->save($PDOdb);
			
			break;
		default:
			
			break;
	}
	
	_fiche($c);
	
function _fiche(&$c) {
	global $db,$conf,$langs,$user;
	
	llxHeader('','Checklist');
	
	$PDOdb=new TPDOdb;
	
	if($c->type_object == 'project') {
		dol_include_once('/projet/class/project.class.php');
		dol_include_once('/core/lib/project.lib.php');
		
		$object = new Project($db);
		$object->fetch($c->fk_object);
		
		$head=project_prepare_head($object);
    	dol_fiche_head($head, 'checklist', $langs->trans("Project"),0,($object->public?'projectpub':'project'));
		
	}
	
	?>
	<table class="border" >
		<tr class="liste_titre">
			<td class="liste_titre"><?php echo $langs->trans('Label'); ?></td>
			<?php
				for($ii = 0; $ii<$c->levelAnswer; $ii++) {
					
					echo '<td class="liste_titre">&nbsp;</td>'; // '.($ii+1).'
					
				}
			if($user->rights->checklist->write) {
			?>
			<td class="liste_titre"><?php echo $langs->trans('YesIDo') ?></td>
			
			<?php
			}
			?>
		</tr>
	<?php
	
	foreach($c->TCheck as $k=>&$check) {
		
		echo '<tr>';
		
		echo '<td>'.$check['label'].'</td>';
		
		$Tab = $check['answers'];
		for($ii = 0; $ii<$c->levelAnswer; $ii++) {
			
			$a = each($Tab);
			
			echo '<td>';
			if(!empty($a)) {
				
				$date = $a['key'];
				
				$value = $a['value'];
				
				if($value['value'] === true) {
					
					$u=new User($db);
					$u->fetch($value['fk_user']);
					
					echo dol_print_date($date).img_info($u->login);
					
				}
				
			}
			echo '</td>';
			
		}
		
		if($user->rights->checklist->write) {
			echo '<td><a href="?action=yesido&k='.$k.'&type_object='.$c->type_object.'&fk_object='.$c->fk_object.'">'.img_picto($langs->trans('YesIDo'),'history.png').'</a></td>';	
		}
		
		echo '</tr>';
				
	}
	
	?>
	</table>
	<?php
	
	dol_fiche_end();
	
	llxFooter();
	
	
}
