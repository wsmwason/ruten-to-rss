<?php
/**
 * 露天拍賣搜尋器
 */
class RutenSearch {

  protected $_keyword;
	protected $_result_url;
	protected $_html;
	protected $_entries;

	function __construct($result_url = '')
	{
		if(empty($result_url)) die('要有 $result_url.');
		$this->_result_url = $result_url;
	}

	/**
	 * 轉出 XML
	 */
	public function asXml()
	{
		$this->_html = $this->getSearch();
		$this->_entries = $this->parseEntries();
		$this->asRss();
	}

	/**
	 * 執行搜尋取回 HTML
   *
	 * @return string
	 */
	public function getSearch()
	{
    $search_url = preg_replace('#o=[0-9]+#', 'o=11', $this->_result_url);
    if(!preg_match('#o=[0-9]+#isu', $this->_result_url)){
      $search_url.= '&o=10';
    }
    //echo $this->_result_url; exit;
    if(preg_match('#k=([^&]+)#isu', $this->_result_url, $keyword)){
      $this->_keyword = $keyword[1];
    }
    $html = $this->exec_search($search_url);
		if(!preg_match('#共 <span class="total">[0-9,]+</span> 項符合#isu', $html)) die('查無符合的商品.');
		return $html;
	}

	/**
	 * 讀取商品列表
   *
	 * @return array
	 */
	private function parseEntries()
	{
		$entries = array();
		if(preg_match_all('#<h3 class="title entry-title">.*?<a href="http://goods.ruten.com.tw/item/show\?([0-9]+)" title="[^"]+">(.*?)</a></h3>#isu', $this->_html, $matches)){

      // 取出日期
      preg_match_all('#<td headers="dateTime" class="date-time" rowspan="1">(\d{4}-\d{2}-\d{2})#isu', $this->_html, $dateinfo);

      // 取出賣家
      preg_match_all('#<a href="http://class.ruten.com.tw/user/index00.php\?s=[^"]+"[^>]+>(.*?)</a>#isu', $this->_html, $ownerinfo);

      // 取出圖片
      preg_match_all('#<span class="image"><a[^>]+><img src="(http[^"]+)"[^>]*?></a></span>#isu', $this->_html, $imageinfo);

			foreach($matches[1] as $i => $id){
				$entries[$id] = array(
					'id' => $id,
					'title' => $matches[2][$i],
					'url' => 'http://goods.ruten.com.tw/item/show?'.$id,
					'author' => $ownerinfo[1][$i],
					'pubdate' => $dateinfo[1][$i],
          'image' => str_replace('_s', '_m', $imageinfo[1][$i]),
				);
			}
		}
		return $entries;
	}

  /**
   * 執行搜尋並轉回 UTF-8
   *
   * @param string $url
   * @return string
   */
  private function exec_search($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:24.0) Gecko/20100101 Firefox/24.0');
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIE, '_ts_id=ruten-to-rss');
    $html = curl_exec($ch);
    //print_r(curl_error($ch)); exit;
    curl_close($ch);
    $html = mb_convert_encoding($html, 'UTF-8', 'BIG-5');
    return $html;
  }

	/**
	 * 輸出 RSS
	 */
	private function asRss()
	{
		$XML = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss version="2.0" />');
		$channel = $XML->addChild('channel');
      $channel->addChild('title', $this->_keyword.' 露天拍賣關鍵字 RSS');
      $channel->addChild('link', 'http://www.ruten.com.tw/');
      $channel->addChild('language', 'zh-tw');
      $channel->addChild('lastBuildDate', date("D, j M Y H:i:s +0800", time()));
      $channel->addChild('ttl', '20');

		foreach($this->_entries as $id => $entry){

      $image_proxy = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'/proxy.php';
      $image_url = $image_proxy.'?url='.urlencode($entry['image']);

			$item = $channel->addChild('item');
				$item->addChild('title', $entry['title']);
				$item->addChild('link', $entry['url']);
				$item->addChild('author', $entry['author']);
				$item->addChild('description', '<img src="'.$image_url.'"> 商品編號: '.$id);
				$item->addChild('pubDate', date("D, j M Y H:i:s +0800", strtotime($entry['pubdate'])));
		}

		header('Content-type: text/xml');
    echo $XML->asXML();
	}

}
