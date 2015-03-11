<?php

$thisfile = basename(__FILE__, ".php");
register_plugin(
	$thisfile,
	'Markdown Editor',
	'0.1',
	'Carlos Navarro',
	'http://www.cyberiada.org/cnb/',
	'Create and edit pages using the Markdown syntax',
	'',
	''
);

if (!defined('MARKDOWN_EDITOR') || MARKDOWN_EDITOR) {
  add_action('changedata-save','mde_savecontent');
  add_action('edit-extras','mde_editcontent');
  add_filter('pagecache','mde_fixpagecache');
  if (mde_addlink())
    add_action('pages-sidebar', 'mde_sidebar');
}

function mde_editcontent() {
	global $content, $HTMLEDITOR, $data_edit;

  if (
    isset($data_edit->contentmd) ||
    isset($_GET['markdown']) ||
    (!mde_addlink() && (!isset($_GET['id']) || empty($_GET['id']) || empty($content))) ||
    (empty($content) && defined('MARKDOWN_EMPTY') && MARKDOWN_EMPTY)
    ) {
    if (!empty($data_edit->contentmd)) $content = $data_edit->contentmd;
    $HTMLEDITOR = '';
    echo PHP_EOL,'<input id="markdown-enabled" name="markdown-enabled" type="hidden" value="1">',PHP_EOL;
  }
}

function mde_savecontent() {
	if (isset($_POST['markdown-enabled']) && $_POST['markdown-enabled'] == '1') {
    global $xml;
    if (!class_exists('Parsedown'))
      require_once 'markdown_editor/parsedown/Parsedown.php';
    $Parsedown = new Parsedown();
		$text = get_magic_quotes_gpc()!=0 ? stripslashes($_POST['post-content']) : $_POST['post-content'];
		unset($xml->content);
		$note = $xml->addChild('content');
		$note->addCData(safe_slash_html($Parsedown->text($text)));
		$note = $xml->addChild('contentmd');
		$note->addCData($text);
	}
}

function mde_fixpagecache($xml) {
  foreach ($xml as $item)
    if (isset($item->contentmd)) unset($item->contentmd);
  return $xml;
}

function mde_addlink() {
  if (!defined('MARKDOWN_ADDLINK'))
    return true;
  elseif (MARKDOWN_ADDLINK)
    return MARKDOWN_ADDLINK;
  else
    return false;
}

function mde_sidebar() {
  if (strlen(mde_addlink()) > 1)
    $str = mde_addlink();
  else
    $str = strip_tags(i18n_r('SIDE_CREATE_NEW')).' (Markdown)';
?>
	<li id="sb_markdown_editor" class="plugin_sb"><a href="edit.php?markdown"><?php echo $str; ?></a></li>
<?php
}
