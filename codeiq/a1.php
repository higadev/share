<?php

class Hello {
}

class World {
}

echo \implode(
    \chr(\strlen(\md5(\PHP_VERSION))),
    [Hello::class, World::class]
) . \PHP_EOL;
