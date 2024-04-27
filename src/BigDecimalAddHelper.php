<?php

namespace SeoworkTask;

use Brick\Math\BigInteger;
use Brick\Math\Exception\DivisionByZeroException;
use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use SplDoublyLinkedList;
use ValueError;

class BigDecimalAddHelper
{
    /**
     * Сложение с помощью bcadd
     * @param string ...$values
     * @return string
     */
    static public function variant1(string ...$values): string
    {
        $result = '0';
        foreach ($values as $value) {
            $result = bcadd($value, $result);
        }
        return $result;
    }

    /**
     * Сложение строк
     * @param string ...$values
     * @return string
     */
    static public function variant2(string ...$values): string
    {
        $result = '0';
        foreach ($values as $value) {
            $result = self::sumBigDecimalString($value, $result);
        }
        return $result;
    }

    /**
     * @param string $a
     * @param string $b
     * @return string
     */
    static public function sumBigDecimalString(string $a, string $b): string
    {
        if (!is_numeric($a)) {
            throw new ValueError($a);
        } else if (!is_numeric($b)) {
            throw new ValueError($b);
        }

        $lenA = strlen($a);
        $lenB = strlen($b);

        if ($lenB > $lenA) {
            [$lenA, $lenB] = [$lenB, $lenA];
            [$a, $b] = [$b, $a];
        }

        $addRad = 0;
        for ($ai = $lenA - 1, $bi = $lenB - 1; $ai >= 0; --$ai, --$bi) {
            if ($bi < 0 && $addRad == 0) {
                break;
            }
            $valB = ($bi >= 0) ? (int)$b[$bi] : 0;
            $tmp = (int)$a[$ai] + $valB + $addRad;
            $addRad = ((int)($tmp / 10) > 0) ? 1 : 0;
            $a[$ai] = $tmp % 10;
        }

        if ($addRad) {
            $a = '1' . $a;
        }

        return $a;
    }

    /**
     * Сложение с помощью gmp_add
     * @param string ...$values
     * @return string
     */
    static public function variant3(string ...$values): string
    {
        $result = '0';
        foreach ($values as $value) {
            $result = gmp_add($value, $result);
        }
        return gmp_strval($result);
    }

    /**
     * Сложение с помощью BigInteger
     * @param string ...$values
     * @return string
     * @throws DivisionByZeroException
     * @throws MathException
     * @throws NumberFormatException
     */
    static public function variant4(string ...$values): string
    {
        $result = '0';
        foreach ($values as $value) {
            $bigValue = BigInteger::of($value);
            $result = $bigValue->plus($result);
        }
        return $result;
    }

    /**
     * Сложение с помощью SplDoublyLinkedList
     * @param string ...$values
     * @return string
     */
    static public function variant5(string ...$values): string
    {
        $result = new SplDoublyLinkedList();
        $result->push(0);
        foreach ($values as $value) {
            $listValue = self::stringToList($value);
            $result->rewind();
            $result = self::sumBigDecimalList($listValue, $result);
        }
        return self::listToString($result);
    }

    /**
     * @param SplDoublyLinkedList $l1
     * @param SplDoublyLinkedList $l2
     * @return SplDoublyLinkedList
     */
    static public function sumBigDecimalList(SplDoublyLinkedList $l1, SplDoublyLinkedList $l2): SplDoublyLinkedList
    {
        $addRad = 0;
        $l = new SplDoublyLinkedList();
        while ($l1->valid() || $l2->valid() || $addRad != 0) {
            $val1 = 0;
            if ($l1->valid()) {
                $val1 = $l1->current();
                $l1->next();
            }
            $val2 = 0;
            if ($l2->valid()) {
                $val2 = $l2->current();
                $l2->next();
            }

            $val = $val1 + $val2 + $addRad;
            $addRad = (int)($val / 10);
            $val = $val % 10;

            $l->push($val);
        }
        $l->rewind();
        return $l;
    }

    /**
     * @param string $value
     * @return SplDoublyLinkedList
     */
    static public function stringToList(string $value): SplDoublyLinkedList
    {
        $result = new SplDoublyLinkedList();
        $len = strlen($value);
        for ($i = $len - 1; $i >= 0; --$i) {
            $result->push((int)$value[$i]);
        }
        $result->rewind();
        return $result;
    }

    /**
     * @param SplDoublyLinkedList $list
     * @return string
     */
    static public function listToString(SplDoublyLinkedList $list): string
    {
        $result = '';
        $list->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);
        for ($list->rewind(); $list->valid(); $list->next()) {
            $result .= $list->current();
        }
        return $result;
    }
}
