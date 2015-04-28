## 基于字典检索、RMM算法的分词处理

```php
// 如果有自动加载机制，这个可以省略
require('Lib/Consts.php');
require('Lib/Compile.php');
require('Buffer/Char.php');
require('Buffer/Filec.php');
require('Lib/Retrieval.php');
require('Lib/Splitword.php');

$splitword = \Lib\Splitword::getInstance();
$splitword->initRetrieval('Bin/compile.ctx');

$splitword->exec('极致纤薄一体时尚机身 三星A3仅售1770');
var_dump($splitword->getRetrieved());

array(6) {
  '66d954' =>
  string(6) "仅售"
  '779c4e' =>
  string(8) "三星A3"
  [634334] =>
  string(6) "机身"
  '0ccaec' =>
  string(6) "时尚"
  'a32dc5' =>
  string(6) "一体"
  '7b8f34' =>
  string(6) "极致"
}
```