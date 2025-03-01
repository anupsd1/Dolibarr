<?php
/* Copyright (C) 2001-2007  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2004       Eric Seigne             <eric.seigne@ryxeo.com>
 * Copyright (C) 2005       Marc Barilley / Ocebo   <marc@ocebo.com>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2006       Andre Cianfarani        <acianfa@free.fr>
 * Copyright (C) 2010-2014  Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2010-2011  Philippe Grand          <philippe.grand@atoo-net.com>
 * Copyright (C) 2012-2013  Christophe Battarel     <christophe.battarel@altairis.fr>
 * Copyright (C) 2013-2014  Florian Henry           <florian.henry@open-concept.pro>
 * Copyright (C) 2014       Ferran Marcet           <fmarcet@2byte.es>
 * Copyright (C) 2018       Frédéric France         <frederic.france@netlogic.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		htdocs/supplier_proposal/card.php
 *	\ingroup	supplier_proposal
 *	\brief		Card supplier proposal
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formmargin.class.php';
require_once DOL_DOCUMENT_ROOT . '/supplier_proposal/class/supplier_proposal.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/modules/supplier_proposal/modules_supplier_proposal.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/supplier_proposal.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
if (! empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
}

// Load translation files required by the page
$langs->loadLangs(array('companies', 'supplier_proposal', 'compta', 'bills', 'propal', 'orders', 'products', 'deliveries', 'sendings'));
if (! empty($conf->margin->enabled))
	$langs->load('margins');

$error = 0;

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$socid = GETPOST('socid', 'int');
$action = GETPOST('action', 'alpha');
$origin = GETPOST('origin', 'alpha');
$originid = GETPOST('originid', 'int');
$confirm = GETPOST('confirm', 'alpha');
$projectid = GETPOST('projectid', 'int');
$lineid = GETPOST('lineid', 'int');
$contactid = GETPOST('contactid','int');

// PDF
$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

// Nombre de ligne pour choix de produit/service predefinis
$NBLINES = 4;

// Security check
if (! empty($user->societe_id)) $socid = $user->societe_id;
$result = restrictedArea($user, 'supplier_proposal', $id);

$object = new SupplierProposal($db);
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Load object
if ($id > 0 || ! empty($ref)) {
	$ret = $object->fetch($id, $ref);
	if ($ret > 0)
		$ret = $object->fetch_thirdparty();
	if ($ret < 0)
		dol_print_error('', $object->error);
}

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('supplier_proposalcard','globalcard'));

$permissionnote = $user->rights->supplier_proposal->creer; // Used by the include of actions_setnotes.inc.php
$permissiondellink=$user->rights->supplier_proposal->creer;	// Used by the include of actions_dellink.inc.php
$permissiontoedit=$user->rights->supplier_proposal->creer;	// Used by the include of actions_lineupdown.inc.php


/*
 * Actions
 */

