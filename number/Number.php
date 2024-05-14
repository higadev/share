<?php

define('ZERO', []);
define('ONE', [ZERO]);

class NaturalNumber {
    public array $value;

    public function __construct(array $value)
    {
        $this->value = $value;
    }

    public static function as(array $value): NaturalNumber
    {
        return new static($value);
    }

    public function next(): NaturalNumber
    {
        return NaturalNumber::as(\array_merge($this->value, [$this->value]));
    }

    public function previous(): NaturalNumber
    {
        if (ZERO === $this->value) {
            return $this;
        }

        $value = $this->value;
        \array_pop($value);

        return NaturalNumber::as($value);
    }

    public function isGreaterThan(NaturalNumber $natural_number): bool
    {
        return \in_array($natural_number->value, $this->value);
    }

    public function add(NaturalNumber $natural_number): NaturalNumber
    {
        if (ZERO === $natural_number->value) {
            return $this;
        }

        return $this->next()->add($natural_number->previous());
    }

    public function multiply(NaturalNumber $natural_number): NaturalNumber
    {
        if (ZERO === $natural_number->value) {
            return $natural_number;
        }

        return $this->multiply($natural_number->previous())->add($this);
    }
}

class Integer {
    public NaturalNumber $natural_number;
    public NaturalNumber $diff;

    public function __construct(NaturalNumber $natural_number, NaturalNumber $diff)
    {
        $this->natural_number = $natural_number;
        $this->diff = $diff;
    }

    public static function as(NaturalNumber $natural_number, NaturalNumber $diff): Integer
    {
        return new static($natural_number, $diff);
    }

    public function equals(Integer $integer): bool
    {
        return $this->natural_number->add($integer->diff)->value === $this->diff->add($integer->natural_number)->value;
    }

    public function negative(): Integer
    {
        return Integer::as($this->diff, $this->natural_number);
    }

    public function add(Integer $integer): Integer
    {
        return Integer::as(
            $this->natural_number->add($integer->natural_number),
            $this->diff->add($integer->diff),
        );
    }

    public function multiply(Integer $integer): Integer
    {
        return Integer::as(
            $this->natural_number->multiply($integer->natural_number),
            $this->natural_number->multiply($integer->diff),
        )->add(Integer::as(
            $this->diff->multiply($integer->diff),
            $this->diff->multiply($integer->natural_number),
        ));
    }

    public function subtract(Integer $integer): Integer
    {
        return Integer::as(
            $this->natural_number->add($integer->diff),
            $this->diff->add($integer->natural_number),
        );
    }
}

class RationalNumber
{
    public Integer $integer;
    public Integer $division;

    public function __construct(Integer $integer, Integer $division)
    {
        if ($division->equals(Integer::as(NaturalNumber::as(ZERO), NaturalNumber::as(ZERO)))) {
            throw new \InvalidArgumentException();
        }

        $this->integer = $integer;
        $this->division = $division;
    }

    public static function as(Integer $integer, Integer $division): RationalNumber
    {
        return new static($integer, $division);
    }

    public function equals(RationalNumber $rational_number): bool
    {
        return $this->integer->multiply($rational_number->division)->equals($this->division->multiply($rational_number->integer));
    }

    public function negative(): RationalNumber
    {
        return RationalNumber::as($this->integer->negative(), $this->division);
    }

    public function add(RationalNumber $rational_number): RationalNumber
    {
        return RationalNumber::as(
            $this->integer->multiply($rational_number->division)->add($this->division->multiply($rational_number->integer)),
            $this->division->multiply($rational_number->division),
        );
    }

    public function multiply(RationalNumber $rational_number): RationalNumber
    {
        return RationalNumber::as(
            $this->integer->multiply($rational_number->integer),
            $this->division->multiply($rational_number->division),
        );
    }

    public function subtract(RationalNumber $rational_number): RationalNumber
    {
        return $this->add($rational_number->negative());
    }

    public function reciprocal(): RationalNumber
    {
        return RationalNumber::as($this->division, $this->integer);
    }

    public function divide(RationalNumber $rational_number): RationalNumber
    {
        return $this->multiply($rational_number->reciprocal());
    }
}

/**
 * @deprecated
 */
function v(int $lost_value): array
{
    $natural_number = NaturalNumber::as(ZERO);

    if ($lost_value < 0) {
        return $natural_number->value;
    }

    for($i = 0; $i < $lost_value; $i++) {
        $natural_number = $natural_number->next();
    }

    return $natural_number->value;
}

/**
 * @deprecated
 */
function n(array $value): int
{
    return \count($value);
}

/**
 * @deprecated
 */
function i(Integer $integer): int
{
    return n($integer->natural_number->value) - n($integer->diff->value);
}

/**
 * @deprecated
 */
function r(RationalNumber $rational_number): float
{
    return (float) i($rational_number->integer) / (float) i($rational_number->division);
}

function judge(bool $equation)
{
    if (!$equation) {
        throw new \Exception();
    }
}

judge(ZERO === NaturalNumber::as([])->value);
judge(ONE === NaturalNumber::as([[]])->value);
judge([[], [[]], [[], [[]]]] === NaturalNumber::as(v(3))->value);

