<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2005-2012 Maxime Kohlhaas      <mko@atm-consulting.fr>
 * Copyright (C) 2015-2021 Frédéric France      <frederic.france@netlogic.fr>
 * Copyright (C) 2015      Juanjo Menent	    <jmenent@2byte.es>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/core/boxes/box_produits_alerte_stock.php
 *	\ingroup    produits
 *	\brief      Module to generate box of products with too low stock
 */

include_once DOL_DOCUMENT_ROOT.'/core/boxes/modules_boxes.php';
include_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';


/**
 * Class to manage the box to show too low stocks products
 */
class box_produits_alerte_stock extends ModeleBoxes
{
	public $boxcode = "productsalertstock";
	public $boximg = "object_product";
	public $boxlabel = "BoxProductsAlertStock";
	public $depends = array("produit");

	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	public $param;

	public $info_box_head = array();
	public $info_box_contents = array();


	/**
	 *  Constructor
	 *
	 *  @param  DoliDB	$db      	Database handler
	 *  @param	string	$param		More parameters
	 */
	public function __construct($db, $param = '')
	{
		global $conf, $user;

		$this->db = $db;

		$listofmodulesforexternal = explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL);
		$tmpentry = array('enabled'=>((!empty($conf->product->enabled) || !empty($conf->service->enabled)) && !empty($conf->stock->enabled)), 'perms'=>!empty($user->rights->stock->lire), 'module'=>'product|service|stock');
		$showmode = isVisibleToUserType(($user->socid > 0 ? 1 : 0), $tmpentry, $listofmodulesforexternal);
		$this->hidden = ($showmode != 1);
	}

	/**
	 *  Load data into info_box_contents array to show array later.
	 *
	 *  @param	int		$max        Maximum number of records to load
	 *  @return	void
	 */
	public function loadBox($max = 5)
	{
		global $user, $langs, $conf, $hookmanager;

		$this->max = 50; // Modification : Maximum de lignes affichées

		include_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		$productstatic = new Product($this->db);

		$this->info_box_head = array('text' => $langs->trans("BoxTitleProductsAlertStock", $max));

		if (($user->rights->produit->lire || $user->rights->service->lire) && $user->rights->stock->lire) {
			$sql = "SELECT p.rowid, p.label, p.price, p.ref, p.price_base_type, p.price_ttc, p.fk_product_type, p.tms, p.tosell, p.tobuy, p.barcode, p.seuil_stock_alerte, p.entity,";
			$sql .= " p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export,";
			$sql .= " p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export,";
			$sql .= " SUM(".$this->db->ifsql("s.reel IS NULL", "0", "s.reel").") as total_stock";
			$sql .= " FROM ".MAIN_DB_PREFIX."product as p";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_stock as s on p.rowid = s.fk_product";
			$sql .= ' WHERE p.entity IN ('.getEntity($productstatic->element).')';
			$sql .= " AND p.tobuy = 1 OR p.tosell = 1 AND p.seuil_stock_alerte > 0"; // Modification : Passage d'alerte sur produit en achat
			if (empty($user->rights->produit->lire)) {
				$sql .= ' AND p.fk_product_type != 0'; // Modification : <> en !=
			}
			if (empty($user->rights->service->lire)) {
				$sql .= ' AND p.fk_product_type != 1'; // Modification : <> en !=
			}
			// Add where from hooks
			if (is_object($hookmanager)) {
				$parameters = array('boxproductalertstocklist' => 1, 'boxcode' => $this->boxcode);
				$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $productstatic); // Note that $action and $object may have been modified by hook
				$sql .= $hookmanager->resPrint;
			}
			$sql .= " GROUP BY p.rowid, p.ref, p.label, p.price, p.price_base_type, p.price_ttc, p.fk_product_type, p.tms, p.tosell, p.tobuy, p.barcode, p.seuil_stock_alerte, p.entity,";
			$sql .= " p.accountancy_code_sell, p.accountancy_code_sell_intra, p.accountancy_code_sell_export,";
			$sql .= " p.accountancy_code_buy, p.accountancy_code_buy_intra, p.accountancy_code_buy_export";
			$sql .= " HAVING SUM(".$this->db->ifsql("s.reel IS NULL", "0", "s.reel").") < p.seuil_stock_alerte";
			$sql .= $this->db->order('p.seuil_stock_alerte', 'DESC');
			$sql .= $this->db->plimit($max, 0);

			$result = $this->db->query($sql);
			if ($result) {
				$langs->load("stocks");
				$num = $this->db->num_rows($result);
				$line = 0;
				while ($line < $num) {
					$objp = $this->db->fetch_object($result);
					$datem = $this->db->jdate($objp->tms);
					$price = '';
					$price_base_type = '';

					// Multilangs
					if (!empty($conf->global->MAIN_MULTILANGS)) { // si l'option est active
						$sqld = "SELECT label";
						$sqld .= " FROM ".MAIN_DB_PREFIX."product_lang";
						$sqld .= " WHERE fk_product = ".((int) $objp->rowid);
						$sqld .= " AND lang = '".$this->db->escape($langs->getDefaultLang())."'";
						$sqld .= " LIMIT 1";

						$resultd = $this->db->query($sqld);
						if ($resultd) {
							$objtp = $this->db->fetch_object($resultd);
							if (isset($objtp->label) && $objtp->label != '') {
								$objp->label = $objtp->label;
							}
						}
					}

					// Modification : Requète commande en cours [145-167]
					global $db;
					$sqle = "SELECT d.qty, o.fk_statut";
					$sqle .= " FROM llx_commande_fournisseurdet as d";
					$sqle .= " INNER JOIN llx_product as p on d.fk_product = p.rowid";
					$sqle .= " INNER JOIN llx_commande_fournisseur as o on d.fk_commande = o.rowid";
					$sqle .= " WHERE d.fk_product = ".$objp->rowid." AND o.fk_statut < '5'";
					$sqle .= " GROUP BY o.fk_statut";

					$resulte = $db->query($sqle);
					
					$obje = $resulte->fetch_all(MYSQLI_ASSOC);
					
					$sqlf = "SELECT SUM(r.qty) as sum_qty_received";
					$sqlf .= " FROM llx_commande_fournisseur_dispatch as r";
					$sqlf .= " INNER JOIN llx_commande_fournisseur as o on r.fk_commande = o.rowid";
					$sqlf .= " WHERE r.fk_product = ".$objp->rowid." AND o.fk_statut = 4";
					
					$resultf = $db->query($sqlf);

					$objf = $db->fetch_object($resultf);
					$objp->received_qty = $objf->sum_qty_received;

					$productstatic->id = $objp->rowid;
					$productstatic->ref = $objp->ref;
					$productstatic->type = $objp->fk_product_type;
					$productstatic->label = $objp->label;
					$productstatic->entity = $objp->entity;
					$productstatic->barcode = $objp->barcode;
					$productstatic->status = $objp->tosell;
					$productstatic->status_buy = $objp->tobuy;
					$productstatic->accountancy_code_sell = $objp->accountancy_code_sell;
					$productstatic->accountancy_code_sell_intra = $objp->accountancy_code_sell_intra;
					$productstatic->accountancy_code_sell_export = $objp->accountancy_code_sell_export;
					$productstatic->accountancy_code_buy = $objp->accountancy_code_buy;
					$productstatic->accountancy_code_buy_intra = $objp->accountancy_code_buy_intra;
					$productstatic->accountancy_code_buy_export = $objp->accountancy_code_buy_export;

					$this->info_box_contents[$line][] = array(
						'td' => 'class="tdoverflowmax150 maxwidth150onsmartphone" width="150px"', // Modification :Taille
						'text' => $productstatic->getNomUrl(1),
						'asis' => 1,
					);

					$this->info_box_contents[$line][] = array(
						'td' => 'class="tdoverflowmax150 maxwidth150onsmartphone"', // Modification :Taille
						'text' => $objp->label,
					);

					// Modification : Affichage du statut de commande [194-241]
					$this->info_box_contents[$line][] = array(
						'td' => 'class="center" width="125px"',
						'text' => price2num($objp->total_stock, 'MS').' / '.$objp->seuil_stock_alerte,
						'text2'=>img_warning($langs->transnoentitiesnoconv("StockLowerThanLimit", $objp->seuil_stock_alerte))
					);

						// Message initiale de réassort
						$info_reap = '<a href="product/stock/product.php?id='.$productstatic->id.'"><i class="fas fa-search" style="color: grey;" title="Voir le détail des stocks"></i></a>';
						
						//Remise a zéro des valeurs initiales
						$reap_in_draft = $reap_awaiting = $reap_ordered = 0;
						$count_draft = $count_awaiting = $count_ordered = 0;
						$qty_in_draft = $qty_awaiting = $ordered_qty = 0;
					
						/* Somme colonne qty du tableau
						// Première Solution somme globale
						// $ordered_qty = array_sum(array_column($obje, 'qty'));
						// Deuxième solution somme en fonction du statut */
						// Somme des qty en fonction du statut de commande et modification du message a afficher
						foreach ($obje as $item) {
							if ((int)$item['fk_statut'] == 0) {
								$qty_in_draft += $item['qty'];
								$count_draft++;
								$reap_in_draft++;
							} elseif ((int)$item['fk_statut'] == 1 || (int)$item['fk_statut'] == 2) {
								$qty_awaiting += $item['qty'];
								$count_awaiting++;
								$reap_awaiting++;
							} elseif ((int)$item['fk_statut'] == 3 || (int)$item['fk_statut'] == 4) {
						    	$ordered_qty += $item['qty'];
								$count_ordered++;
								$reap_ordered++;
							}
						}
						// Soustraction de ce qui a déjà été reçu
						$qty_to_receive = $ordered_qty - $objp->received_qty;
						
						// Icones en fonction du statut
						if ($reap_in_draft > 0) {
							$info_reap .= ' <a href="product/stats/commande_fournisseur.php?id='.$productstatic->id.'"><i class="fas fa-edit" style="color: grey;" title="'.$qty_in_draft.' dans '.$count_draft.' commande(s) en brouillon => Voir les détails"></i></a>';
						}
						if ($reap_awaiting > 0) {
							$info_reap .= ' <a href="product/stats/commande_fournisseur.php?id='.$productstatic->id.'"><i class="fas fa-history" style="color: grey;" title="'.$qty_awaiting.' dans '.$count_awaiting.' commande(s) en attente => Voir les détails"></i></a>';
						}
						if ($reap_ordered > 0 && $qty_to_receive > 0) {
							$info_reap .= ' <a href="product/stats/commande_fournisseur.php?id='.$productstatic->id.'"><i class="fas fa-shipping-fast" title="'.$qty_to_receive.' en cours de livraison dans '.$count_ordered.' commande(s) => Voir les détails"></i></a>';
						}

					$this->info_box_contents[$line][] = array(
						// Modification : Affichage statut [244-247]
						'td' => 'class="center" width="75px"',
						'text' => $info_reap,
						'asis' => 1
					);

					$this->info_box_contents[$line][] = array(
						'td' => 'class="right" width="18px"', // Modification : Taille
						'text' => '<span class="statusrefsell">'.$productstatic->LibStatut($objp->tosell, 3, 0).'</span>',
						'asis' => 1
					);

					$this->info_box_contents[$line][] = array(
						'td' => 'class="right" width="18px"', // Modification : Taille
						'text' => '<span class="statusrefbuy">'.$productstatic->LibStatut($objp->tobuy, 3, 1).'</span>',
						'asis' => 1
					);

					$line++;
				}
				if ($num == 0) {
					$this->info_box_contents[$line][0] = array(
						'td' => 'class="center"',
						'text'=>$langs->trans("NoTooLowStockProducts"),
					);
				}

				$this->db->free($result);
			} else {
				$this->info_box_contents[0][0] = array(
					'td' => '',
					'maxlength'=>500,
					'text' => ($this->db->error().' sql='.$sql),
				);
			}
		} else {
			$this->info_box_contents[0][0] = array(
				'td' => 'class="nohover opacitymedium left"',
				'text' => $langs->trans("ReadPermissionNotAllowed")
			);
		}
	}

	/**
	 *	Method to show box
	 *
	 *	@param	array	$head       Array with properties of box title
	 *	@param  array	$contents   Array with properties of box lines
	 *  @param	int		$nooutput	No print, only return string
	 *	@return	string
	 */
	public function showBox($head = null, $contents = null, $nooutput = 0)
	{
		return parent::showBox($this->info_box_head, $this->info_box_contents, $nooutput);
	}
}
