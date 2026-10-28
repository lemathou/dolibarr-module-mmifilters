<?php
/**
 * Test fonctionnel du filtre « Uniquement les pro » sur la liste des expéditions (RM3495),
 * et non-régression sur devis / commandes / factures.
 *
 * Appelle directement les hooks de ActionsMMIFilters avec le contexte voulu et vérifie
 * les fragments produits (HTML, FROM, WHERE, param d'URL), puis contrôle en base que la
 * jointure extrafields ne duplique aucune expédition. Lecture seule : rien n'est écrit.
 *
 * Usage : php7.4 test/functional/shipment_pro_filter_test.php (depuis n'importe où)
 * Code de sortie 0 si tout passe.
 */

// Le chargeur mmicommon cherche master.inc.php relativement au cwd : se placer à la racine du module
chdir(__DIR__.'/../..');
require_once __DIR__.'/../../master_load.inc.php';
dol_include_once('custom/mmifilters/class/actions_mmifilters.class.php');

global $db, $conf;

$fail = 0;
$n = 0;
function check($label, $cond, $detail = '')
{
	global $fail, $n;
	$n++;
	echo ($cond ? 'ok   ' : 'FAIL ').$label.(!$cond && $detail !== '' ? ' — '.$detail : '')."\n";
	if (!$cond) $fail++;
}

/** Joue un hook dans un contexte donné et renvoie resprints. */
function run_hook($hook, $context, array $get)
{
	global $db;
	$_GET = $get;
	$_POST = [];
	$_REQUEST = $get;
	$obj = new ActionsMMIFilters($db);
	$object = null;
	$action = 'list';
	$parameters = ['context' => $context, 'currentcontext' => $context];
	$ret = $obj->$hook($parameters, $object, $action, null);
	return [$ret, (string) $obj->resprints];
}

$conf->global->MMIFILTERS_PRO_USE_SIREN = 0;
$on = ['search_thirdparty_pro' => '1'];
$off = [];

// --- Expéditions, case cochée -------------------------------------------------------
list($ret, $html) = run_hook('printFieldPreListTitle', 'shipmentlist', $on);
check('shipmentlist : case « pro » rendue', $ret === 0 && strpos($html, 'name="search_thirdparty_pro"') !== false);
check('shipmentlist : case cochée quand le paramètre est posé', strpos($html, 'checked="checked"') !== false);
check('shipmentlist : pas de champ « Réf. Produit »', strpos($html, 'search_product_ref') === false);

list($ret, $from) = run_hook('printFieldListFrom', 'shipmentlist', $on);
check('shipmentlist : jointure societe_extrafields', strpos($from, 'LEFT JOIN '.MAIN_DB_PREFIX.'societe_extrafields as se2 ON se2.fk_object=s.rowid') !== false, $from);

list($ret, $where) = run_hook('printFieldListWhere', 'shipmentlist', $on);
check('shipmentlist : clause se2.pro = 1', trim($where) === 'AND ((se2.pro = 1))', $where);

list($ret, $param) = run_hook('printFieldListSearchParam', 'shipmentlist', $on);
check('shipmentlist : paramètre propagé (pagination / tri)', $param === '&search_thirdparty_pro=1', $param);

// --- Expéditions, case décochée : aucun fragment -----------------------------------
list($ret, $html) = run_hook('printFieldPreListTitle', 'shipmentlist', $off);
check('shipmentlist décoché : case présente non cochée', strpos($html, 'name="search_thirdparty_pro"') !== false && strpos($html, 'checked="checked"') === false);
list($ret, $from) = run_hook('printFieldListFrom', 'shipmentlist', $off);
check('shipmentlist décoché : pas de jointure', $from === '', $from);
list($ret, $where) = run_hook('printFieldListWhere', 'shipmentlist', $off);
check('shipmentlist décoché : pas de clause WHERE', $where === '', $where);
list($ret, $param) = run_hook('printFieldListSearchParam', 'shipmentlist', $off);
check('shipmentlist décoché : pas de paramètre', $param === '', $param);

// --- Option SIREN ------------------------------------------------------------------
$conf->global->MMIFILTERS_PRO_USE_SIREN = 1;
list($ret, $where) = run_hook('printFieldListWhere', 'shipmentlist', $on);
check('shipmentlist + MMIFILTERS_PRO_USE_SIREN : siren/siret/tva_intra dans la clause', strpos($where, 's.siren IS NOT NULL') !== false && strpos($where, 's.tva_intra IS NOT NULL') !== false && strpos($where, '(se2.pro = 1)') !== false, $where);
$conf->global->MMIFILTERS_PRO_USE_SIREN = 0;

// --- Non-régression devis / commandes / factures ------------------------------------
foreach (['propallist', 'orderlist', 'invoicelist'] as $ctx) {
	list($ret, $html) = run_hook('printFieldPreListTitle', $ctx, $on);
	check("$ctx : case « pro » + champ « Réf. Produit »", strpos($html, 'name="search_thirdparty_pro"') !== false && strpos($html, 'name="search_product_ref"') !== false);
	list($ret, $join) = run_hook('printFieldListJoinSoc', $ctx, $on);
	check("$ctx : jointure extrafields via printFieldListJoinSoc", strpos($join, 'societe_extrafields as se2') !== false, $join);
	list($ret, $from) = run_hook('printFieldListFrom', $ctx, $on);
	check("$ctx : printFieldListFrom inchangé (vide)", $from === '', $from);
	list($ret, $where) = run_hook('printFieldListWhere', $ctx, $on);
	check("$ctx : clause se2.pro = 1", trim($where) === 'AND ((se2.pro = 1))', $where);
	list($ret, $param) = run_hook('printFieldListSearchParam', $ctx, $on);
	check("$ctx : paramètre propagé", $param === '&search_thirdparty_pro=1', $param);
}
list($ret, $html) = run_hook('printFieldPreListTitle', 'propallist', $on);
check('propallist : case « Sans commercial » toujours là', strpos($html, 'name="search_no_user"') !== false);
list($ret, $html) = run_hook('printFieldPreListTitle', 'orderlist', $on);
check('orderlist : pas de case « Sans commercial »', strpos($html, 'name="search_no_user"') === false);

// --- Base : la jointure ne duplique pas, et le filtre réduit la liste --------------
$sql_total = 'SELECT COUNT(e.rowid) AS n FROM '.MAIN_DB_PREFIX.'expedition as e';
$sql_join  = 'SELECT COUNT(e.rowid) AS n, COUNT(DISTINCT e.rowid) AS nd FROM '.MAIN_DB_PREFIX.'expedition as e'
	.' LEFT JOIN '.MAIN_DB_PREFIX.'societe as s ON s.rowid = e.fk_soc'.ActionsMMIFilters::proJoinClause();
$sql_pro   = $sql_join.' WHERE 1=1'.ActionsMMIFilters::proWhereClause();
$total = $db->fetch_object($db->query($sql_total));
$joined = $db->fetch_object($db->query($sql_join));
$pro = $db->fetch_object($db->query($sql_pro));
check('base : la jointure extrafields ne duplique aucune expédition', (int) $joined->n === (int) $total->n && (int) $joined->nd === (int) $total->n, "total={$total->n} joint={$joined->n} distinct={$joined->nd}");
check('base : filtre pro ⇒ sous-ensemble sans doublon', (int) $pro->n === (int) $pro->nd && (int) $pro->n <= (int) $total->n, "pro={$pro->n} distinct={$pro->nd}");
echo "info : expéditions total={$total->n}, pro={$pro->n}\n";

echo "\n$n contrôles, $fail échec(s)\n";
exit($fail ? 1 : 0);