judge(NaturalNumber::as(v(10))->value === NaturalNumber::as(v(9))->next()->value);
judge(NaturalNumber::as(v(5))->value === NaturalNumber::as(v(6))->previous()->value);

judge(NaturalNumber::as(v(2))->isGreaterThan(NaturalNumber::as(v(0))));
judge(NaturalNumber::as(v(10))->isGreaterThan(NaturalNumber::as(v(3))));
judge(!(NaturalNumber::as(v(5))->isGreaterThan(NaturalNumber::as(v(6)))));
judge(!(NaturalNumber::as(v(9))->isGreaterThan(NaturalNumber::as(v(9)))));

judge(NaturalNumber::as(v(7))->value === NaturalNumber::as(v(3))->add(NaturalNumber::as(v(4)))->value);
judge(NaturalNumber::as(v(21))->value === NaturalNumber::as(v(7))->multiply(NaturalNumber::as(v(3)))->value);

judge(7 === i(Integer::as(NaturalNumber::as(v(11)), NaturalNumber::as(v(4)))));
judge(7 === i(Integer::as(NaturalNumber::as(v(9)), NaturalNumber::as(v(2)))));
judge(-7 === i((Integer::as(NaturalNumber::as(v(11)), NaturalNumber::as(v(4))))->negative()));

judge(Integer::as(NaturalNumber::as(v(11)), NaturalNumber::as(v(4)))->equals(Integer::as(NaturalNumber::as(v(9)), NaturalNumber::as(v(2)))));
judge(14 === i(Integer::as(NaturalNumber::as(v(11)), NaturalNumber::as(v(4)))->add(Integer::as(NaturalNumber::as(v(9)), NaturalNumber::as(v(2))))));

// 3 - 12 = -9   であること
judge(-9 === i(Integer::as(NaturalNumber::as(v(7)), NaturalNumber::as(v(4)))->add(Integer::as(NaturalNumber::as(v(1)), NaturalNumber::as(v(13))))));
judge(-9 === i(Integer::as(NaturalNumber::as(v(7)), NaturalNumber::as(v(4)))->subtract(Integer::as(NaturalNumber::as(v(13)), NaturalNumber::as(v(1))))));

// -5 * 3 = -15    であること
judge(-15 === i(Integer::as(NaturalNumber::as(v(2)), NaturalNumber::as(v(7)))->multiply(Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(3))))));
judge(-15 === i(Integer::as(NaturalNumber::as(v(5)), NaturalNumber::as(v(2)))->multiply(Integer::as(NaturalNumber::as(v(0)), NaturalNumber::as(v(5))))));

judge(3.0 === r(RationalNumber::as(Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(0))), Integer::as(NaturalNumber::as(v(7)), NaturalNumber::as(v(3))))));
judge(3.0 === r(RationalNumber::as(Integer::as(NaturalNumber::as(v(10)), NaturalNumber::as(v(1))), Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(3))))));

judge(-3.0 === r(RationalNumber::as(Integer::as(NaturalNumber::as(v(1)), NaturalNumber::as(v(10))), Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(3))))));

judge(RationalNumber::as(
        Integer::as(NaturalNumber::as(v(2)), NaturalNumber::as(v(1))),
        Integer::as(NaturalNumber::as(v(2)), NaturalNumber::as(v(1))),
    )->equals(
        RationalNumber::as(
            Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(5))),
            Integer::as(NaturalNumber::as(v(5)), NaturalNumber::as(v(4))),
        )
    )
);
judge(!RationalNumber::as(
    Integer::as(NaturalNumber::as(v(2)), NaturalNumber::as(v(1))),
    Integer::as(NaturalNumber::as(v(2)), NaturalNumber::as(v(1))),
)->equals(
    RationalNumber::as(
        Integer::as(NaturalNumber::as(v(3)), NaturalNumber::as(v(1))),
        Integer::as(NaturalNumber::as(v(2)), NaturalNumber::as(v(1))),
    )
)
);

judge(0.5 === r(RationalNumber::as(Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(1))), Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))))));
judge(-0.5 === r(RationalNumber::as(Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(1))), Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))))->negative()));

judge(0.7 === r(
    RationalNumber::as(
        Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(1))),
        Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))),
    )->add(
        RationalNumber::as(
            Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(4))),
            Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))),
        )
    )
));
judge(0.1 === r(
    RationalNumber::as(
        Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(1))),
        Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))),
    )->multiply(
        RationalNumber::as(
            Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(4))),
            Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))),
        )
    )
));
judge(0.3 === r(
    RationalNumber::as(
        Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(1))),
        Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))),
    )->subtract(
        RationalNumber::as(
            Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(4))),
            Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))),
        )
    )
));

judge(2.0 === r(RationalNumber::as(Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(1))), Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))))->reciprocal()));

judge(2.5 === r(
    RationalNumber::as(
        Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(1))),
        Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))),
    )->divide(
        RationalNumber::as(
            Integer::as(NaturalNumber::as(v(6)), NaturalNumber::as(v(4))),
            Integer::as(NaturalNumber::as(v(12)), NaturalNumber::as(v(2))),
        )
    )
));
