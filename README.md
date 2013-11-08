ruten-to-rss
============

露天拍賣 ( www.ruten.com.tw ) 的搜尋結果轉換 RSS 格式提供閱讀器讀取用。

### 關於此程式
露天拍賣不像 Yahoo 拍賣有關鍵字的 RSS 輸出，
可以讓人不用回去一直查就能快速掌握相關關鍵字的商品，
透過 ruten-to-rss 可以把搜尋結果的頁面轉換為 RSS 輸出，
就能交給 Feedly 之類的 RSS Reader 掌握。

###露天拍賣 RSS 搜尋結果 RSS 轉換
目前搜尋露天拍賣的結果網址轉換為 RSS 2.0 格式，
必須自行將此程配合 Feedly 或其他 RSS 閱讀器使用，
由於只會讀取第一頁，所以過往的商品資訊不會被讀取到。

### 排序結果
輸出的 RSS 2.0 格式排序內容，
不論在搜尋結果網址選擇什麼樣的排序方式，
一律會以 `最新刊登` `降冪排列` 作為預設的排序方式，
以利獲取最新的商品資訊。

### example.php
```php
include("RutenSearch.php");

// 直接把搜尋結果從 GET 傳入
$result_url = isset($_GET['result_url']) ? $_GET['result_url'] : '';

//讀取帳號的資料
$RutenSearch = new RutenSearch($result_url);
$RutenSearch->asXml();
```

### 簡易使用方式
將 example.php 放置於可透過外部存取的 web server，
透過 `result_url` 的參數作為 GET Param。
例如我想找 `HTC New One` `二手` `商品在台北市` 的條件，露天搜尋結果網址為：
```
http://search.ruten.com.tw/search/s000.php?k=HTC+New+one&c=0&ctab=1&searchfrom=searchbars&f=512&o=9&m=1&fx=512&fy=1&h=0&p1=&p2=```
此時將該網址送往 example.php
** 記得要做 urlencode **
```php
http://yourhost.domain/example.php?result_url=http%3A%2F%2Fsearch.ruten.com.tw%2Fsearch%2Fs000.php%3Fk%3DHTC%2BNew%2Bone%26c%3D0%26ctab%3D1%26searchfrom%3Dsearchbars%26f%3D512%26o%3D9%26m%3D1%26fx%3D512%26fy%3D1%26h%3D0%26p1%3D%26p2%3D
```

### proxy.php
由於露天拍賣的圖片有防止盜連，
因此不能直接用 `<img>` 的方式來呈現圖片，
透過 proxy.php 會透過送出假 Referer 的方式存取圖片，
但僅接受下列圖片網址的圖片檔案。
```php
$domains = array(
  'a.rimg.com.tw',
  'b.rimg.com.tw',
  'c.rimg.com.tw',
  'd.rimg.com.tw',
  'e.rimg.com.tw',
  'f.rimg.com.tw',
);
```