$parameters = array('socid' => $socid);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	if ($cancel)
	{
		if (! empty($backtopage))
		{
			header("Location: ".$backtopage);
			exit;
		}
		$action='';
	}

	include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php'; 	// Must be include, not include_once

	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

	include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';	// Must be include, not include_once

	// Action clone object
	if ($action == 'confirm_clone' && $confirm == 'yes')
	{
		if (1 == 0 && ! GETPOST('clone_content') && ! GETPOST('clone_receivers'))
		{
			setEventMessages($langs->trans("NoCloneOptionsSpecified"), null, 'errors');
		}
		else
		{
			if ($object->id > 0) {
				$result = $object->createFromClone($socid);
				if ($result > 0) {
					header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $result);
					exit();
				}
				else
				{
					setEventMessages($object->error, $object->errors, 'errors');
					$action = '';
				}
			}
		}
	}

	// Delete askprice
	else if ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->supplier_proposal->supprimer)
	{
		$result = $object->delete($user);
		if ($result > 0) {
			header('Location: ' . DOL_URL_ROOT . '/supplier_proposal/list.php');
			exit();
		} else {
			$langs->load("errors");
			setEventMessages($langs->trans($object->error), null, 'errors');
		}
	}

	// Remove line
	else if ($action == 'confirm_deleteline' && $confirm == 'yes' && $user->rights->supplier_proposal->creer)
	{
		$result = $object->deleteline($lineid);
		// reorder lines
		if ($result)
			$object->line_order(true);

		if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
			// Define output language
			$outputlangs = $langs;
			if (! empty($conf->global->MAIN_MULTILANGS)) {
				$outputlangs = new Translate("", $conf);
				$newlang = (GETPOST('lang_id','aZ09') ? GETPOST('lang_id','aZ09') : $object->thirdparty->default_lang);
				$outputlangs->setDefaultLang($newlang);
			}
			$ret = $object->fetch($id); // Reload to get new records
			$object->generateDocument($object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
		}

		header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
		exit();
	}

	// Validation
	else if ($action == 'confirm_validate' && $confirm == 'yes' &&
		((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->supplier_proposal->creer))
	   	|| (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->supplier_proposal->validate_advance)))
	)
	{
		$result = $object->valid($user);
		if ($result >= 0)
		{
			if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
			{
						// Define output language
				if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
				{
					$outputlangs = $langs;
					$newlang = '';
					if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang = GETPOST('lang_id','aZ09');
					if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
					if (! empty($newlang)) {
						$outputlangs = new Translate("", $conf);
						$outputlangs->setDefaultLang($newlang);
					}
					$model=$object->modelpdf;
					$ret = $object->fetch($id); // Reload to get new records

					$object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
				}
			}
		} else {
			$langs->load("errors");
			if (count($object->errors) > 0) setEventMessages($object->error, $object->errors, 'errors');
			else setEventMessages($langs->trans($object->error), null, 'errors');
		}
	}

	else if ($action == 'setdate_livraison' && $user->rights->supplier_proposal->creer)
	{
		$result = $object->set_date_livraison($user, dol_mktime(12, 0, 0, $_POST['liv_month'], $_POST['liv_day'], $_POST['liv_year']));
		if ($result < 0)
			dol_print_error($db, $object->error);
	}

	// Create askprice
	else if ($action == 'add' && $user->rights->supplier_proposal->creer)
	{
		$object->socid = $socid;
		$object->fetch_thirdparty();

		$date_delivery = dol_mktime(12, 0, 0, GETPOST('liv_month'), GETPOST('liv_day'), GETPOST('liv_year'));

		if ($socid < 1) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Supplier")), null, 'errors');
			$action = 'create';
			$error ++;
		}

		if (! $error)
		{
			$db->begin();

			// Si on a selectionne une demande a copier, on realise la copie
			if (GETPOST('createmode') == 'copy' && GETPOST('copie_supplier_proposal'))
			{
				if ($object->fetch(GETPOST('copie_supplier_proposal')) > 0) {
					$object->ref = GETPOST('ref');
					$object->date_livraison = $date_delivery;
					$object->shipping_method_id = GETPOST('shipping_method_id', 'int');
					$object->cond_reglement_id = GETPOST('cond_reglement_id');
					$object->mode_reglement_id = GETPOST('mode_reglement_id');
					$object->fk_account = GETPOST('fk_account', 'int');
					$object->remise_percent = GETPOST('remise_percent');
					$object->remise_absolue = GETPOST('remise_absolue');
					$object->socid = GETPOST('socid');
					$object->fk_project = GETPOST('projectid','int');
					$object->modelpdf = GETPOST('model');
					$object->author = $user->id; // deprecated
					$object->note = GETPOST('note','none');
					$object->statut = SupplierProposal::STATUS_DRAFT;

					$id = $object->create_from($user);
				} else {
					setEventMessages($langs->trans("ErrorFailedToCopyProposal", GETPOST('copie_supplier_proposal')), null, 'errors');
				}
			} else {
				$object->ref = GETPOST('ref');
				$object->date_livraison = $date_delivery;
				$object->demand_reason_id = GETPOST('demand_reason_id');
				$object->shipping_method_id = GETPOST('shipping_method_id', 'int');
				$object->cond_reglement_id = GETPOST('cond_reglement_id');
				$object->mode_reglement_id = GETPOST('mode_reglement_id');
				$object->fk_account = GETPOST('fk_account', 'int');
				$object->fk_project = GETPOST('projectid','int');
				$object->modelpdf = GETPOST('model');
				$object->author = $user->id; // deprecated
				$object->note = GETPOST('note','none');

				$object->origin = GETPOST('origin');
				$object->origin_id = GETPOST('originid');

				// Multicurrency
				if (!empty($conf->multicurrency->enabled))
				{
					$object->multicurrency_code = GETPOST('multicurrency_code', 'alpha');
				}

				// Fill array 'array_options' with data from add form
				$ret = $extrafields->setOptionalsFromPost($extralabels, $object);
				if ($ret < 0) {
					$error ++;
					$action = 'create';
				}
			}

			if (! $error)
			{
				if ($origin && $originid)
				{
					$element = 'supplier_proposal';
					$subelement = 'supplier_proposal';

					$object->origin = $origin;
					$object->origin_id = $originid;

					// Possibility to add external linked objects with hooks
					$object->linked_objects [$object->origin] = $object->origin_id;
					if (is_array($_POST['other_linked_objects']) && ! empty($_POST['other_linked_objects'])) {
						$object->linked_objects = array_merge($object->linked_objects, $_POST['other_linked_objects']);
					}

					$id = $object->create($user);
					if ($id > 0)
					{
							dol_include_once('/' . $element . '/class/' . $subelement . '.class.php');

							$classname = ucfirst($subelement);
							$srcobject = new $classname($db);

							dol_syslog("Try to find source object origin=" . $object->origin . " originid=" . $object->origin_id . " to add lines");
							$result = $srcobject->fetch($object->origin_id);

							if ($result > 0)
							{
								$lines = $srcobject->lines;
								if (empty($lines) && method_exists($srcobject, 'fetch_lines'))
								{
									$srcobject->fetch_lines();
									$lines = $srcobject->lines;
								}

								$fk_parent_line=0;
								$num=count($lines);
								for ($i=0;$i<$num;$i++)
								{
									$label=(! empty($lines[$i]->label)?$lines[$i]->label:'');
									$desc=(! empty($lines[$i]->desc)?$lines[$i]->desc:$lines[$i]->libelle);

									// Positive line
									$product_type = ($lines[$i]->product_type ? $lines[$i]->product_type : 0);

									// Reset fk_parent_line for no child products and special product
									if (($lines[$i]->product_type != 9 && empty($lines[$i]->fk_parent_line)) || $lines[$i]->product_type == 9) {
										$fk_parent_line = 0;
									}

									// Extrafields
									if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED) && method_exists($lines[$i], 'fetch_optionals')) {
										$lines[$i]->fetch_optionals();
										$array_options = $lines[$i]->array_options;
									}

									$result = $object->addline(
										$desc, $lines[$i]->subprice, $lines[$i]->qty, $lines[$i]->tva_tx,
										$lines[$i]->localtax1_tx, $lines[$i]->localtax2_tx,
										$lines[$i]->fk_product, $lines[$i]->remise_percent,
										'HT', 0, $lines[$i]->info_bits, $product_type,
										$lines[$i]->rang, $lines[$i]->special_code, $fk_parent_line,
										$lines[$i]->fk_fournprice, $lines[$i]->pa_ht, $label, $array_options,
										$lines[$i]->ref_supplier, $lines[$i]->fk_unit
									);

									if ($result > 0) {
										$lineid = $result;
									} else {
										$lineid = 0;
										$error ++;
										break;
									}

									// Defined the new fk_parent_line
									if ($result > 0 && $lines[$i]->product_type == 9) {
										$fk_parent_line = $result;
									}
								}

								// Hooks
								$parameters = array('objFrom' => $srcobject);
								$reshook = $hookmanager->executeHooks('createFrom', $parameters, $object, $action); // Note that $action and $object may have been
																											   // modified by hook
								if ($reshook < 0)
									$error ++;
							} else {
								setEventMessages($srcobject->error, $srcobject->errors, 'errors');
								$error ++;
							}
					} else {
						setEventMessages($object->error, $object->errors, 'errors');
						$error ++;
					}
				} 			// Standard creation
				else
				{
					$id = $object->create($user);
				}

				if ($id > 0)
				{
					if (! $error)
					{
						$db->commit();

						// Define output language
						if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
						{
							$outputlangs = $langs;
							$newlang = '';
							if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang = GETPOST('lang_id','aZ09');
							if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
							if (! empty($newlang)) {
								$outputlangs = new Translate("", $conf);
								$outputlangs->setDefaultLang($newlang);
							}
							$model=$object->modelpdf;

							$ret = $object->fetch($id); // Reload to get new records
							$result=$object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
							if ($result < 0) dol_print_error($db,$result);
						}

						header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $id);
						exit();
					}
					else
					{
						$db->rollback();
						$action='create';
					}
				}
				else
				{
					setEventMessages($object->error, $object->errors, 'errors');
					$db->rollback();
					$action='create';
				}
			}
		}
	}

	// Reopen proposal
	else if ($action == 'confirm_reopen' && $user->rights->supplier_proposal->cloturer && ! GETPOST('cancel','alpha')) {
		// prevent browser refresh from reopening proposal several times
		if ($object->statut == SupplierProposal::STATUS_SIGNED || $object->statut == SupplierProposal::STATUS_NOTSIGNED || $object->statut == SupplierProposal::STATUS_CLOSE) {
			$object->reopen($user, SupplierProposal::STATUS_VALIDATED);
		}
	}

	// Close proposal
	else if ($action == 'close' && $user->rights->supplier_proposal->cloturer && ! GETPOST('cancel','alpha')) {
		// prevent browser refresh from reopening proposal several times
		if ($object->statut == SupplierProposal::STATUS_SIGNED) {
			$object->setStatut(SupplierProposal::STATUS_CLOSE);
		}
	}

	// Set accepted/refused
	else if ($action == 'setstatut' && $user->rights->supplier_proposal->cloturer && ! GETPOST('cancel','alpha')) {
		if (! GETPOST('statut')) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("CloseAs")), null, 'errors');
			$action = 'statut';
		} else {
			// prevent browser refresh from closing proposal several times
			if ($object->statut == SupplierProposal::STATUS_VALIDATED) {
				$object->cloture($user, GETPOST('statut'), GETPOST('note','none'));
			}
		}
	}

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to send emails
	$trigger_name='PROPOSAL_SUPPLIER_SENTBYMAIL';
	$autocopy='MAIN_MAIL_AUTOCOPY_SUPPLIER_PROPOSAL_TO';
	$trackid='spr'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';

	// Actions to build doc
	$upload_dir = $conf->supplier_proposal->dir_output;
	$permissioncreate = $user->rights->supplier_proposal->creer;
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';


	// Go back to draft
	if ($action == 'modif' && $user->rights->supplier_proposal->creer)
	{
		$object->set_draft($user);

		if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
		{
			// Define output language
			$outputlangs = $langs;
			if (! empty($conf->global->MAIN_MULTILANGS)) {
				$outputlangs = new Translate("", $conf);
				$newlang = (GETPOST('lang_id','aZ09') ? GETPOST('lang_id','aZ09') : $object->thirdparty->default_lang);
				$outputlangs->setDefaultLang($newlang);
			}
			$ret = $object->fetch($id); // Reload to get new records
			$object->generateDocument($object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
		}
	}

	else if ($action == "setabsolutediscount" && $user->rights->supplier_proposal->creer) {
		if ($_POST["remise_id"]) {
			if ($object->id > 0) {
				$result = $object->insert_discount($_POST["remise_id"]);
				if ($result < 0) {
					setEventMessages($object->error, $object->errors, 'errors');
				}
			}
		}
	}

	// Add a product line
	if ($action == 'addline' && $user->rights->supplier_proposal->creer)
	{
		$langs->load('errors');
		$error = 0;

		// Set if we used free entry or predefined product
		$predef='';
		$product_desc=(GETPOST('dp_desc')?GETPOST('dp_desc'):'');
		$date_start=dol_mktime(GETPOST('date_start'.$predef.'hour'), GETPOST('date_start'.$predef.'min'), GETPOST('date_start' . $predef . 'sec'), GETPOST('date_start'.$predef.'month'), GETPOST('date_start'.$predef.'day'), GETPOST('date_start'.$predef.'year'));
		$date_end=dol_mktime(GETPOST('date_end'.$predef.'hour'), GETPOST('date_end'.$predef.'min'), GETPOST('date_end' . $predef . 'sec'), GETPOST('date_end'.$predef.'month'), GETPOST('date_end'.$predef.'day'), GETPOST('date_end'.$predef.'year'));
		$ref_supplier = GETPOST('fourn_ref','alpha');
		$prod_entry_mode = GETPOST('prod_entry_mode');
		if ($prod_entry_mode == 'free')
		{
			$idprod=0;
			$price_ht = GETPOST('price_ht');
			$tva_tx = (GETPOST('tva_tx') ? GETPOST('tva_tx') : 0);
		}
		else
		{
			$idprod=GETPOST('idprod', 'int');
			$price_ht = '';
			$tva_tx = '';
		}

		$qty = GETPOST('qty' . $predef);
		$remise_percent = GETPOST('remise_percent' . $predef);
		$price_ht_devise = GETPOST('multicurrency_price_ht');

		// Extrafields
		$extrafieldsline = new ExtraFields($db);
		$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
		$array_options = $extrafieldsline->getOptionalsFromPost($extralabelsline, $predef);
		// Unset extrafield
		if (is_array($extralabelsline)) {
			// Get extra fields
			foreach ($extralabelsline as $key => $value) {
				unset($_POST["options_" . $key]);
			}
		}

		if ($prod_entry_mode == 'free' && empty($idprod) && GETPOST('type') < 0) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Type")), null, 'errors');
			$error ++;
		}

		if ($prod_entry_mode == 'free' && empty($idprod) && $price_ht == '') 	// Unit price can be 0 but not ''. Also price can be negative for proposal.
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("UnitPriceHT")), null, 'errors');
			$error ++;
		}
		if ($prod_entry_mode == 'free' && empty($idprod) && empty($product_desc)) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Description")), null, 'errors');
			$error ++;
		}
		if (! $error && ($qty >= 0)) {
			$pu_ht = 0;
			$pu_ttc = 0;
			$price_min = 0;
			$price_base_type = (GETPOST('price_base_type', 'alpha') ? GETPOST('price_base_type', 'alpha') : 'HT');

			$db->begin();

			// Ecrase $pu par celui du produit
			// Ecrase $desc par celui du produit
			// Ecrase $txtva par celui du produit
			if ($prod_entry_mode != 'free' && empty($error))	// With combolist mode idprodfournprice is > 0 or -1. With autocomplete, idprodfournprice is > 0 or ''
			{
				$productsupplier = new ProductFournisseur($db);

				$idprod=0;
				if (GETPOST('idprodfournprice','alpha') == -1 || GETPOST('idprodfournprice','alpha') == '') $idprod=-99;	// Same behaviour than with combolist. When not select idprodfournprice is now -99 (to avoid conflict with next action that may return -1, -2, ...)

				if (preg_match('/^idprod_([0-9]+)$/', GETPOST('idprodfournprice','alpha'), $reg))
				{
					$idprod=$reg[1];
					$res=$productsupplier->fetch($idprod);
					// Call to init some price properties of $productsupplier
					// So if a supplier price already exists for another thirdparty (first one found), we use it as reference price
					if (! empty($conf->global->SUPPLIER_TAKE_FIRST_PRICE_IF_NO_PRICE_FOR_CURRENT_SUPPLIER))
					{
						$fksoctosearch = 0;
						$productsupplier->get_buyprice(0, -1, $idprod, 'none', $fksoctosearch);        // We force qty to -1 to be sure to find if a supplier price exist
						if ($productsupplier->fourn_socid != $socid)	// The price we found is for another supplier, so we clear supplier price
						{
							$productsupplier->ref_supplier = '';
						}
					}
					else
					{
						$fksoctosearch = $object->thirdparty->id;
						$productsupplier->get_buyprice(0, -1, $idprod, 'none', $fksoctosearch);        // We force qty to -1 to be sure to find if a supplier price exist
					}
				}
				elseif (GETPOST('idprodfournprice','alpha') > 0)
				{
					//$qtytosearch=$qty; 	   // Just to see if a price exists for the quantity. Not used to found vat.
					$qtytosearch=-1;	       // We force qty to -1 to be sure to find if a supplier price exist
					$idprod=$productsupplier->get_buyprice(GETPOST('idprodfournprice','alpha'), $qtytosearch);
					$res=$productsupplier->fetch($idprod);
				}

				if ($idprod > 0)
				{
					$label = $productsupplier->label;

					// if we use supplier description of the products
					if(!empty($productsupplier->desc_supplier) && !empty($conf->global->PRODUIT_FOURN_TEXTS)) {
					    $desc = $productsupplier->desc_supplier;
					} else $desc = $productsupplier->description;

					if (trim($product_desc) != trim($desc)) $desc = dol_concatdesc($desc, $product_desc);

					$pu_ht = $productsupplier->fourn_pu;

					$type = $productsupplier->type;
					$price_base_type = ($productsupplier->fourn_price_base_type?$productsupplier->fourn_price_base_type:'HT');

					$ref_supplier = $productsupplier->ref_supplier;

                    $fk_unit = $productsupplier->fk_unit;

					$tva_tx	= get_default_tva($object->thirdparty, $mysoc, $productsupplier->id, GETPOST('idprodfournprice','alpha'));
					$tva_npr = get_default_npr($object->thirdparty, $mysoc, $productsupplier->id, GETPOST('idprodfournprice','alpha'));
					if (empty($tva_tx)) $tva_npr=0;
					$localtax1_tx= get_localtax($tva_tx, 1, $mysoc, $object->thirdparty, $tva_npr);
					$localtax2_tx= get_localtax($tva_tx, 2, $mysoc, $object->thirdparty, $tva_npr);

					$result=$object->addline(
						$desc,
						$pu_ht,
						$qty,
						$tva_tx,
						$localtax1_tx,
						$localtax2_tx,
						$productsupplier->id,
						$remise_percent,
						$price_base_type,
						$pu_ttc,
						$tva_npr,
						$type,
						-1,
						0,
						GETPOST('fk_parent_line'),
						$fournprice,
						$buyingprice,
						$label,
						$array_options,
						$ref_supplier,
						$fk_unit,
						'',
						0,
						$productsupplier->fourn_multicurrency_unitprice
                    );
					//var_dump($tva_tx);var_dump($productsupplier->fourn_pu);var_dump($price_base_type);exit;
				}
				if ($idprod == -99 || $idprod == 0)
				{
					// Product not selected
					$error++;
					$langs->load("errors");
					setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ProductOrService")).' '.$langs->trans("or").' '.$langs->trans("NoPriceDefinedForThisSupplier"), null, 'errors');
				}
				if ($idprod == -1)
				{
					// Quantity too low
					$error++;
					$langs->load("errors");
					setEventMessages($langs->trans("ErrorQtyTooLowForThisSupplier"), null, 'errors');
				}
			}
			else if((GETPOST('price_ht')!=='' || GETPOST('price_ttc')!=='') && empty($error))    // Free product.  // $price_ht is already set
			{
				$pu_ht = price2num($price_ht, 'MU');
				$pu_ttc = price2num(GETPOST('price_ttc'), 'MU');
				$tva_npr = (preg_match('/\*/', $tva_tx) ? 1 : 0);
				$tva_tx = str_replace('*', '', $tva_tx);
				$label = (GETPOST('product_label') ? GETPOST('product_label') : '');
				$desc = $product_desc;
				$type = GETPOST('type');

				$fk_unit= GETPOST('units', 'alpha');

				$tva_tx = price2num($tva_tx);	// When vat is text input field

				// Local Taxes
				$localtax1_tx= get_localtax($tva_tx, 1, $mysoc, $object->thirdparty);
				$localtax2_tx= get_localtax($tva_tx, 2, $mysoc, $object->thirdparty);

				if ($price_ht !== '')
				{
					$pu_ht = price2num($price_ht, 'MU'); // $pu_ht must be rounded according to settings
				}
				else
				{
					$pu_ttc = price2num(GETPOST('price_ttc'), 'MU');
					$pu_ht = price2num($pu_ttc / (1 + ($tva_tx / 100)), 'MU'); // $pu_ht must be rounded according to settings
				}
				$price_base_type = 'HT';
				$pu_ht_devise = price2num($price_ht_devise, 'MU');

				$result = $object->addline($desc, $pu_ht, $qty, $tva_tx, $localtax1_tx, $localtax2_tx, $idprod, $remise_percent, $price_base_type, $pu_ttc, $info_bits, $type, - 1, 0, GETPOST('fk_parent_line'), $fournprice, $buyingprice, $label, $array_options, $ref_supplier, $fk_unit);
				//$result = $object->addline($desc, $ht, $qty, $tva_tx, $localtax1_tx, $localtax2_tx, 0, 0, '', $remise_percent, $price_base_type, $ttc, $type,'','', $date_start, $date_end, $array_options, $fk_unit);
			}


			if (! $error && $result > 0)
			{
					$db->commit();

					// Define output language
					if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
					{
						$outputlangs = $langs;
						$newlang = '';
						if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang = GETPOST('lang_id','aZ09');
						if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
						if (! empty($newlang)) {
							$outputlangs = new Translate("", $conf);
							$outputlangs->setDefaultLang($newlang);
						}
						$model=$object->modelpdf;
						$ret = $object->fetch($id); // Reload to get new records

						$result=$object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
						if ($result < 0) dol_print_error($db,$result);
					}

					unset($_POST['prod_entry_mode']);

					unset($_POST['qty']);
					unset($_POST['type']);
					unset($_POST['remise_percent']);
					unset($_POST['pu']);
					unset($_POST['price_ht']);
					unset($_POST['multicurrency_price_ht']);
					unset($_POST['price_ttc']);
					unset($_POST['tva_tx']);
					unset($_POST['label']);
					unset($_POST['product_ref']);
					unset($_POST['product_label']);
					unset($_POST['product_desc']);
					unset($_POST['fournprice']);
					unset($_POST['buying_price']);
					unset($localtax1_tx);
					unset($localtax2_tx);
					unset($_POST['np_marginRate']);
					unset($_POST['np_markRate']);
					unset($_POST['dp_desc']);
					unset($_POST['idprodfournprice']);
					unset($_POST['idprod']);

					unset($_POST['date_starthour']);
					unset($_POST['date_startmin']);
					unset($_POST['date_startsec']);
					unset($_POST['date_startday']);
					unset($_POST['date_startmonth']);
					unset($_POST['date_startyear']);
					unset($_POST['date_endhour']);
					unset($_POST['date_endmin']);
					unset($_POST['date_endsec']);
					unset($_POST['date_endday']);
					unset($_POST['date_endmonth']);
					unset($_POST['date_endyear']);
			}
			else
			{
				$db->rollback();

				setEventMessages($object->error, $object->errors, 'errors');
			}
			//}
		}
	}

	// Mise a jour d'une ligne dans la demande de prix
	else if ($action == 'updateline' && $user->rights->supplier_proposal->creer && GETPOST('save') == $langs->trans("Save")) {

		// Define info_bits
		$info_bits = 0;
		if (preg_match('/\*/', GETPOST('tva_tx')))
			$info_bits |= 0x01;

			// Clean parameters
		$description = dol_htmlcleanlastbr(GETPOST('product_desc','none'));

		// Define vat_rate
		$vat_rate = (GETPOST('tva_tx') ? GETPOST('tva_tx') : 0);
		$vat_rate = str_replace('*', '', $vat_rate);
		$localtax1_rate = get_localtax($vat_rate, 1, $object->thirdparty);
		$localtax2_rate = get_localtax($vat_rate, 2, $object->thirdparty);
		$pu_ht = GETPOST('price_ht') ? GETPOST('price_ht') : 0;

		// Add buying price
		$fournprice = (GETPOST('fournprice') ? GETPOST('fournprice') : '');
		$buyingprice = (GETPOST('buying_price') != '' ? GETPOST('buying_price') : '');    // If buying_price is '0', we muste keep this value

		// Extrafields
		$extrafieldsline = new ExtraFields($db);
		$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
		$array_options = $extrafieldsline->getOptionalsFromPost($extralabelsline);
		// Unset extrafield
		if (is_array($extralabelsline)) {
			// Get extra fields
			foreach ($extralabelsline as $key => $value) {
				unset($_POST["options_" . $key]);
			}
		}

		// Define special_code for special lines
		$special_code=GETPOST('special_code');
		if (! GETPOST('qty')) $special_code=3;

		// Check minimum price
		$productid = GETPOST('productid', 'int');
		if (! empty($productid)) {

			$productsupplier = new ProductFournisseur($db);
			if (! empty($conf->global->SUPPLIER_PROPOSAL_WITH_PREDEFINED_PRICES_ONLY))
			{
				if ($productid > 0 && $productsupplier->get_buyprice(0, price2num($_POST['qty']), $productid, 'none', GETPOST('socid','int')) < 0 )
				{
					setEventMessages($langs->trans("ErrorQtyTooLowForThisSupplier"), null, 'warnings');
				}
			}

			$product = new Product($db);
			$res = $product->fetch($productid);

			$type = $product->type;

			$price_min = $product->price_min;
			if (! empty($conf->global->PRODUIT_MULTIPRICES) && ! empty($object->thirdparty->price_level))
				$price_min = $product->multiprices_min [$object->thirdparty->price_level];

			$label = ((GETPOST('update_label') && GETPOST('product_label')) ? GETPOST('product_label') : '');
		} else {
			$type = GETPOST('type');
			$label = (GETPOST('product_label') ? GETPOST('product_label') : '');

			// Check parameters
			if (GETPOST('type') < 0) {
				setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Type")), null, 'errors');
				$error ++;
			}
		}

		if (! $error) {
			$db->begin();
			$ref_supplier = GETPOST('fourn_ref','alpha');
			$fk_unit = GETPOST('units');
			$result = $object->updateline(GETPOST('lineid'), $pu_ht, GETPOST('qty'), GETPOST('remise_percent'), $vat_rate, $localtax1_rate, $localtax2_rate, $description, 'HT', $info_bits, $special_code, GETPOST('fk_parent_line'), 0, $fournprice, $buyingprice, $label, $type, $array_options, $ref_supplier, $fk_unit);

			if ($result >= 0) {
				$db->commit();

				if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
					// Define output language
					$outputlangs = $langs;
					if (! empty($conf->global->MAIN_MULTILANGS)) {
						$outputlangs = new Translate("", $conf);
						$newlang = (GETPOST('lang_id','aZ09') ? GETPOST('lang_id','aZ09') : $object->thirdparty->default_lang);
						$outputlangs->setDefaultLang($newlang);
					}
					$ret = $object->fetch($id); // Reload to get new records
					$object->generateDocument($object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
				}

				unset($_POST['qty']);
				unset($_POST['type']);
				unset($_POST['productid']);
				unset($_POST['remise_percent']);
				unset($_POST['price_ht']);
				unset($_POST['multicurrency_price_ht']);
				unset($_POST['price_ttc']);
				unset($_POST['tva_tx']);
				unset($_POST['product_ref']);
				unset($_POST['product_label']);
				unset($_POST['product_desc']);
				unset($_POST['fournprice']);
				unset($_POST['buying_price']);

				unset($_POST['date_starthour']);
				unset($_POST['date_startmin']);
				unset($_POST['date_startsec']);
				unset($_POST['date_startday']);
				unset($_POST['date_startmonth']);
				unset($_POST['date_startyear']);
				unset($_POST['date_endhour']);
				unset($_POST['date_endmin']);
				unset($_POST['date_endsec']);
				unset($_POST['date_endday']);
				unset($_POST['date_endmonth']);
				unset($_POST['date_endyear']);
			} else {
				$db->rollback();

				setEventMessages($object->error, $object->errors, 'errors');
			}
		}
	}

	else if ($action == 'updateline' && $user->rights->supplier_proposal->creer && GETPOST('cancel','alpha') == $langs->trans('Cancel')) {
		header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $object->id); // Pour reaffichage de la fiche en cours d'edition
		exit();
	}

	// Set project
	else if ($action == 'classin' && $user->rights->supplier_proposal->creer) {
		$object->setProject(GETPOST('projectid'),'int');
	}

	// Delai de livraison
	else if ($action == 'setavailability' && $user->rights->supplier_proposal->creer) {
		$result = $object->availability($_POST['availability_id']);
	}

	// Conditions de reglement
	else if ($action == 'setconditions' && $user->rights->supplier_proposal->creer) {
		$result = $object->setPaymentTerms(GETPOST('cond_reglement_id', 'int'));
	}

	else if ($action == 'setremisepercent' && $user->rights->supplier_proposal->creer) {
		$result = $object->set_remise_percent($user, $_POST['remise_percent']);
	}

	else if ($action == 'setremiseabsolue' && $user->rights->supplier_proposal->creer) {
		$result = $object->set_remise_absolue($user, $_POST['remise_absolue']);
	}

	// Mode de reglement
	else if ($action == 'setmode' && $user->rights->supplier_proposal->creer) {
		$result = $object->setPaymentMethods(GETPOST('mode_reglement_id', 'int'));
	}

	// Multicurrency Code
	else if ($action == 'setmulticurrencycode' && $user->rights->supplier_proposal->creer) {
		$result = $object->setMulticurrencyCode(GETPOST('multicurrency_code', 'alpha'));
	}

	// Multicurrency rate
	else if ($action == 'setmulticurrencyrate' && $user->rights->supplier_proposal->creer) {
		$result = $object->setMulticurrencyRate(price2num(GETPOST('multicurrency_tx')));
	}

	else if ($action == 'update_extras') {
		$object->oldcopy = dol_clone($object);

		// Fill array 'array_options' with data from update form
		$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
		$ret = $extrafields->setOptionalsFromPost($extralabels, $object, GETPOST('attribute', 'none'));
		if ($ret < 0) $error++;

		if (! $error)
		{
			$result = $object->insertExtraFields('SUPPLIER_PROPOSAL_MODIFY');
			if ($result < 0)
			{
				setEventMessages($object->error, $object->errors, 'errors');
				$error++;
			}
		}

		if ($error) $action = 'edit_extras';
	}
}


