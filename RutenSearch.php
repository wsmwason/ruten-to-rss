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
      $this->_keyword = urldecode($keyword[1]);
    }
    $html = $this->exec_search($search_url);
    $match_result_count = 0;
    if(preg_match('#"pager_config":{"p":1,"total":"([0-9]+)"#isu', $html, $match_result)){
      $match_result_count = $match_result[1];
    }
    if(empty($match_result_count)) die('查無符合的商品.');
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
    if(preg_match_all('#<a class="item-name-anchor" href="http://goods.ruten.com.tw/item/show\?([0-9]+)" title="([^"]+)"#isu', $this->_html, $matches)){

      // 取出日期
      preg_match_all('#<td class="text-right">\s+<span>\s+(\d{4}-\d{2}-\d{2})\s+</span>\s+</td>#isu', $this->_html, $dateinfo);

      // 取出賣家
      preg_match_all('#<a href="http://class.ruten.com.tw/user/index00.php\?s=[^"]+"[^>]+>(.*?)</a>#isu', $this->_html, $ownerinfo);

      // 取出圖片
      preg_match_all('#<img src="([^"]+)" alt="[^"]+" title="[^"]+" border="0" itemprop="image" />#isu', $this->_html, $imageinfo);

      // 取出價格
      preg_match_all('#<meta itemprop="price" content="([0-9]+)" />#isu', $this->_html, $priceInfo);

      foreach($matches[1] as $i => $id){
        $entries[$id] = array(
          'id' => $id,
          'title' => $matches[2][$i],
          'url' => 'http://goods.ruten.com.tw/item/show?'.$id,
          'author' => $ownerinfo[1][$i],
          'price' => $priceInfo[1][$i],
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
    curl_close($ch);
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

      $image_url = $entry['image'];

      $item = $channel->addChild('item');
        $item->addChild('title', $entry['title']);
        $item->addChild('link', $entry['url']);
        $item->addChild('author', $entry['author']);
        $item->addChild('description', '<img src="'.$image_url.'" /><br />商品編號: ' . $id . '<br />價格: ' . $entry['price']);
        $item->addChild('pubDate', date("D, j M Y H:i:s +0800", strtotime($entry['pubdate'])));
    }

    header('Content-type: text/xml');
    echo $XML->asXML();
  }

}
