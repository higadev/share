<?php

const ZERO = 'チピチピ';
const ONE = 'チャパチャパ';

echo '実行するプログラムを入力: ';
$lines = file (readline(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$program_chars = [];

foreach ($lines as $line) {
    $program_chars[] = \mb_chr(\bindec(\str_replace(ONE, '1', \str_replace(ZERO, '0', $line))));
};

$program = \sprintf('<?php%s%s', \PHP_EOL, \implode('',  $program_chars));
\file_put_contents('executor.php', $program);

echo '読み込み中';

for ($i = 0; $i < 20; $i++) {
    echo '.';
    \usleep(100000);
}

\sleep(1);
echo \PHP_EOL, \PHP_EOL;

require './executor.php';
echo \PHP_EOL, \PHP_EOL;
