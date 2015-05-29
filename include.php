<?php
RegisterPlugin("baiduRTSM", "ActivePlugin_baiduRTSM");

function ActivePlugin_baiduRTSM() {
	Add_Filter_Plugin('Filter_Plugin_Edit_Response3', 'baiduRTSM_edit_response3');
	Add_Filter_Plugin('Filter_Plugin_PostArticle_Succeed', 'baiduRTSM_post_article_succeed');
}

function InstallPlugin_baiduRTSM() {
	global $zbp;
	if (!isset($zbp->Config('baiduRTSM')->strData)) {
		$zbp->Config('baiduRTSM')->strData = '';
		$zbp->SaveConfig('baiduRTSM');
	}
}

function UninstallPlugin_baiduRTSM() {
}

function baiduRTSM_edit_response3() {
	?>
<div id="baiduRTSM" class="editmod">
  <label for="edtbaiduRTSM" class="editinputname">百度推送:</label>
  <input id="edtbaiduRTSM" name="baiduRTSM" type="text" value="1" class="checkbox"/>
</div>
<?php
}
function baiduRTSM_post_single($url, $article_url) {
	if (preg_match('/ping.baidu.com\/sitemap/', $url)) {
		baiduRTSM_old_sitemap($url, $article_url);
	} else {
		baiduRTSM_new_linksubmit($url, $article_url);
	}
}

function baiduRTSM_new_linksubmit($url, $article_url) {
	global $zbp;
	$s = trim($article_url);

	$ajax = Network::Create();
	if (!$ajax) {
		throw new Exception('主机没有开启网络功能');
	}
	$ajax->open('POST', $url);
	$ajax->setRequestHeader('Content-Type', 'text/plain');
	$ajax->send($s);
	$ajax = null;
	return;
}

function baiduRTSM_old_sitemap($url, $article_url) {

	global $zbp;
	$s = '<?xml version="1.0" encoding="UTF-8"?><urlset><url><loc>' . trim($article_url) . '</loc><lastmod>' . date('c') . '</lastmod><changefreq>daily</changefreq><priority>0.8</priority></url></urlset>';

	$ajax = Network::Create();
	if (!$ajax) {
		throw new Exception('主机没有开启网络功能');
	}

	$ajax->open('POST', $url);
	$ajax->setRequestHeader('Content-Type', 'text/xml');
	$ajax->send($s);

	$ajax = null;

	$zbp->SetHint('bad', '<a href="' . $zbp->host . 'zb_users/plugin/baiduRTSM/main.php" target="_blank">请立即点击这里，打开你的百度推送插件，设置新的推送地址！</a>');
	$zbp->SetHint('bad', '否则，百度推送可能无法正常工作！');

	return;
}

function baiduRTSM_post_article_succeed(&$article) {

	if (GetVars('baiduRTSM', 'POST') != '1') {
		return;
	}

	global $zbp;

	$ping_data = $zbp->Config('baiduRTSM')->strData;
	$ping_array = explode("\n", $ping_data);

	for ($i = 0; $i < count($ping_array); $i++) {
		baiduRTSM_post_single(trim($ping_array[$i]), $article->Url);
		echo $article->Url . "\n";
	}

}