<?php
function console($data){
  $stdout = fopen('php://stdout', 'w');
  fwrite($stdout,json_encode($data)."\n");   //为了打印出来的格式更加清晰，把所有数据都格式化成Json字符串
  fclose($stdout);
}

console(__DIR__);