/*
 * View
 */

llxHeader('', $langs->trans('CommRequests'), 'EN:Ask_Price_Supplier|FR:Demande_de_prix_fournisseur');

$form = new Form($db);
$formother = new FormOther($db);
$formfile = new FormFile($db);
$formmargin = new FormMargin($db);
$companystatic = new Societe($db);
if (! empty($conf->projet->enabled)) { $formproject = new FormProjets($db); }

$now = dol_now();

// Add new askprice
if ($action == 'create')
{
	$currency_code = $conf->currency;

	print load_fiche_titre($langs->trans("NewAskPrice"));

	$soc = new Societe($db);
	if ($socid > 0)
		$res = $soc->fetch($socid);

	// Load objectsrc
	if (! empty($origin) && ! empty($originid))
	{
		$element = 'supplier_proposal';
		$subelement = 'supplier_proposal';

		dol_include_once('/' . $element . '/class/' . $subelement . '.class.php');

		$classname = ucfirst($subelement);
		$objectsrc = new $classname($db);
		$objectsrc->fetch($originid);
		if (empty($objectsrc->lines) && method_exists($objectsrc, 'fetch_lines'))
		{
			$objectsrc->fetch_lines();
		}
		$objectsrc->fetch_thirdparty();

		$projectid = (! empty($objectsrc->fk_project) ? $objectsrc->fk_project : '');
		$soc = $objectsrc->thirdparty;

		$cond_reglement_id 	= (! empty($objectsrc->cond_reglement_id)?$objectsrc->cond_reglement_id:(! empty($soc->cond_reglement_id)?$soc->cond_reglement_id:0)); // TODO maybe add default value option
		$mode_reglement_id 	= (! empty($objectsrc->mode_reglement_id)?$objectsrc->mode_reglement_id:(! empty($soc->mode_reglement_id)?$soc->mode_reglement_id:0));
		$remise_percent 	= (! empty($objectsrc->remise_percent)?$objectsrc->remise_percent:(! empty($soc->remise_supplier_percent)?$soc->remise_supplier_percent:0));
		$remise_absolue 	= (! empty($objectsrc->remise_absolue)?$objectsrc->remise_absolue:(! empty($soc->remise_absolue)?$soc->remise_absolue:0));

		// Replicate extrafields
		$objectsrc->fetch_optionals($originid);
		$object->array_options = $objectsrc->array_options;

		if (!empty($conf->multicurrency->enabled))
		{
			if (!empty($objectsrc->multicurrency_code)) $currency_code = $objectsrc->multicurrency_code;
			if (!empty($conf->global->MULTICURRENCY_USE_ORIGIN_TX) && !empty($objectsrc->multicurrency_tx))	$currency_tx = $objectsrc->multicurrency_tx;
		}
	}
	else
	{
		$cond_reglement_id 	= $soc->cond_reglement_supplier_id;
		$mode_reglement_id 	= $soc->mode_reglement_supplier_id;
		if (!empty($conf->multicurrency->enabled) && !empty($soc->multicurrency_code)) $currency_code = $soc->multicurrency_code;
	}

	$object = new SupplierProposal($db);

	print '<form name="addprop" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	if ($origin != 'project' && $originid) {
		print '<input type="hidden" name="origin" value="' . $origin . '">';
		print '<input type="hidden" name="originid" value="' . $originid . '">';
	}

	dol_fiche_head();

	print '<table class="border" width="100%">';

	// Reference
	print '<tr><td class="titlefieldcreate fieldrequired">' . $langs->trans('Ref') . '</td><td colspan="2">' . $langs->trans("Draft") . '</td></tr>';

	// Third party
	print '<tr>';
	print '<td class="fieldrequired">' . $langs->trans('Supplier') . '</td>';
	if ($socid > 0) {
		print '<td colspan="2">';
		print $soc->getNomUrl(1);
		print '<input type="hidden" name="socid" value="' . $soc->id . '">';
		print '</td>';
	} else {
		print '<td colspan="2">';
		print $form->select_company('', 'socid', 's.fournisseur = 1', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300');
		print ' <a href="'.DOL_URL_ROOT.'/societe/card.php?action=create&client=0&fournisseur=1&backtopage='.urlencode($_SERVER["PHP_SELF"].'?action=create').'">'.$langs->trans("AddThirdParty").'</a>';
		print '</td>';
	}
	print '</tr>' . "\n";

	if ($soc->id > 0)
	{
		// Discounts for third party
		print '<tr><td>' . $langs->trans('Discounts') . '</td><td>';

		$absolute_discount = $soc->getAvailableDiscounts('', '', 0, 1);

		$thirdparty = $soc;
		$discount_type = 1;
		$backtopage = urlencode($_SERVER["PHP_SELF"] . '?socid=' . $thirdparty->id . '&action=' . $action . '&origin=' . GETPOST('origin') . '&originid=' . GETPOST('originid'));
		include DOL_DOCUMENT_ROOT.'/core/tpl/object_discounts.tpl.php';

		print '</td></tr>';
	}

	// Terms of payment
	print '<tr><td class="nowrap">' . $langs->trans('PaymentConditionsShort') . '</td><td colspan="2">';
	$form->select_conditions_paiements(GETPOST('cond_reglement_id') > 0 ? GETPOST('cond_reglement_id') : $cond_reglement_id, 'cond_reglement_id', -1, 1);
	print '</td></tr>';

	// Mode of payment
	print '<tr><td>' . $langs->trans('PaymentMode') . '</td><td colspan="2">';
	$form->select_types_paiements(GETPOST('mode_reglement_id') > 0 ? GETPOST('mode_reglement_id') : $mode_reglement_id, 'mode_reglement_id');
	print '</td></tr>';

	// Bank Account
	if (! empty($conf->global->BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL) && ! empty($conf->banque->enabled)) {
		print '<tr><td>' . $langs->trans('BankAccount') . '</td><td colspan="2">';
		$form->select_comptes(GETPOST('fk_account')>0 ? GETPOST('fk_account','int') : $fk_account, 'fk_account', 0, '', 1);
		print '</td></tr>';
	}

	// Shipping Method
	if (! empty($conf->expedition->enabled)) {
		print '<tr><td>' . $langs->trans('SendingMethod') . '</td><td colspan="2">';
		print $form->selectShippingMethod(GETPOST('shipping_method_id') > 0 ? GETPOST('shipping_method_id', 'int') : $shipping_method_id, 'shipping_method_id', '', 1);
		print '</td></tr>';
	}

	// Delivery date (or manufacturing)
	print '<tr><td>' . $langs->trans("DeliveryDate") . '</td>';
	print '<td colspan="2">';
	$datedelivery = dol_mktime(0, 0, 0, GETPOST('liv_month'), GETPOST('liv_day'), GETPOST('liv_year'));
	if ($conf->global->DATE_LIVRAISON_WEEK_DELAY != "") {
		$tmpdte = time() + ((7 * $conf->global->DATE_LIVRAISON_WEEK_DELAY) * 24 * 60 * 60);
		$syear = date("Y", $tmpdte);
		$smonth = date("m", $tmpdte);
		$sday = date("d", $tmpdte);
		print $form->selectDate($syear."-".$smonth."-".$sday, 'liv_', '', '', '', "addask");
	} else {
		print $form->selectDate($datedelivery ? $datedelivery : -1, 'liv_', '', '', '', "addask", 1, 1);
	}
	print '</td></tr>';


	// Model
	print '<tr>';
	print '<td>' . $langs->trans("DefaultModel") . '</td>';
	print '<td colspan="2">';
	$liste = ModelePDFSupplierProposal::liste_modeles($db);
	print $form->selectarray('model', $liste, ($conf->global->SUPPLIER_PROPOSAL_ADDON_PDF_ODT_DEFAULT ? $conf->global->SUPPLIER_PROPOSAL_ADDON_PDF_ODT_DEFAULT : $conf->global->SUPPLIER_PROPOSAL_ADDON_PDF));
	print "</td></tr>";

	// Project
	if (! empty($conf->projet->enabled))
	{
		$langs->load("projects");

		$formproject = new FormProjets($db);

		if ($origin == 'project') $projectid = ($originid ? $originid : 0);

		print '<tr>';
		print '<td>' . $langs->trans("Project") . '</td><td colspan="2">';

		$numprojet = $formproject->select_projects(($soc->id > 0 ? $soc->id : -1), $projectid, 'projectid', 0, 0, 1, 1);
		print ' &nbsp; <a href="'.DOL_URL_ROOT.'/projet/card.php?socid=' . $soc->id . '&action=create&status=1&backtopage='.urlencode($_SERVER["PHP_SELF"].'?action=create&socid='.$soc->id).'">' . $langs->trans("AddProject") . '</a>';

		print '</td>';
		print '</tr>';
	}

	// Multicurrency
	if (! empty($conf->multicurrency->enabled))
	{
		print '<tr>';
		print '<td>'.fieldLabel('Currency','multicurrency_code').'</td>';
		print '<td colspan="3" class="maxwidthonsmartphone">';
		print $form->selectMultiCurrency($currency_code, 'multicurrency_code');
		print '</td></tr>';
	}

	// Other attributes
	$parameters = array('colspan' => ' colspan="3"');
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	if (empty($reshook)) {
		print $object->showOptionals($extrafields, 'edit');
	}


	// Lines from source
	if (! empty($origin) && ! empty($originid) && is_object($objectsrc))
	{
		// TODO for compatibility
		if ($origin == 'contrat') {
			// Calcul contrat->price (HT), contrat->total (TTC), contrat->tva
			$objectsrc->remise_absolue = $remise_absolue;
			$objectsrc->remise_percent = $remise_percent;
			$objectsrc->update_price(1, - 1, 1);
		}

		print "\n<!-- " . $classname . " info -->";
		print "\n";
		print '<input type="hidden" name="amount"         value="' . $objectsrc->total_ht . '">' . "\n";
		print '<input type="hidden" name="total"          value="' . $objectsrc->total_ttc . '">' . "\n";
		print '<input type="hidden" name="tva"            value="' . $objectsrc->total_tva . '">' . "\n";
		print '<input type="hidden" name="origin"         value="' . $objectsrc->element . '">';
		print '<input type="hidden" name="originid"       value="' . $objectsrc->id . '">';

		print '<tr><td>' . $langs->trans('CommRequest') . '</td><td colspan="2">' . $objectsrc->getNomUrl(1) . '</td></tr>';
		print '<tr><td>' . $langs->trans('TotalHT') . '</td><td colspan="2">' . price($objectsrc->total_ht) . '</td></tr>';
		print '<tr><td>' . $langs->trans('TotalVAT') . '</td><td colspan="2">' . price($objectsrc->total_tva) . "</td></tr>";
		if ($mysoc->localtax1_assuj == "1" || $objectsrc->total_localtax1 != 0 ) 		// Localtax1
		{
			print '<tr><td>' . $langs->transcountry("AmountLT1", $mysoc->country_code) . '</td><td colspan="2">' . price($objectsrc->total_localtax1) . "</td></tr>";
		}

		if ($mysoc->localtax2_assuj == "1" || $objectsrc->total_localtax2 != 0) 		// Localtax2
		{
			print '<tr><td>' . $langs->transcountry("AmountLT2", $mysoc->country_code) . '</td><td colspan="2">' . price($objectsrc->total_localtax2) . "</td></tr>";
		}
		print '<tr><td>' . $langs->trans('TotalTTC') . '</td><td colspan="2">' . price($objectsrc->total_ttc) . "</td></tr>";

		if (!empty($conf->multicurrency->enabled))
		{
			print '<tr><td>' . $langs->trans('MulticurrencyTotalHT') . '</td><td colspan="2">' . price($objectsrc->multicurrency_total_ht) . '</td></tr>';
			print '<tr><td>' . $langs->trans('MulticurrencyTotalVAT') . '</td><td colspan="2">' . price($objectsrc->multicurrency_total_tva) . "</td></tr>";
			print '<tr><td>' . $langs->trans('MulticurrencyTotalTTC') . '</td><td colspan="2">' . price($objectsrc->multicurrency_total_ttc) . "</td></tr>";
		}
	}

	print "</table>\n";


	/*
	 * Combobox pour la fonction de copie
 	 */

	if (empty($conf->global->SUPPLIER_PROPOSAL_CLONE_ON_CREATE_PAGE)) print '<input type="hidden" name="createmode" value="empty">';

	if (! empty($conf->global->SUPPLIER_PROPOSAL_CLONE_ON_CREATE_PAGE))
	{
		print '<br><table>';

		// For backward compatibility
		print '<tr>';
		print '<td><input type="radio" name="createmode" value="copy"></td>';
		print '<td>' . $langs->trans("CopyAskFrom") . ' </td>';
		print '<td>';
		$liste_ask = array();
		$liste_ask [0] = '';

		$sql = "SELECT p.rowid as id, p.ref, s.nom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "supplier_proposal p";
		$sql .= ", " . MAIN_DB_PREFIX . "societe s";
		$sql .= " WHERE s.rowid = p.fk_soc";
		$sql .= " AND p.entity = " . $conf->entity;
		$sql .= " AND p.fk_statut <> ".SupplierProposal::STATUS_DRAFT;
		$sql .= " ORDER BY Id";

		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$row = $db->fetch_row($resql);
				$askPriceSupplierRefAndSocName = $row [1] . " - " . $row [2];
				$liste_ask [$row [0]] = $askPriceSupplierRefAndSocName;
				$i ++;
			}
			print $form->selectarray("copie_supplier_proposal", $liste_ask, 0);
		} else {
			dol_print_error($db);
		}
		print '</td></tr>';

		print '<tr><td class="tdtop"><input type="radio" name="createmode" value="empty" checked></td>';
		print '<td valign="top" colspan="2">' . $langs->trans("CreateEmptyAsk") . '</td></tr>';
	}

	if (! empty($conf->global->SUPPLIER_PROPOSAL_CLONE_ON_CREATE_PAGE)) print '</table>';

	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" value="' . $langs->trans("CreateDraft") . '">';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	print '<input type="button" class="button" value="' . $langs->trans("Cancel") . '" onClick="javascript:history.go(-1)">';
	print '</div>';

	print "</form>";


	// Show origin lines
	if (! empty($origin) && ! empty($originid) && is_object($objectsrc)) {
		print '<br>';

		$title = $langs->trans('ProductsAndServices');
		print load_fiche_titre($title);

		print '<table class="noborder" width="100%">';

		$objectsrc->printOriginLinesList();

		print '</table>';
	}
} else {
	/*
	 * Show object in view mode
	 */

	$soc = new Societe($db);
	$soc->fetch($object->socid);

	$head = supplier_proposal_prepare_head($object);
	dol_fiche_head($head, 'comm', $langs->trans('CommRequest'), -1, 'supplier_proposal');

	$formconfirm = '';

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array(
							// 'text' => $langs->trans("ConfirmClone"),
							// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
							// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' =>
							// 1),
							array('type' => 'other','name' => 'socid','label' => $langs->trans("SelectThirdParty"),'value' => $form->select_company(GETPOST('socid', 'int'), 'socid', 's.fournisseur=1')));
		// Paiement incomplet. On demande si motif = escompte ou autre
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('CloneAsk'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirm delete
	else if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteAsk'), $langs->trans('ConfirmDeleteAsk', $object->ref), 'confirm_delete', '', 0, 1);
	}

	// Confirm reopen
	else if ($action == 'reopen') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ReOpen'), $langs->trans('ConfirmReOpenAsk', $object->ref), 'confirm_reopen', '', 0, 1);
	}

	// Confirmation delete product/service line
	else if ($action == 'ask_deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&lineid=' . $lineid, $langs->trans('DeleteProductLine'), $langs->trans('ConfirmDeleteProductLine'), 'confirm_deleteline', '', 0, 1);
	}

	// Confirm validate askprice
	else if ($action == 'validate') {
		$error = 0;

		// on verifie si l'objet est en numerotation provisoire
		$ref = substr($object->ref, 1, 4);
		if ($ref == 'PROV') {
			$numref = $object->getNextNumRef($soc);
			if (empty($numref)) {
				$error ++;
				setEventMessages($object->error, $object->errors, 'errors');
			}
		} else {
			$numref = $object->ref;
		}

		$text = $langs->trans('ConfirmValidateAsk', $numref);
		if (! empty($conf->notification->enabled)) {
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('SUPPLIER_PROPOSAL_VALIDATE', $object->socid, $object);
		}

		if (! $error)
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ValidateAsk'), $text, 'confirm_validate', '', 0, 1);
	}

	// Call Hook formConfirm
	$parameters = array('lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


	// Supplier proposal card
	$linkback = '<a href="' . DOL_URL_ROOT . '/supplier_proposal/list.php?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';


	$morehtmlref='<div class="refidno">';
	// Ref supplier
	//$morehtmlref.=$form->editfieldkey("RefSupplier", 'ref_supplier', $object->ref_supplier, $object, $user->rights->fournisseur->commande->creer, 'string', '', 0, 1);
	//$morehtmlref.=$form->editfieldval("RefSupplier", 'ref_supplier', $object->ref_supplier, $object, $user->rights->fournisseur->commande->creer, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref.=$langs->trans('ThirdParty') . ' : ' . $object->thirdparty->getNomUrl(1);
	if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $object->thirdparty->id > 0) $morehtmlref.=' (<a href="'.DOL_URL_ROOT.'/supplier_proposal/list.php?socid='.$object->thirdparty->id.'&search_societe='.urlencode($object->thirdparty->name).'">'.$langs->trans("OtherProposals").'</a>)';
	// Project
	if (! empty($conf->projet->enabled))
	{
		$langs->load("projects");
		$morehtmlref.='<br>'.$langs->trans('Project') . ' ';
		if ($user->rights->supplier_proposal->creer)
		{
			if ($action != 'classify')
				$morehtmlref.='<a href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
				if ($action == 'classify') {
					//$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
					$morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
					$morehtmlref.='<input type="hidden" name="action" value="classin">';
					$morehtmlref.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
					$morehtmlref.=$formproject->select_projects((empty($conf->global->PROJECT_CAN_ALWAYS_LINK_TO_ALL_SUPPLIERS)?$object->socid:-1), $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
					$morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
					$morehtmlref.='</form>';
				} else {
					$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
				}
		} else {
			if (! empty($object->fk_project)) {
				$proj = new Project($db);
				$proj->fetch($object->fk_project);
				$morehtmlref.='<a href="'.DOL_URL_ROOT.'/projet/card.php?id=' . $object->fk_project . '" title="' . $langs->trans('ShowProject') . '">';
				$morehtmlref.=$proj->ref;
				$morehtmlref.='</a>';
			} else {
				$morehtmlref.='';
			}
		}
	}
	$morehtmlref.='</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';

	print '<table class="border" width="100%">';

	// Relative and absolute discounts
	if (! empty($conf->global->FACTURE_DEPOSITS_ARE_JUST_PAYMENTS)) {
		$filterabsolutediscount = "fk_invoice_supplier_source IS NULL"; // If we want deposit to be substracted to payments only and not to total of final invoice
		$filtercreditnote = "fk_invoice_supplier_source IS NOT NULL"; // If we want deposit to be substracted to payments only and not to total of final invoice
	} else {
		$filterabsolutediscount = "fk_invoice_supplier_source IS NULL OR (description LIKE '(DEPOSIT)%' AND description NOT LIKE '(EXCESS PAID)%')";
		$filtercreditnote = "fk_invoice_supplier_source IS NOT NULL AND (description NOT LIKE '(DEPOSIT)%' OR description LIKE '(EXCESS PAID)%')";
	}

	print '<tr><td class="titlefield">' . $langs->trans('Discounts') . '</td><td>';

	$absolute_discount = $soc->getAvailableDiscounts('', $filterabsolutediscount, 0, 1);
	$absolute_creditnote = $soc->getAvailableDiscounts('', $filtercreditnote, 0, 1);
	$absolute_discount = price2num($absolute_discount, 'MT');
	$absolute_creditnote = price2num($absolute_creditnote, 'MT');

	$thirdparty = $soc;
	$discount_type = 1;
	$backtopage = urlencode($_SERVER["PHP_SELF"] . '?id=' . $object->id);
	include DOL_DOCUMENT_ROOT.'/core/tpl/object_discounts.tpl.php';

	print '</td></tr>';

	// Payment term
	print '<tr><td class="titlefield">';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('PaymentConditionsShort');
	print '</td>';
	if ($action != 'editconditions' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editconditions&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetConditions'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == 'editconditions') {
		$form->form_conditions_reglement($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->cond_reglement_id, 'cond_reglement_id', 1);
	} else {
		$form->form_conditions_reglement($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->cond_reglement_id, 'none', 1);
	}
	print '</td>';
	print '</tr>';

	// Delivery date
	$langs->load('deliveries');
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('DeliveryDate');
	print '</td>';
	if ($action != 'editdate_livraison' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editdate_livraison&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetDeliveryDate'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == 'editdate_livraison') {
		print '<form name="editdate_livraison" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
		print '<input type="hidden" name="action" value="setdate_livraison">';
		print $form->selectDate($object->date_livraison, 'liv_', '', '', '', "editdate_livraison");
		print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
		print '</form>';
	} else {
		print dol_print_date($object->date_livraison, 'daytext');
	}
	print '</td>';
	print '</tr>';

	// Payment mode
	print '<tr>';
	print '<td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('PaymentMode');
	print '</td>';
	if ($action != 'editmode' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editmode&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetMode'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == 'editmode') {
		$form->form_modes_reglement($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->mode_reglement_id, 'mode_reglement_id', 'DBIT', 1, 1);
	} else {
		$form->form_modes_reglement($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->mode_reglement_id, 'none');
	}
	print '</td></tr>';

	// Multicurrency
	if (! empty($conf->multicurrency->enabled))
	{
		// Multicurrency code
		print '<tr>';
		print '<td>';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print fieldLabel('Currency','multicurrency_code');
		print '</td>';
		if ($action != 'editmulticurrencycode' && ! empty($object->brouillon))
			print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editmulticurrencycode&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetMultiCurrencyCode'), 1) . '</a></td>';
		print '</tr></table>';
		print '</td><td colspan="3">';
		if ($action == 'editmulticurrencycode') {
			$form->form_multicurrency_code($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->multicurrency_code, 'multicurrency_code');
		} else {
			$form->form_multicurrency_code($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->multicurrency_code, 'none');
		}
		print '</td></tr>';

		// Multicurrency rate
		print '<tr>';
		print '<td>';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print fieldLabel('CurrencyRate','multicurrency_tx');
		print '</td>';
		if ($action != 'editmulticurrencyrate' && ! empty($object->brouillon) && $object->multicurrency_code && $object->multicurrency_code != $conf->currency)
			print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editmulticurrencyrate&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetMultiCurrencyCode'), 1) . '</a></td>';
		print '</tr></table>';
		print '</td><td colspan="3">';
		if ($action == 'editmulticurrencyrate' || $action == 'actualizemulticurrencyrate') {
			if($action == 'actualizemulticurrencyrate') {
   				list($object->fk_multicurrency, $object->multicurrency_tx) = MultiCurrency::getIdAndTxFromCode($object->db, $object->multicurrency_code);
			}
			$form->form_multicurrency_rate($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->multicurrency_tx, 'multicurrency_tx', $object->multicurrency_code);
		} else {
			$form->form_multicurrency_rate($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->multicurrency_tx, 'none', $object->multicurrency_code);
			if($object->statut == $object::STATUS_DRAFT && $object->multicurrency_code && $object->multicurrency_code != $conf->currency) {
				print '<div class="inline-block"> &nbsp; &nbsp; &nbsp; &nbsp; ';
				print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=actualizemulticurrencyrate">'.$langs->trans("ActualizeCurrency").'</a>';
				print '</div>';
			}
		}
		print '</td></tr>';
	}

	/* Not for supplier proposals
	if ($soc->outstanding_limit)
	{
		// Outstanding Bill
		print '<tr><td>';
		print $langs->trans('OutstandingBill');
		print '</td><td align=right colspan="3">';
		print price($soc->get_OutstandingBill()) . ' / ';
		print price($soc->outstanding_limit, 0, '', 1, - 1, - 1, $conf->currency);
		print '</td>';
		print '</tr>';
	}*/

	if (! empty($conf->global->BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL) && ! empty($conf->banque->enabled))
	{
		// Bank Account
		print '<tr><td>';
		print '<table width="100%" class="nobordernopadding"><tr><td>';
		print $langs->trans('BankAccount');
		print '</td>';
		if ($action != 'editbankaccount' && $user->rights->supplier_proposal->creer)
			print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editbankaccount&amp;id='.$object->id.'">'.img_edit($langs->trans('SetBankAccount'),1).'</a></td>';
		print '</tr></table>';
		print '</td><td colspan="3">';
		if ($action == 'editbankaccount') {
			$form->formSelectAccount($_SERVER['PHP_SELF'].'?id='.$object->id, $object->fk_account, 'fk_account', 1);
		} else {
			$form->formSelectAccount($_SERVER['PHP_SELF'].'?id='.$object->id, $object->fk_account, 'none');
		}
		print '</td>';
		print '</tr>';
	}

	// Other attributes
	$cols = 2;
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';

	print '</div>';
	print '<div class="fichehalfright">';
	print '<div class="ficheaddleft">';
	print '<div class="underbanner clearboth"></div>';

	print '<table class="border centpercent">';

	if (!empty($conf->multicurrency->enabled) && ($object->multicurrency_code != $conf->currency))
	{
		// Multicurrency Amount HT
		print '<tr><td height="10" class="titlefieldmiddle">' . fieldLabel('MulticurrencyAmountHT','multicurrency_total_ht') . '</td>';
		print '<td>' . price($object->multicurrency_total_ht, '', $langs, 0, - 1, - 1, (!empty($object->multicurrency_code) ? $object->multicurrency_code : $conf->currency)) . '</td>';
		print '</tr>';

		// Multicurrency Amount VAT
		print '<tr><td height="10">' . fieldLabel('MulticurrencyAmountVAT','multicurrency_total_tva') . '</td>';
		print '<td>' . price($object->multicurrency_total_tva, '', $langs, 0, - 1, - 1, (!empty($object->multicurrency_code) ? $object->multicurrency_code : $conf->currency)) . '</td>';
		print '</tr>';

		// Multicurrency Amount TTC
		print '<tr><td height="10">' . fieldLabel('MulticurrencyAmountTTC','multicurrency_total_ttc') . '</td>';
		print '<td>' . price($object->multicurrency_total_ttc, '', $langs, 0, - 1, - 1, (!empty($object->multicurrency_code) ? $object->multicurrency_code : $conf->currency)) . '</td>';
		print '</tr>';
	}

	// Amount HT
	print '<tr><td class="titlefieldmiddle">' . $langs->trans('AmountHT') . '</td>';
	print '<td>' . price($object->total_ht, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
	print '</tr>';

	// Amount VAT
	print '<tr><td>' . $langs->trans('AmountVAT') . '</td>';
	print '<td>' . price($object->total_tva, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
	print '</tr>';

	// Amount Local Taxes
	if ($mysoc->localtax1_assuj == "1" || $object->total_localtax1 != 0) 	// Localtax1
	{
		print '<tr><td>' . $langs->transcountry("AmountLT1", $mysoc->country_code) . '</td>';
		print '<td class="nowrap">' . price($object->total_localtax1, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
		print '</tr>';
	}
	if ($mysoc->localtax2_assuj == "1" || $object->total_localtax2 != 0) 	// Localtax2
	{
		print '<tr><td height="10">' . $langs->transcountry("AmountLT2", $mysoc->country_code) . '</td>';
		print '<td class="nowrap">' . price($object->total_localtax2, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
		print '</tr>';
	}

	// Amount TTC
	print '<tr><td height="10">' . $langs->trans('AmountTTC') . '</td>';
	print '<td class="nowrap">' . price($object->total_ttc, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
	print '</tr>';

	print '</table>';

	// Margin Infos
	/*if (! empty($conf->margin->enabled)) {
	   $formmargin->displayMarginInfos($object);
	}*/

	print '</div>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div><br>';

	if (! empty($conf->global->MAIN_DISABLE_CONTACTS_TAB)) {
		$blocname = 'contacts';
		$title = $langs->trans('ContactsAddresses');
		include DOL_DOCUMENT_ROOT . '/core/tpl/bloc_showhide.tpl.php';
	}

	if (! empty($conf->global->MAIN_DISABLE_NOTES_TAB)) {
		$blocname = 'notes';
		$title = $langs->trans('Notes');
		include DOL_DOCUMENT_ROOT . '/core/tpl/bloc_showhide.tpl.php';
	}

	/*
	 * Lines
	 */

	// Show object lines
	$result = $object->getLinesArray();

	print '	<form name="addproduct" id="addproduct" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '#add' : '#line_' . GETPOST('lineid')) . '" method="POST">
	<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">
	<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
	<input type="hidden" name="mode" value="">
	<input type="hidden" name="id" value="' . $object->id . '">
	';

	if (! empty($conf->use_javascript_ajax) && $object->statut == SupplierProposal::STATUS_DRAFT) {
		include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
	}

	print '<div class="div-table-responsive-no-min">';
	print '<table id="tablelines" class="noborder noshadow" width="100%">';

	// Add free products/services form
	global $forceall, $senderissupplier, $dateSelector;
	$forceall=1; $dateSelector=0;
	$senderissupplier=2;	// $senderissupplier=2 is same than 1 but disable test on minimum qty.
	if (! empty($conf->global->SUPPLIER_PROPOSAL_WITH_PREDEFINED_PRICES_ONLY)) $senderissupplier=1;

	if (! empty($object->lines))
		$ret = $object->printObjectLines($action, $soc, $mysoc, $lineid, 1);

	// Form to add new line
	if ($object->statut == SupplierProposal::STATUS_DRAFT && $user->rights->supplier_proposal->creer)
	{
		if ($action != 'editline')
		{
			// Add products/services form
			$object->formAddObjectLine(1, $soc, $mysoc);

			$parameters = array();
			$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		}
	}

	print '</table>';
	print '</div>';
	print "</form>\n";

	dol_fiche_end();

	if ($action == 'statut')
	{
		// Form to set proposal accepted/refused
		$form_close = '<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
		if (! empty($conf->global->SUPPLIER_PROPOSAL_UPDATE_PRICE_ON_SUPPlIER_PROPOSAL)) $form_close .= '<p class="notice">'.$langs->trans('SupplierProposalRefFournNotice').'</p>';  // TODO Suggest a permanent checkbox instead of option
		$form_close .= '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
		$form_close .= '<table class="border" width="100%">';
		$form_close .= '<tr><td width="150"  align="left">' . $langs->trans("CloseAs") . '</td><td align="left">';
		$form_close .= '<input type="hidden" name="action" value="setstatut">';
		$form_close .= '<select id="statut" name="statut" class="flat">';
		$form_close .= '<option value="0">&nbsp;</option>';
		$form_close .= '<option value="2">' . $langs->trans('SupplierProposalStatusSigned') . '</option>';
		$form_close .= '<option value="3">' . $langs->trans('SupplierProposalStatusNotSigned') . '</option>';
		$form_close .= '</select>';
		$form_close .= '</td></tr>';
		$form_close .= '<tr><td width="150" align="left">' . $langs->trans('Note') . '</td><td align="left"><textarea cols="70" rows="' . ROWS_3 . '" wrap="soft" name="note">';
		$form_close .= $object->note;
		$form_close .= '</textarea></td></tr>';
		$form_close .= '<tr><td align="center" colspan="2">';
		$form_close .= '<input type="submit" class="button" name="validate" value="' . $langs->trans('Save') . '">';
		$form_close .= ' &nbsp; <input type="submit" class="button" name="cancel" value="' . $langs->trans('Cancel') . '">';
		$form_close .= '<a name="acceptedrefused">&nbsp;</a>';
		$form_close .= '</td>';
		$form_close .= '</tr></table></form>';

		print $form_close;
	}

	/*
	 * Boutons Actions
	 */
	if ($action != 'presend') {
		print '<div class="tabsAction">';

		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been
																									   // modified by hook
		if (empty($reshook))
		{
			if ($action != 'statut' && $action != 'editline')
			{
				// Validate
				if ($object->statut == SupplierProposal::STATUS_DRAFT && $object->total_ttc >= 0 && count($object->lines) > 0 &&
					((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->supplier_proposal->creer))
	   				|| (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->supplier_proposal->validate_advance)))
				) {
					if (count($object->lines) > 0)
						print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=validate">' . $langs->trans('Validate') . '</a></div>';
					// else print '<a class="butActionRefused" href="#">'.$langs->trans('Validate').'</a>';
				}

				// Edit
				if ($object->statut == SupplierProposal::STATUS_VALIDATED && $user->rights->supplier_proposal->creer) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=modif">' . $langs->trans('Modify') . '</a></div>';
				}

				// ReOpen
				if (($object->statut == SupplierProposal::STATUS_SIGNED || $object->statut == SupplierProposal::STATUS_NOTSIGNED || $object->statut == SupplierProposal::STATUS_CLOSE) && $user->rights->supplier_proposal->cloturer) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=reopen' . (empty($conf->global->MAIN_JUMP_TAG) ? '' : '#reopen') . '"';
					print '>' . $langs->trans('ReOpen') . '</a></div>';
				}

				// Send
				if ($object->statut == SupplierProposal::STATUS_VALIDATED || $object->statut == SupplierProposal::STATUS_SIGNED) {
					if (empty($conf->global->MAIN_USE_ADVANCED_PERMS) || $user->rights->supplier_proposal->send_advance) {
						print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a></div>';
					} else
						print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('SendMail') . '</a></div>';
				}

				// Create an order
				if (! empty($conf->fournisseur->enabled) && $object->statut == SupplierProposal::STATUS_SIGNED) {
					if ($user->rights->fournisseur->commande->creer) {
						print '<div class="inline-block divButAction"><a class="butAction" href="' . DOL_URL_ROOT . '/fourn/commande/card.php?action=create&amp;origin=' . $object->element . '&amp;originid=' . $object->id . '&amp;socid=' . $object->socid . '">' . $langs->trans("AddOrder") . '</a></div>';
					}
				}

				// Set accepted/refused
				if ($object->statut == SupplierProposal::STATUS_VALIDATED && $user->rights->supplier_proposal->cloturer) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=statut' . (empty($conf->global->MAIN_JUMP_TAG) ? '' : '#acceptedrefused') . '"';
					print '>' . $langs->trans('SetAcceptedRefused') . '</a></div>';
				}

				// Close
				if ($object->statut == SupplierProposal::STATUS_SIGNED && $user->rights->supplier_proposal->cloturer) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=close' . (empty($conf->global->MAIN_JUMP_TAG) ? '' : '#close') . '"';
					print '>' . $langs->trans('Close') . '</a></div>';
				}

				// Clone
				if ($user->rights->supplier_proposal->creer) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $object->socid . '&amp;action=clone&amp;object=' . $object->element . '">' . $langs->trans("ToClone") . '</a></div>';
				}

				// Delete
				if (($object->statut == SupplierProposal::STATUS_DRAFT && $user->rights->supplier_proposal->creer) || $user->rights->supplier_proposal->supprimer) {
					print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete"';
					print '>' . $langs->trans('Delete') . '</a></div>';
				}
			}
		}

		print '</div>';
	}

	if ($action != 'presend')
	{
		print '<div class="fichecenter"><div class="fichehalfleft">';

		/*
		 * Documents generes
		 */
		$filename = dol_sanitizeFileName($object->ref);
		$filedir = $conf->supplier_proposal->dir_output . "/" . dol_sanitizeFileName($object->ref);
		$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		$genallowed = $user->rights->supplier_proposal->lire;
		$delallowed = $user->rights->supplier_proposal->creer;

		print $formfile->showdocuments('supplier_proposal', $filename, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', 0, '', $soc->default_lang);


		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('supplier_proposal'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, 'supplier_proposal', $socid, 1);

		print '</div></div></div>';
	}

	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail='supplier_proposal_send';
	$defaulttopic='SendAskRef';
	$diroutput = $conf->supplier_proposal->dir_output;
	$trackid = 'spr'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
